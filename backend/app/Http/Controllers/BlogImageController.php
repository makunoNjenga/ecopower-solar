<?php
namespace App\Http\Controllers;

use App\Http\Resources\BlogImageResource;
use App\Models\Blog;
use App\Models\BlogImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Controller for managing blog images
 */
class BlogImageController extends Controller
{
    /**
     * Get all images for a blog
     *
     * @param Blog $blog
     * @return AnonymousResourceCollection
     */
    public function index(Blog $blog): AnonymousResourceCollection
    {
        $images = $blog->images()->orderBy('sort_order')->get();

        return BlogImageResource::collection($images);
    }

    /**
     * Upload and store a new image for a blog
     *
     * @param Request $request
     * @param Blog $blog
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Blog $blog): JsonResponse
    {
        $request->validate([
            'image'    => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption'  => ['nullable', 'string', 'max:255'],
        ]);

        $image    = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $path     = $image->storeAs('blogs', $filename, 'public');

        $maxSortOrder = $blog->images()->max('sort_order') ?? -1;

        $blogImage = $blog->images()->create([
            'image_path' => $path,
            'alt_text'   => $request->alt_text,
            'caption'    => $request->caption,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return (new BlogImageResource($blogImage))
            ->additional(['message' => 'Image uploaded successfully'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Upload and store multiple images for a blog
     *
     * @param Request $request
     * @param Blog $blog
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeMultiple(Request $request, Blog $blog): JsonResponse
    {
        $request->validate([
            'images'      => ['required', 'array', 'min:1', 'max:10'],
            'images.*'    => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'alt_texts'   => ['nullable', 'array'],
            'alt_texts.*' => ['nullable', 'string', 'max:255'],
            'captions'    => ['nullable', 'array'],
            'captions.*'  => ['nullable', 'string', 'max:255'],
        ]);

        $uploadedImages = [];
        $errors         = [];

        $maxSortOrder = $blog->images()->max('sort_order') ?? -1;

        foreach ($request->file('images') as $index => $image) {
            try {
                $filename = time() . '_' . uniqid() . '_' . $index . '.' . $image->getClientOriginalExtension();
                $path     = $image->storeAs('blogs', $filename, 'public');

                $altText = $request->alt_texts[$index] ?? '';
                $caption = $request->captions[$index] ?? '';

                $blogImage = $blog->images()->create([
                    'image_path' => $path,
                    'alt_text'   => $altText,
                    'caption'    => $caption,
                    'sort_order' => $maxSortOrder + $index + 1,
                ]);

                $uploadedImages[] = new BlogImageResource($blogImage);

            } catch (\Exception $e) {
                $errors[] = [
                    'index'    => $index,
                    'filename' => $image->getClientOriginalName(),
                    'error'    => $e->getMessage(),
                ];
            }
        }

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
     *
     * @param Request $request
     * @param Blog $blog
     * @param BlogImage $image
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Blog $blog, BlogImage $image): JsonResponse
    {
        if ($image->blog_id !== $blog->id) {
            return response()->json([
                'message' => 'Image not found for this blog',
            ], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'alt_text'   => ['nullable', 'string', 'max:255'],
            'caption'    => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $image->update($request->only(['alt_text', 'caption', 'sort_order']));

        return (new BlogImageResource($image))
            ->additional(['message' => 'Image updated successfully'])
            ->response();
    }

    /**
     * Delete an image
     *
     * @param Blog $blog
     * @param BlogImage $image
     * @return JsonResponse
     */
    public function destroy(Blog $blog, BlogImage $image): JsonResponse
    {
        if ($image->blog_id !== $blog->id) {
            return response()->json([
                'message' => 'Image not found for this blog',
            ], Response::HTTP_NOT_FOUND);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully',
        ]);
    }
}
