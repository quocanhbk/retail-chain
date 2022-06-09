<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "branch_id" => Branch::factory(),
            "name" => $this->faker->name,
            "start_time" => $this->faker->time("H:i"),
            "end_time" => $this->faker->time("H:i"),
        ];
    }
}
