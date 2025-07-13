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
