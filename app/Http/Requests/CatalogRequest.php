<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CatalogRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:0',
            'sort' => 'sometimes|string|in:id,name,price,created_at',
            'direction' => 'sometimes|string|in:asc,desc',
            'per_page' => 'sometimes|integer|min:1|max:50'
        ];
    }
}
