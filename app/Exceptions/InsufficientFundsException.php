<?php

namespace App\Exceptions;

class InsufficientFundsException extends OrderException
{
    public function __construct()
    {
        parent::__construct('Недостаточно средств у покупателя');
    }

    public function toArray(): array
    {
        return [
            'error' => 'insufficient_funds'
        ];
    }
}
