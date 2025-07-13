<?php

namespace App\Exceptions;

class InvalidOrderStatusException extends OrderException
{
    protected int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
        parent::__construct("Недопустимый статус заказа");
    }

    public function toArray(): array
    {
        return [
            'error' => 'invalid_order_status',
            'order_id' => $this->orderId,
        ];
    }

    /**
     * Get the exception's context information.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'order_id' => $this->orderId
        ];
    }
}
