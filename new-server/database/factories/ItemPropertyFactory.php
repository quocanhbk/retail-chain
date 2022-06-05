<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemPropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "item_id" => Item::factory(),
            "branch_id" => Branch::factory(),
            "quantity" => $this->faker->randomNumber(3, false),
            "sell_price" => $this->faker->randomNumber(5, true),
            "base_price" => $this->faker->randomNumber(5, true),
            "last_purchase_price" => $this->faker->randomNumber(5, true),
        ];
    }
}
