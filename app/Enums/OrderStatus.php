<?php

namespace App\Enums;

enum OrderStatus: int
{
    case NEW = 0;
    case APPROVED = 1;
    case CANCELLED = 2;

    public function label(): string
    {
        return match($this) {
            self::NEW => 'Ожидает подтверждения',
            self::APPROVED => 'Подтвержден',
            self::CANCELLED => 'Отменен',
        };
    }
}
