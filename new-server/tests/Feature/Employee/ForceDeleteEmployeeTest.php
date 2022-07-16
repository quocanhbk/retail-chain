<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForceDeleteEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function testForceDeleteEmployeeUnauthenticated()
    {
        $response = $this->delete("/api/employee/1/force");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testForceDeleteEmployeeAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->delete("/api/employee/1/force");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testForceDeleteUndeletedEmployee()
    {
        $store = Store::find(1);

        $employee = Employee::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->delete("/api/employee/{$employee->id}/force");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDeleted("employees", [
            "id" => $employee->id,
        ]);

        $this->assertDatabaseMissing("employments", [
            "employee_id" => $employee->id,
        ]);
    }

    public function testForceDeleteDeletedEmployee()
    {
        $store = Store::find(1);

        $employee = Employee::where("store_id", $store->id)->first();

        $employee->delete();

        $response = $this->actingAs($store, "stores")->delete("/api/employee/{$employee->id}/force");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("employees", [
            "id" => $employee->id,
        ]);

        $this->assertDatabaseMissing("employments", [
            "employee_id" => $employee->id,
        ]);
    }

    public function testForceDeleteEmployeeNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->delete("/api/employee/9999/force");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
