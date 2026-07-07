<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\Task;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Fixed, known user you'll actually log in and test with
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);

        // Create 5 categories for the user
        $categories = Category::factory()->count(5)->for($user)->create();

        // Create 10 tasks for each category
        Task::factory()
        ->count(10)
        ->for($user)
        ->for($categories->random())
        ->create()
        ->each(function ($task) use ($categories) {
            $task->update(['category_id' => $categories->random()->id]);
        });
    }
}
