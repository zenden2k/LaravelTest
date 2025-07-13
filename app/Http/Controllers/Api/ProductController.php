<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ProductServiceInterface;
use App\Http\Requests\CatalogRequest;
use Illuminate\Http\JsonResponse;

class ProductController extends BaseController
{
    protected ProductServiceInterface $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    // GET /catalog
    public function index(CatalogRequest $request): JsonResponse
    {
        $products = $this->productService->getAllProducts($request->validated());
        return $this->sendResponse($products);
    }
}
