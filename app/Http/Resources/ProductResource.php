<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'category' => [
                'id' => $this->category_id,
                'name' => $this->category->name
            ]
        ];
    }
}
