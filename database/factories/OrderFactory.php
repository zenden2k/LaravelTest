<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'status' => fake()->randomElement(OrderStatus::cases()),
            'total_amount' => 0.0,
        ];
    }

    public function withRandomProducts(): static
    {
        return $this->afterCreating(function (Order $order) {
            $products = Product::inRandomOrder()
                ->limit(fake()->numberBetween(1, 5))
                ->get();

            $totalAmount = 0.0;
            $pivot = [];

            foreach ($products as $product) {
                $quantity = fake()->numberBetween(1, 3);
                $price = $product->price;

                $pivot[$product->id] = [
                    'quantity' => $quantity,
                    'price' => $price,
                ];

                $totalAmount += $quantity * $price;
            }

            $order->products()->attach($pivot);

            $order->update(['total_amount' => $totalAmount]);
        });
    }

    // Cоздание заказа с конкретными товарами
    public function withProducts(array $productData): static
    {
        return $this->afterCreating(function (Order $order) use ($productData) {
            $totalAmount = 0.0;
            $pivot = [];

            foreach ($productData as $data) {
                $product = Product::find($data['product_id']);
                $quantity = $data['quantity'];
                $price = $product->price;

                $pivot[$product->id] = [
                    'quantity' => $quantity,
                    'price' => $price,
                ];

                $totalAmount += $quantity * $price;
            }

            $order->products()->attach($pivot);
            $order->update(['total_amount' => $totalAmount]);
        });
    }
}
