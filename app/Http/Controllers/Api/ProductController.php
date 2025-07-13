<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ProductServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogRequest;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    protected ProductServiceInterface $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    // GET /catalog
    public function index(CatalogRequest $request)
    {
        $products = $this->productService->getAllProducts($request->validated());
        return ProductResource::collection($products);
    }
}
