<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316', '#ec4899'];
        $icons = ['folder', 'briefcase', 'book', 'star', 'code', 'target', 'heart'];

        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'color' => fake()->randomElement($colors),
            'icon' => fake()->randomElement($icons),
            'is_favorite' => false,
            'order' => fake()->numberBetween(0, 10),
            'view_mode' => 'list',
        ];
    }

    public function favorite(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_favorite' => true,
        ]);
    }
}
