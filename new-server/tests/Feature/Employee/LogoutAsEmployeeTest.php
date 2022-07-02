<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogoutAsEmployeeTest extends TestCase
{
    public function test_log_out_while_already_logged_out()
    {
        $response = $this->post("/api/employee/logout");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_log_out_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee/logout");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);
    }

    public function test_log_out_while_logged_in_as_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/employee/logout");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }
}
