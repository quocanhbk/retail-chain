<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->name,
            "email" => $this->faker->email,
            "phone" => $this->faker->phoneNumber,
            "code" => Str::uuid(),
            "point" => $this->faker->numberBetween(0, 100),
        ];
    }
}
