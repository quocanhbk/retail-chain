<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class LogoutAsEmployeeTest extends TestCase
{
    public function testLogOutWhileAlreadyLoggedOut()
    {
        $response = $this->post("/api/employee/logout");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testLogOutSuccessfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee/logout");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);
    }

    public function testLogOutWhileLoggedInAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/employee/logout");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }
}
