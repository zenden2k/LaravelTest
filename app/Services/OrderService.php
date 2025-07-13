<?php

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Enums\OrderStatus;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\InsufficientQuantityException;
use App\Exceptions\InvalidOrderStatusException;
use App\Exceptions\OrderException;
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

    /**
     * @param int $userId
     * @param array $items
     * @return Order
     * @throws OrderException|\Throwable
     */
    public function createOrder(int $userId, array $items): Order
    {
        return $this->db->transaction(function () use ($userId, $items) {
            $productIds = collect($items)->pluck('product_id')->toArray();

            $user = User::lockForUpdate()->find($userId);

            if ($user === null) {
                throw new EntityNotFoundException("Пользователь не найден");
            }

            $order = new Order();
            $order->user_id = $userId;
            $order->status = OrderStatus::NEW;
            $products = Product::query()->whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
            $totalAmount = 0.0;
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
                $totalAmount += $quantity * $product->price;
            }

            if ($user->money - $user->reserved_money < $totalAmount) {
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

            $user->increment('reserved_money', $totalAmount);
            $order->total_amount = $totalAmount;
            $order->save();
            $order->products()->attach($pivot);

            return $order->load('products');
        });

    }

    /**
     * @param int $orderId
     * @return mixed
     * @throws OrderException|\Throwable
     */
    public function approveOrder(int $orderId)
    {
        return $this->db->transaction(function () use ($orderId) {
            $order = Order::lockForUpdate()->find($orderId);
            if ($order === null) {
                throw new EntityNotFoundException("Заказ не найден");
            }
            if ($order->status != OrderStatus::NEW) {
                throw new InvalidOrderStatusException($orderId);
            }
            $user = User::lockForUpdate()->find($order->user_id);
            if ($user === null) {
                throw new EntityNotFoundException("Пользователь не найден");
            }

            if ($user->reserved_money < $order->total_amount) {
                throw new InsufficientFundsException();
            }

            foreach ($order->products()->lockForUpdate()->get() as $product) {
                $product->decrement('reserved_quantity', $product->pivot->quantity);
                $product->decrement('quantity', $product->pivot->quantity);
            }

            $user->decrement('reserved_money', $order->total_amount);
            $user->decrement('money', $order->total_amount);

            $order->status = OrderStatus::APPROVED;
            $order->save();
            return $order;
        });
    }
}
