<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = Category::inRandomOrder()->first();
        $metadata = [
            'brand' => $this->faker->uuid(),
            'image' => $this->faker->uuid()
        ];

        return [
            'category_uuid' => $category->uuid,
            'uuid' => $this->faker->uuid(),
            'title' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 0.1, 1000),
            'description' => $this->faker->paragraph(),
            'metadata' => json_encode($metadata),
        ];
    }
}
