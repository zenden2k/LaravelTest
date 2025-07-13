<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CatalogRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends BaseController
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    // GET /catalog
    public function index(CatalogRequest $request): JsonResponse
    {
        //return '';
        $products = $this->productService->getAllProducts($request->validated());
        return $this->sendResponse($products);
    }
}
