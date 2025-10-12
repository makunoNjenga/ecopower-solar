<?php
namespace App\Http\Controllers;

use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Controller for public blog viewing
 */
class GuestBlogController extends Controller
{
    /**
     * Display a listing of published blogs
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Blog::published()->with(['category', 'author', 'images', 'products']);

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

        // Sort - default to newest first
        $sortBy    = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $blogs = $query->paginate($request->get('per_page', 12));

        return BlogResource::collection($blogs);
    }

    /**
     * Display the specified blog by slug and increment view count
     *
     * @param string $slug
     * @return BlogResource|JsonResponse
     */
    public function show(string $slug): BlogResource | JsonResponse
    {
        $blog = Blog::where('slug', $slug)
            ->published()
            ->with(['category', 'author', 'images', 'products.category', 'products.primaryImage'])
            ->first();

        if (! $blog) {
            return response()->json([
                'message' => 'Blog not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Increment view count
        $blog->incrementViews();

        return new BlogResource($blog);
    }
}
