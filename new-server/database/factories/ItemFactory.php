<?php

namespace Database\Factories;

use App\Models\Category;
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
            "barcode" => $this->faker->ean8(),
            "code" => $this->faker->unique()->word(),
            "name" => $this->faker->word(),
            "image" => $this->faker->imageUrl(),
            "category_id" => null,
        ];
    }
}
