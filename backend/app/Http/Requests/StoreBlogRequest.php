<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for storing a new blog
 */
class StoreBlogRequest extends FormRequest
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
        return [
            'title'            => ['required', 'string', 'max:255'],
            'excerpt'          => ['nullable', 'string'],
            'content'          => ['required', 'string'],
            'category_id'      => ['nullable', 'exists:categories,id'],
            'featured_image'   => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords'    => ['nullable', 'string'],
            'is_published'     => ['boolean'],
            'published_at'     => ['nullable', 'date'],
            'product_ids'      => ['nullable', 'array'],
            'product_ids.*'    => ['exists:products,id'],
        ];
    }
}
