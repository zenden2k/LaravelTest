<?php

namespace App\Exceptions;

class ProductNotFoundException extends OrderException
{
    protected int $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
        parent::__construct("Товар с ID {$productId} не найден", 404);
    }

    public function toArray(): array
    {
        return [
            'error' => 'product_not_found',
            'message' => $this->getMessage(),
            'product_id' => $this->productId,
        ];
    }
}
