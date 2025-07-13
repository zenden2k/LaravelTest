<?php

namespace App\Exceptions;

class InsufficientQuantityException extends OrderException
{
    protected int $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
        parent::__construct("Недостаточно товара с ID {$productId} на складе", 404);
    }

    public function toArray(): array
    {
        return [
            'error' => 'insufficient_quantity',
            'message' => $this->getMessage(),
            'product_id' => $this->productId,
        ];
    }

}
