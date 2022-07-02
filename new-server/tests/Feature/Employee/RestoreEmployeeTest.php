<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RestoreEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_restore_employee_unauthenticated()
    {
        $response = $this->post("/api/employee/1/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_restore_employee_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee/1/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_restore_employee_as_admin()
    {
        $store = Store::first();

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

    public function test_restore_employee_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/employee/9999/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_restore_undeleted_employee()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        $response = $this->actingAs($store, "stores")->post("/api/employee/{$employee->id}/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
