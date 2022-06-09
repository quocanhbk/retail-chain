<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
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
            "name" => $this->faker->name(),
            "address" => $this->faker->address(),
            "code" => $this->faker->word(),
            "phone" => $this->faker->phoneNumber(),
            "email" => $this->faker->unique()->safeEmail(),
            "tax_number" => $this->faker->word(),
            "note" => $this->faker->sentence(),
        ];
    }
}
