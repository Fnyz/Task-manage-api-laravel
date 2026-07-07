<?php

namespace Database\Factories;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // create a task with a random title and description
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'is_completed' => fake()->boolean(30),
        ];
    }
}
