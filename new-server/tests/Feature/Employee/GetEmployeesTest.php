<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class GetEmployeesTest extends TestCase
{
    public function testGetEmployeesUnauthenticated()
    {
        $response = $this->get("/api/employee");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetEmployeesUnauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetEmployeesByAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/employee");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "email", "phone", "gender", "birthday", "employment"]]);
    }

    public function testGetEmployeesByBranch()
    {
        $store = Store::find(1);

        $branch = $store->branches->first();

        $response = $this->actingAs($store, "stores")->get("/api/employee?branch_id={$branch->id}");

        $response->assertStatus(200);

        $response->assertJsonFragment(["branch_id" => $branch->id]);
    }

    public function testGetEmployeesWithPagination()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/employee?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }

    public function testGetEmployeesWithSearch()
    {
        $store = Store::find(1);

        $employee = $store->employees->first();

        $response = $this->actingAs($store, "stores")->get("/api/employee?search={$employee->name}");

        $response->assertStatus(200);

        $response->assertJsonFragment(["name" => $employee->name]);
    }
}
