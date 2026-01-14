<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResearchProject>
 */
class ResearchProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'supervisor' => fake()->name(),
            'credits' => fake()->numberBetween(5, 10),
            'max_students' => fake()->numberBetween(1, 5),
            'start_date' => null,
            'end_date' => null,
        ];
    }
}
