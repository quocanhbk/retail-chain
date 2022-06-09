<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->name(),
            "address" => $this->faker->address(),
            "image" => $this->faker->imageUrl(),
            "image_key" => $this->faker->word(),
            "store_id" => Store::factory(),
        ];
    }
}
