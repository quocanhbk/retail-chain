<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetDeletedEmployeesTest extends TestCase
{
    use RefreshDatabase;

    public function testGetDeletedEmployeesUnauthenticated()
    {
        $response = $this->get("/api/employee/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetDeletedEmployeesAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetDeletedEmployeesAsAdmin()
    {
        $store = Store::find(1);

        $store->employees()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/employee/deleted");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "email"]]);
    }

    public function testGetDeletedEmployeesWithPagination()
    {
        $store = Store::find(1);

        $store->employees()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/employee/deleted?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "email"]]);

        $response->assertJsonCount(1);
    }

    public function testGetDeletedEmployeesWithSearch()
    {
        $store = Store::find(1);

        $employee = $store->employees->first();

        $store->employees()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/employee/deleted?search={$employee->name}");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "email"]]);

        $response->assertJsonFragment(["name" => $employee->name]);
    }
}
