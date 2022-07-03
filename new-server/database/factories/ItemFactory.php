<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "store_id" => Store::factory(),
            "barcode" => $this->faker->ean8(),
            "code" => $this->faker->unique()->word(),
            "name" => $this->faker->word(),
            "image" => $this->faker->imageUrl(),
            "category_id" => Category::factory(),
        ];
    }
}
