<?php

namespace App\Services;

use App\Contracts\ProductServiceInterface;
use App\Models\Product;
use Illuminate\Support\Arr;

class ProductService implements ProductServiceInterface
{
    public function getAllProducts(array $filters)
    {
        $query = Product::query();

        if (Arr::has($filters, 'name')) {
            $query->whereLike('name', $filters['name']);
        }

        if (Arr::has($filters, 'category_id')) {
            $query->where('category_id', $filters['category_id']);
        }

        if (Arr::has($filters, 'min_price')) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (Arr::has($filters, 'max_price')) {
            $query->where('price', '<=', $filters['max_price']);
        }
        $query->with('category');

        $sortField = Arr::get($filters, 'sort', 'id');
        $sortDirection = Arr::get($filters, 'direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = Arr::get($filters, 'per_page', config('app.pagesize', 20));

        return $query->paginate($perPage);
    }
}
