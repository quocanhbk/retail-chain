<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetDeletedEmployeesTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_deleted_employees_unauthenticated()
    {
        $response = $this->get("/api/employee/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_deleted_employees_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_deleted_employees_as_admin()
    {
        $store = Store::first();

        $store->employees()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/employee/deleted");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "email"]]);
    }

    public function test_get_deleted_employees_with_pagination()
    {
        $store = Store::first();

        $store->employees()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/employee/deleted?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "email"]]);

        $response->assertJsonCount(1);
    }

    public function test_get_deleted_employees_with_search()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        $store->employees()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/employee/deleted?search={$employee->name}");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "email"]]);

        $response->assertJsonCount(1);
    }
}
