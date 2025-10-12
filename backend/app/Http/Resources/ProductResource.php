<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'description'       => $this->description,
            'short_description' => $this->short_description,
            'sku'               => $this->sku,
            'price'             => $this->price,
            'sale_price'        => $this->sale_price,
            'effective_price'   => $this->effective_price,
            'stock_quantity'    => $this->stock_quantity,
            'min_stock_level'   => $this->min_stock_level,
            'weight'            => $this->weight,
            'dimensions'        => $this->dimensions,
            'brand'             => $this->brand,
            'tags'              => $this->tags,
            'is_featured'       => $this->is_featured,
            'is_active'         => $this->is_active,
            'is_on_sale'        => $this->isOnSale(),
            'is_in_stock'       => $this->isInStock(),
            'is_low_stock'      => $this->isLowStock(),
            'meta_title'        => $this->meta_title,
            'meta_description'  => $this->meta_description,
            'category'          => $this->whenLoaded('category'),
            'agent'             => $this->whenLoaded('agent', function () {
                return [
                    'id'    => $this->agent->id,
                    'name'  => $this->agent->name,
                    'email' => $this->agent->email,
                    'phone' => $this->agent->phone,
                ];
            }),
            'images'            => ProductImageResource::collection($this->whenLoaded('productImages')),
            'primary_image'     => new ProductImageResource($this->whenLoaded('primaryImage')),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
