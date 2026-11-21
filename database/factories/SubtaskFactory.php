<?php

namespace Database\Factories;

use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subtask>
 */
class SubtaskFactory extends Factory
{
    protected $model = Subtask::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => fn (array $attributes) => Task::find($attributes['task_id'])->user_id ?? User::factory(),
            'title' => fake()->sentence(3),
            'is_completed' => false,
            'position' => 0,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }
}
