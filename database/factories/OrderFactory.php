<?php

namespace Database\Factories;

use App\Models\OrderStatus;
use App\Models\Payment;
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
        $user = User::inRandomOrder()->first();
        $status = OrderStatus::inRandomOrder()->first();
        $payment = Payment::inRandomOrder()->first();
        $products = Product::inRandomOrder()->limit($this->faker->numberBetween(1, 10))->get()->map(function ($product) {
            return [
                'product' => $product->uuid,
                'quantity' => $this->faker->numberBetween(1, 5),
            ];
        })->toArray();

        $address = [
            'billing' => $this->faker->address(),
            'shipping' => $this->faker->address(),
        ];

        return [
            'user_id' => $user->id,
            'order_status_id' => $status->id,
            'payment_id' => $payment->id,
            'uuid' => $this->faker->uuid(),
            'products' => json_encode($products),
            'address' => json_encode($address),
            'delivery_fee' => $this->faker->randomFloat(2, 0, 50),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'shipped_at' => $this->faker->dateTimeBetween('2020-01-01', '2023-12-31'),
        ];
    }
}
