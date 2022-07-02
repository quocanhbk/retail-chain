<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginAsEmployeeTest extends TestCase
{
    public function test_login_while_already_logged_in_as_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/employee/login");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_login_while_already_logged_in_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee/login");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_login_successfully()
    {
        $employee = Employee::factory()->create([
            "password" => Hash::make("password"),
        ]);

        $response = $this->post("/api/employee/login", [
            "email" => $employee->email,
            "password" => "password",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "email", "phone"]);
    }

    public function test_login_with_invalid_credentials()
    {
        $employee = Employee::factory()->create([
            "password" => Hash::make("password"),
        ]);

        $response = $this->post("/api/employee/login", [
            "email" => $employee->email,
            "password" => "invalid",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_login_with_invalid_email()
    {
        $response = $this->post("/api/employee/login", [
            "email" => "invalid",
            "password" => "password",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }
}
