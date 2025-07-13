<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product_id' => $this->id,
            'name' => $this->name,
            'price' => $this->pivot->price,
            'quantity' => $this->pivot->quantity,
        ];
    }
}
