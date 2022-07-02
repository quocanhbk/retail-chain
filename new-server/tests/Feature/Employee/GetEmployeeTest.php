<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetEmployeeTest extends TestCase
{
    public function test_get_employee_unauthenticated()
    {
        $response = $this->get("/api/employee/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_employee_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_employee_as_admin()
    {
        $store = Store::first();

        $employee = $store->employees()->first();

        $response = $this->actingAs($store, "stores")->get("/api/employee/{$employee->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "name",
            "email",
            "phone",
            "employment" => [
                "branch_id",
                "roles" => [
                    [
                        "role" => ["name"],
                    ],
                ],
            ],
        ]);

        $response->assertJson([
            "id" => $employee->id,
            "name" => $employee->name,
            "email" => $employee->email,
            "phone" => $employee->phone,
            "employment" => [
                "branch_id" => $employee->employment->branch_id,
                "roles" => [
                    [
                        "role" => [
                            "name" => $employee->employment->roles->first()->role->name,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_get_employee_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/employee/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
