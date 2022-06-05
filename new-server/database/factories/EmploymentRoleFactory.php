<?php

namespace Database\Factories;

use App\Models\Employment;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmploymentRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "employment_id" => Employment::factory(),
            "role" => $this->faker->randomElement(["purchase", "sale", "manage"]),
        ];
    }
}
