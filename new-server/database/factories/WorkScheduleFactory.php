<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "shift_id" => Shift::factory(),
            "employee_id" => Employee::factory(),
            "date" => $this->faker->date("Y-m-d"),
            "note" => $this->faker->text(),
        ];
    }
}
