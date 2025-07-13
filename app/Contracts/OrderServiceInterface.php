<?php

namespace App\Contracts;

use App\Models\Order;

interface OrderServiceInterface
{
    public function createOrder(int $userId, array $items): Order;

    public function approveOrder(Order $order);
}
