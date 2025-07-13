<?php

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Enums\OrderStatus;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\InsufficientQuantityException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\InsufficientFundsException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Arr;

class OrderService implements OrderServiceInterface
{
    protected ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function createOrder(int $userId, array $items): Order
    {
        return $this->db->transaction(function () use ($userId, $items) {
            $productIds = collect($items)->pluck('product_id')->toArray();

            $user = User::query()->where('id', $userId)->lockForUpdate()->first();

            if ($user === null) {
                throw new EntityNotFoundException("Пользователь не найден");
            }

            $order = new Order();
            $order->user_id = $userId;
            $order->status = OrderStatus::NEW;
            $products = Product::query()->whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
            $totalPrice = 0.0;
            foreach ($items as $item) {
                $productId = Arr::get($item, 'product_id');
                $product = $products->get($productId);
                if ($product === null) {
                    throw new ProductNotFoundException($productId);
                }
                $quantity = (int)Arr::get($item, 'quantity');
                if ($product->quantity - $product->reserved_quantity < $quantity) {
                    throw new InsufficientQuantityException($productId);
                }
                $totalPrice += $quantity * $product->price;
            }

            if ($user->money < $totalPrice) {
                throw new InsufficientFundsException();
            }

            $pivot = [];

            foreach ($items as $item) {
                $productId = Arr::get($item, 'product_id');
                $product = $products->get($productId);
                $quantity = (int)Arr::get($item, 'quantity');
                $product->increment('reserved_quantity', $quantity);
                $pivot[$productId] = [
                    'quantity' => $quantity,
                    'price' => $product->price
                ];
            }

            $order->save();
            $order->products()->attach($pivot);

            return $order->load('products');
        });

    }

    public function approveOrder(Order $order)
    {
        // TODO: Implement approveOrder() method.
    }
}
