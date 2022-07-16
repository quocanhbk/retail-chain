<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class GetEmployeeTest extends TestCase
{
    public function testGetEmployeeUnauthenticated()
    {
        $response = $this->get("/api/employee/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetEmployeeUnauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetEmployeeAsAdmin()
    {
        $store = Store::find(1);

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

    public function testGetEmployeeNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/employee/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
