<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // create a category with a random name and description
            'name' => fake()->randomElement(['Work', 'Personal', 'Shopping', 'Fitness', 'Travel'])
        ];
    }
}
