<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_change_password_authenticated()
    {
        $response = $this->put("/api/employee/password");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_change_password_successfully()
    {
        $employee = (object) Employee::factory()->create([
            "password" => Hash::make("password"),
        ]);

        $response = $this->actingAs($employee)->put("/api/employee/password", [
            "current_password" => "password",
            "new_password" => "new-password",
            "new_password_confirmation" => "new-password",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertTrue(Hash::check("new-password", $employee->fresh()->password));
    }

    public function test_change_password_invalid_input()
    {
        $employee = (object) Employee::factory()->create([
            "password" => Hash::make("password"),
        ]);

        $response = $this->actingAs($employee)->put("/api/employee/password", [
            "current_password" => "invalid-password",
            "new_password" => "new-password",
            "new_password_confirmation" => "new-password",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);

        $this->assertFalse(Hash::check("new-password", $employee->fresh()->password));
    }

    public function test_change_password_no_password_confirmation()
    {
        $employee = (object) Employee::factory()->create([
            "password" => Hash::make("password"),
        ]);

        $response = $this->actingAs($employee)->put("/api/employee/password", [
            "current_password" => "password",
            "new_password" => "new-password",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);

        $this->assertFalse(Hash::check("new-password", $employee->fresh()->password));
    }
}
