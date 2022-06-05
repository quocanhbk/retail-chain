<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseSheetFactory extends Factory
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
            "supplier_id" => Supplier::factory(),
            "discount" => $this->faker->randomNumber(3, false),
            "discount_type" => $this->faker->randomElement(["percent", "cash"]),
            "total" => $this->faker->randomNumber(5, true),
            "paid_amount" => $this->faker->randomNumber(5, true),
            "note" => $this->faker->sentence(),
        ];
    }
}
