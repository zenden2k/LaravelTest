<?php

namespace App\Http\Controllers\Api;

use App\Contracts\OrderServiceInterface;
use App\Http\Requests\CatalogRequest;
use App\Http\Requests\CreateOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class OrderController extends BaseController
{
    protected OrderServiceInterface $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    // POST /create-order
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = Arr::get($validated, 'user_id');
        $products = Arr::get($validated, 'products');
        $products = $this->orderService->createOrder($userId, $products);
        return $this->sendResponse($products);
    }
}
