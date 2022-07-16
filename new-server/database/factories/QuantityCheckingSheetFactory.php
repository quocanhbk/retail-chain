<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuantityCheckingSheetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "code" => $this->faker->unique()->randomNumber(5, true),
            "employee_id" => Employee::factory(),
            "branch_id" => Branch::factory(),
            "note" => $this->faker->sentence(),
        ];
    }
}
