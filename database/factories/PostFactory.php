<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'title' => $this->faker->sentence,
            'slug' => Str::slug($this->faker->unique()->sentence),
            'content' => $this->faker->text(),
            'metadata' => json_encode([
                'author' => $this->faker->name,
                'image' => $this->faker->uuid(),
            ]),
        ];
    }
}
