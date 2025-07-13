<?php

namespace App\Exceptions;

class EntityNotFoundException extends OrderException
{
    public function __construct($message)
    {
        parent::__construct($message, 404);
    }

    public function toArray(): array
    {
        return [
            'error' => 'entity_not_found'
        ];
    }
}
