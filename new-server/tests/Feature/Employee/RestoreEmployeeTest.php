<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestoreEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function testRestoreEmployeeUnauthenticated()
    {
        $response = $this->post("/api/employee/1/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testRestoreEmployeeAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee/1/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testRestoreEmployeeAsAdmin()
    {
        $store = Store::find(1);

        $employee = Employee::where("store_id", $store->id)->first();

        $employee->delete();

        $response = $this->actingAs($store, "stores")->post("/api/employee/{$employee->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("employees", [
            "id" => $employee->id,
            "deleted_at" => null,
        ]);

        $this->assertDatabaseHas("employments", [
            "employee_id" => $employee->id,
            "to" => null,
        ]);

        $this->assertNotNull($employee->fresh()->employment);
    }

    public function testRestoreEmployeeNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/employee/9999/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testRestoreUndeletedEmployee()
    {
        $store = Store::find(1);

        $employee = $store->employees->first();

        $response = $this->actingAs($store, "stores")->post("/api/employee/{$employee->id}/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
