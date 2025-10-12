<?php
namespace App\Http\Controllers;

use App\Http\Resources\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Get all images for a product
     * Returns images ordered by sort order
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Product $product): AnonymousResourceCollection
    {
        $images = $product->productImages()->orderBy('sort_order')->get();

        return ProductImageResource::collection($images);
    }

    /**
     * Upload and store a new image for a product
     * Handles single image upload with validation
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'image'      => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'alt_text'   => ['nullable', 'string', 'max:255'],
            'is_primary' => ['boolean'],
        ]);

        // Check if setting as primary
        if ($request->boolean('is_primary')) {
            $product->productImages()->update(['is_primary' => false]);
        }

        $image    = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $path     = $image->storeAs('products', $filename, 'public');

        $maxSortOrder = $product->productImages()->max('sort_order') ?? -1;

        $productImage = $product->productImages()->create([
            'path'       => $path,
            'filename'   => $filename,
            'alt_text'   => $request->alt_text,
            'sort_order' => $maxSortOrder + 1,
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return (new ProductImageResource($productImage))
            ->additional(['message' => 'Image uploaded successfully'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Upload and store multiple images for a product
     * Accepts up to 10 images and processes them in batch
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeMultiple(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'images'               => ['required', 'array', 'min:1', 'max:10'],
            'images.*'             => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'alt_texts'            => ['nullable', 'array'],
            'alt_texts.*'          => ['nullable', 'string', 'max:255'],
            'set_first_as_primary' => ['boolean'],
        ]);

        $uploadedImages = [];
        $errors         = [];

        // Check if setting first image as primary
        if ($request->boolean('set_first_as_primary')) {
            $product->productImages()->update(['is_primary' => false]);
        }

        $maxSortOrder = $product->productImages()->max('sort_order') ?? -1;

        foreach ($request->file('images') as $index => $image) {
            try {
                $filename = time() . '_' . uniqid() . '_' . $index . '.' . $image->getClientOriginalExtension();
                $path     = $image->storeAs('products', $filename, 'public');

                $altText = $request->alt_texts[$index] ?? '';

                // Determine if this should be primary
                $isPrimary = $request->boolean('set_first_as_primary') && $index === 0;

                $productImage = $product->productImages()->create([
                    'path'       => $path,
                    'filename'   => $filename,
                    'alt_text'   => $altText,
                    'sort_order' => $maxSortOrder + $index + 1,
                    'is_primary' => $isPrimary,
                ]);

                $uploadedImages[] = new ProductImageResource($productImage);

            } catch (\Exception $e) {
                $errors[] = [
                    'index'    => $index,
                    'filename' => $image->getClientOriginalName(),
                    'error'    => $e->getMessage(),
                ];
            }
        }

        // If there were errors, return them along with successful uploads
        if (! empty($errors)) {
            return response()->json([
                'message'         => 'Some images failed to upload',
                'uploaded_images' => $uploadedImages,
                'errors'          => $errors,
                'success_count'   => count($uploadedImages),
                'error_count'     => count($errors),
            ], Response::HTTP_PARTIAL_CONTENT);
        }

        return response()->json([
            'message'         => count($uploadedImages) . ' images uploaded successfully',
            'uploaded_images' => $uploadedImages,
            'success_count'   => count($uploadedImages),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update image details
     * Updates alt text, sort order, or primary status
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @param \App\Models\ProductImage $image
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Product $product, ProductImage $image): JsonResponse
    {
        // Check if image belongs to product
        if ($image->product_id !== $product->id) {
            return response()->json([
                'message' => 'Image not found for this product',
            ], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'alt_text'   => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_primary' => ['boolean'],
        ]);

        // Check if setting as primary
        if ($request->has('is_primary') && $request->boolean('is_primary')) {
            $product->productImages()->where('id', '!=', $image->id)->update(['is_primary' => false]);
        }

        $image->update($request->only(['alt_text', 'sort_order', 'is_primary']));

        return (new ProductImageResource($image))
            ->additional(['message' => 'Image updated successfully'])
            ->response();
    }

    /**
     * Delete an image
     * Removes image file from storage and database record
     *
     * @param \App\Models\Product $product
     * @param \App\Models\ProductImage $image
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product, ProductImage $image): JsonResponse
    {
        // Check if image belongs to product
        if ($image->product_id !== $product->id) {
            return response()->json([
                'message' => 'Image not found for this product',
            ], Response::HTTP_NOT_FOUND);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully',
        ]);
    }
}
