<?php

namespace App\Exceptions;

class InsufficientQuantityException extends OrderException
{
    protected int $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
        parent::__construct("Недостаточно товара с ID {$productId} на складе");
    }

    public function toArray(): array
    {
        return [
            'error' => 'insufficient_quantity',
            'product_id' => $this->productId,
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
            'product_id' => $this->productId
        ];
    }
}
