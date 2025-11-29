<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'manager_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the client should not have a manager.
     */
    public function withoutManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => null,
        ]);
    }
}
