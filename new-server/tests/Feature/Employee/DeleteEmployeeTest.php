<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function testDeleteEmployeeUnauthenticated()
    {
        $response = $this->delete("/api/employee/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteEmployeeAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->delete("/api/employee/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteEmployeeAsAdmin()
    {
        $store = Store::find(1);

        $employee = Employee::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->delete("/api/employee/{$employee->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertSoftDeleted("employees", [
            "id" => $employee->id,
        ]);

        $this->assertDatabaseHas("employments", [
            "employee_id" => $employee->id,
            "to" => date("Y/m/d"),
        ]);
    }

    public function testDeleteEmployeeWithForce()
    {
        $store = Store::find(1);

        $employee = Employee::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->delete("/api/employee/{$employee->id}?force=true");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDeleted("employees", [
            "id" => $employee->id,
        ]);

        $this->assertDatabaseMissing("employments", [
            "employee_id" => $employee->id,
            "to" => date("Y/m/d"),
        ]);
    }

    public function testDeleteEmployeeNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->delete("/api/employee/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
