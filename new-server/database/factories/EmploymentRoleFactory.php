<?php

namespace Database\Factories;

use App\Models\Employment;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmploymentRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "employment_id" => Employment::factory(),
            "role_id" => Role::factory(),
        ];
    }
}
