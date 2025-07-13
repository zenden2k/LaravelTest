<?php

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Arr;

class OrderService implements OrderServiceInterface
{
    protected ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function createOrder($userId, $items)
    {
        return $this->db->transaction(function () use ($userId, $items) {
            $order = new Order();
            $order->user_id = $userId;

            $productIds = collect($items)->pluck('product_id')->toArray();

            $products = Product::query()->whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $item) {
                $productId = Arr::get($item, 'product_id');
                $product = $products->get($productId);
                if ($product === null) {
                    throw new \Exception(sprintf("Товар с ID %d не найден", $productId));
                }
                $quantity = (int)Arr::get($item, 'quantity');
                if ($product->quantity - $product->reserved_quantity < $quantity) {
                    throw new \Exception(sprintf("Недостаточно товара с ID %d", $productId));
                }
            }



            return $order;
        });

    }

    public function approveOrder(Order $order)
    {
        // TODO: Implement approveOrder() method.
    }
}
