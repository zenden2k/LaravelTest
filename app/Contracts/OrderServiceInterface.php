<?php

namespace App\Contracts;

use App\Models\Order;

interface OrderServiceInterface
{
    public function createOrder($userId, $items);

    public function approveOrder(Order $order);
}
