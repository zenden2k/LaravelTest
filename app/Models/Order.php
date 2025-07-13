<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('quantity', 'price');
    }

    protected $casts = [
        'status' => OrderStatus::class,
    ];
}
