<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'products' => [
                'required',
                'array',
                'min:1',
                'max:'.$this->getMaxProducts(),
                function ($attribute, $value, $fail) {
                    $this->validateUniqueProducts($value, $fail);
                }
            ],
            'products.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'products.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:'. $this->getMaxQuantity(),
            ],
        ];
    }

    protected function validateUniqueProducts(array $items, $fail): void
    {
        $productIds = collect($items)->pluck('product_id')->toArray();

        if (count($productIds) !== count(array_unique($productIds))) {
            $fail('В заказе не может быть дублирующихся товаров');
        }
    }

    public function messages(): array
    {
        return [
            'products.required' => 'Список товаров обязателен',
            'products.array' => 'Список товаров должен быть массивом',
            'products.min' => 'Заказ должен содержать хотя бы один товар',
            'products.max' => 'В заказе не может быть больше :max товаров',
            'products.*.product_id.required' => 'ID товара обязателен',
            'products.*.product_id.exists' => 'Товар с указанным ID не существует',
            'products.*.quantity.required' => 'Количество товара обязательно',
            'products.*.quantity.min' => 'Количество товара должно быть больше 0',
            'products.*.quantity.max' => 'Количество товара не может превышать :max',
        ];
    }

    private function getMaxProducts(): int
    {
        return config('app.max_products_per_order', 100);
    }

    private function getMaxQuantity(): int
    {
        return config('app.max_quantity_per_product', 100);
    }
}
