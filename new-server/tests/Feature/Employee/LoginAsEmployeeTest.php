<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginAsEmployeeTest extends TestCase
{
    public function testLoginWhileAlreadyLoggedInAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/employee/login");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testLoginWhileAlreadyLoggedInAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee/login");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testLoginSuccessfully()
    {
        $store = Store::find(1);

        $employee = Employee::factory()->create([
            "store_id" => $store->id,
            "password" => Hash::make("password"),
        ]);

        $response = $this->post("/api/employee/login", [
            "email" => $employee->email,
            "password" => "password",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "email", "phone"]);

        $response->assertCookie("bkrm_session");
    }

    public function testLoginWithInvalidCredentials()
    {
        $store = Store::find(1);

        $employee = Employee::factory()->create([
            "store_id" => $store->id,
            "password" => Hash::make("password"),
        ]);

        $response = $this->post("/api/employee/login", [
            "email" => $employee->email,
            "password" => "invalid",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testLoginWithInvalidEmail()
    {
        $response = $this->post("/api/employee/login", [
            "email" => "invalid",
            "password" => "password",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }
}
