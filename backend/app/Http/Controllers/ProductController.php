<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::with(['category', 'productImages', 'primaryImage', 'agent']);

        // Check if admin route (include inactive products)
        if (! $request->routeIs('admin.*')) {
            $query->active();
        }

        // Filter by category
        if ($request->filled('category_id') && $request->category_id !== 'undefined') {
            $query->where('category_id', $request->category_id);
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Filter by stock
        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        // Search by name or description
        if ($request->filled('search') && $request->search !== 'undefined') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Sort - default to newest first (ID desc)
        $sortBy    = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->get('per_page', 15));

        return ProductResource::collection($products);
    }

    /**
     * Display the specified product.
     *
     * @param Product $product
     * @return ProductResource|JsonResponse
     */
    public function show(Product $product): ProductResource | JsonResponse
    {
        // Check if not active for non-admin routes
        if (! $product->is_active && ! request()->routeIs('admin.*')) {
            return response()->json([
                'message' => 'Product not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $product->load(['category', 'productImages', 'primaryImage', 'agent']);

        return new ProductResource($product);
    }

    /**
     * Store a newly created product.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create([
             ...$request->validated(),
            'slug'     => Str::slug($request->name),
            'agent_id' => auth()->id(),
        ]);

        $product->load(['category', 'productImages', 'primaryImage', 'agent']);

        return (new ProductResource($product))
            ->additional(['message' => 'Product created successfully'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update the specified product.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        // Update slug if name changed
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update($data);
        $product->load(['category', 'productImages', 'primaryImage', 'agent']);

        return (new ProductResource($product))
            ->additional(['message' => 'Product updated successfully'])
            ->response();
    }

    /**
     * Remove the specified product.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
