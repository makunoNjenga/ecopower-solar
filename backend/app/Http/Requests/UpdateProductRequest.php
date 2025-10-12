<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product')->id ?? null;

        return [
            'name'              => ['sometimes', 'string', 'max:255'],
            'description'       => ['sometimes', 'string'],
            'short_description' => ['nullable', 'string'],
            'sku'               => ['sometimes', 'string', 'unique:products,sku,' . $productId],
            'price'             => ['sometimes', 'numeric', 'min:0'],
            'sale_price'        => ['nullable', 'numeric', 'min:0'],
            'stock_quantity'    => ['sometimes', 'integer', 'min:0'],
            'min_stock_level'   => ['nullable', 'integer', 'min:0'],
            'weight'            => ['nullable', 'numeric', 'min:0'],
            'dimensions'        => ['nullable', 'array'],
            'category_id'       => ['sometimes', 'exists:categories,id'],
            'brand'             => ['nullable', 'string', 'max:255'],
            'tags'              => ['nullable', 'array'],
            'is_featured'       => ['boolean'],
            'is_active'         => ['boolean'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string'],
        ];
    }
}
