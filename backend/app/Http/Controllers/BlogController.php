<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Controller for managing blog posts in admin panel
 */
class BlogController extends Controller
{
    /**
     * Display a listing of blogs with statistics
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Blog::with(['category', 'author', 'images', 'products']);

        // Search by title or content
        if ($request->filled('search') && $request->search !== 'undefined') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id') && $request->category_id !== 'undefined') {
            $query->where('category_id', $request->category_id);
        }

        // Filter by published status
        if ($request->filled('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        // Filter by author
        if ($request->filled('author_id') && $request->author_id !== 'undefined') {
            $query->where('author_id', $request->author_id);
        }

        // Sort - default to newest first (ID desc)
        $sortBy    = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $blogs = $query->paginate($request->get('per_page', 15));

        return BlogResource::collection($blogs);
    }

    /**
     * Display the specified blog
     *
     * @param Blog $blog
     * @return BlogResource
     */
    public function show(Blog $blog): BlogResource
    {
        $blog->load(['category', 'author', 'images', 'products']);

        return new BlogResource($blog);
    }

    /**
     * Store a newly created blog
     *
     * @param StoreBlogRequest $request
     * @return JsonResponse
     */
    public function store(StoreBlogRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Generate slug from title
        $data['slug']      = Str::slug($data['title']);
        $data['author_id'] = Auth::id();

        // Set published_at if publishing
        if (! empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $blog = Blog::create($data);

        // Attach products if provided
        if ($request->filled('product_ids')) {
            $productIds = $request->product_ids;
            $syncData   = [];
            foreach ($productIds as $index => $productId) {
                $syncData[$productId] = ['sort_order' => $index];
            }
            $blog->products()->sync($syncData);
        }

        $blog->load(['category', 'author', 'images', 'products']);

        return (new BlogResource($blog))
            ->additional(['message' => 'Blog created successfully'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update the specified blog
     *
     * @param UpdateBlogRequest $request
     * @param Blog $blog
     * @return JsonResponse
     */
    public function update(UpdateBlogRequest $request, Blog $blog): JsonResponse
    {
        $data = $request->validated();

        // Update slug if title changed
        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Set published_at if publishing for the first time
        if (! empty($data['is_published']) && ! $blog->published_at) {
            $data['published_at'] = now();
        }

        $blog->update($data);

        // Update products if provided
        if ($request->has('product_ids')) {
            $productIds = $request->product_ids ?? [];
            $syncData   = [];
            foreach ($productIds as $index => $productId) {
                $syncData[$productId] = ['sort_order' => $index];
            }
            $blog->products()->sync($syncData);
        }

        $blog->load(['category', 'author', 'images', 'products']);

        return (new BlogResource($blog))
            ->additional(['message' => 'Blog updated successfully'])
            ->response();
    }

    /**
     * Remove the specified blog
     *
     * @param Blog $blog
     * @return JsonResponse
     */
    public function destroy(Blog $blog): JsonResponse
    {
        $blog->delete();

        return response()->json([
            'message' => 'Blog deleted successfully',
        ]);
    }

    /**
     * Get blog statistics for admin dashboard
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_blogs'     => Blog::count(),
            'published_blogs' => Blog::where('is_published', true)->count(),
            'draft_blogs'     => Blog::where('is_published', false)->count(),
            'total_views'     => Blog::sum('views'),
            'top_blogs'       => Blog::published()
                ->with(['category', 'author'])
                ->orderByDesc('views')
                ->limit(5)
                ->get()
                ->map(function ($blog) {
                    return [
                        'id'           => $blog->id,
                        'title'        => $blog->title,
                        'slug'         => $blog->slug,
                        'views'        => $blog->views,
                        'author'       => $blog->author->name ?? 'Unknown',
                        'category'     => $blog->category->name ?? 'Uncategorized',
                        'published_at' => $blog->published_at?->format('Y-m-d'),
                    ];
                }),
            'recent_blogs'    => Blog::with(['category', 'author'])
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->map(function ($blog) {
                    return [
                        'id'           => $blog->id,
                        'title'        => $blog->title,
                        'slug'         => $blog->slug,
                        'views'        => $blog->views,
                        'is_published' => $blog->is_published,
                        'author'       => $blog->author->name ?? 'Unknown',
                        'created_at'   => $blog->created_at->format('Y-m-d'),
                    ];
                }),
        ];

        return response()->json($stats);
    }
}
