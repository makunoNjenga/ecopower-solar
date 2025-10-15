<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Blog resource for API responses
 */
class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'excerpt'          => $this->excerpt,
            'content'          => $this->content,
            'category_id'      => $this->category_id,
            'author_id'        => $this->author_id,
            'featured_image'   => $this->featured_image ? url('/storage/' . $this->featured_image) : null,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords'    => $this->meta_keywords,
            'views'            => $this->views,
            'is_published'     => $this->is_published,
            'published_at'     => $this->published_at?->format('Y-m-d H:i:s'),
            'category'         => $this->whenLoaded('category'),
            'author'           => $this->whenLoaded('author', function () {
                return [
                    'id'    => $this->author->id,
                    'name'  => $this->author->name,
                    'email' => $this->author->email,
                ];
            }),
            'images'           => BlogImageResource::collection($this->whenLoaded('images')),
            'products'         => ProductResource::collection($this->whenLoaded('products')),
            'created_at'       => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'       => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
