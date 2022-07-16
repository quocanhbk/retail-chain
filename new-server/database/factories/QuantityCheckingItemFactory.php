<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\QuantityCheckingSheet;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuantityCheckingItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "quantity_checking_sheet_id" => QuantityCheckingSheet::factory(),
            "item_id" => Item::factory(),
            "expected_quantity" => $this->faker->randomNumber(3, false),
            "actual_quantity" => $this->faker->randomNumber(3, false),
            "total" => $this->faker->randomNumber(5, true),
        ];
    }
}
