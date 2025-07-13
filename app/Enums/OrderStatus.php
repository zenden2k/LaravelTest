<?php

namespace App\Enums;

enum OrderStatus: int
{
    case NEW = 0;
    case CONFIRMED = 1;
    case CANCELLED = 2;

    public function label(): string
    {
        return match($this) {
            self::NEW => 'Ожидает подтверждения',
            self::CONFIRMED => 'Подтвержден',
            self::CANCELLED => 'Отменен',
        };
    }
}
