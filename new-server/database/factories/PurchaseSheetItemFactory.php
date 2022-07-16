<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\PurchaseSheet;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseSheetItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "purchase_sheet_id" => PurchaseSheet::factory(),
            "item_id" => Item::factory(),
            "quantity" => $this->faker->randomNumber(3, false),
            "price" => $this->faker->randomNumber(5, true),
            "discount" => $this->faker->randomNumber(3, false),
            "discount_type" => $this->faker->randomElement(["percent", "amount"]),
            "total" => $this->faker->randomNumber(5, true),
        ];
    }
}
