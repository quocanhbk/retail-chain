<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class EmployeeFactory extends Factory
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
            "email" => $this->faker->unique()->safeEmail(),
            "password" => Hash::make("123456"),
            "avatar" => $this->faker->imageUrl(),
            "avatar_key" => $this->faker->md5(),
            "phone" => $this->faker->phoneNumber(),
            "birthday" => $this->faker->date(),
            "gender" => null,
        ];
    }
}
