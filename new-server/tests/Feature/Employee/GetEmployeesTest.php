<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetEmployeesTest extends TestCase
{
    public function test_get_employees_unauthenticated()
    {
        $response = $this->get("/api/employee");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_employees_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_employees_by_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/employee");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            [
                "id",
                "name",
                "email",
                "phone",
                "gender",
                "birthday",
                "employment" => [
                    "branch_id",
                    "roles" => [
                        [
                            "role" => ["name"],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_get_employees_by_branch()
    {
        $store = Store::first();

        $branch = $store->branches->first();

        $response = $this->actingAs($store, "stores")->get("/api/employee?branch_id={$branch->id}");

        $response->assertStatus(200);

        $response->assertJsonFragment(["branch_id" => $branch->id]);
    }

    public function test_get_employees_with_pagination()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/employee?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }

    public function test_get_employees_with_search()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        $response = $this->actingAs($store, "stores")->get("/api/employee?search={$employee->name}");

        $response->assertStatus(200);

        $response->assertJsonFragment(["name" => $employee->name]);

        $response->assertJsonCount(1);
    }
}
