<?php

namespace App\Http\Controllers\Api;

use App\Contracts\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Arr;

class OrderController extends Controller
{
    protected OrderServiceInterface $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    // POST /create-order
    public function store(CreateOrderRequest $request): OrderResource
    {
        $validated = $request->validated();
        $userId = Arr::get($validated, 'user_id');
        $products = Arr::get($validated, 'products');
        $order = $this->orderService->createOrder($userId, $products);
        return new OrderResource($order);
    }
}
