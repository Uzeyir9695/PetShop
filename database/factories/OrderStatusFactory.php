<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderStatus>
 */
class OrderStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a random order status title from an array
        $statusTitles = ['open', 'pending payment', 'paid', 'shipped', 'cancelled'];
        $statusTitle = $this->faker->randomElement($statusTitles);

        return [
            'uuid' => $this->faker->uuid(),
            'title' => $statusTitle,
        ];
    }
}
