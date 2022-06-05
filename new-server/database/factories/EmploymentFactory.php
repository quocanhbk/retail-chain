<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmploymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "employee_id" => Employee::factory(),
            "branch_id" => Branch::factory(),
            "from" => now(),
            "to" => null,
        ];
    }
}
