<?php

namespace App\Exceptions;

class InsufficientFundsException extends OrderException
{
    public function __construct()
    {
        parent::__construct('Недостаточно средств у покупателя', 400);
    }

    public function toArray(): array
    {
        return [
            'error' => 'insufficient_funds',
            'message' => $this->getMessage()
        ];
    }
}
