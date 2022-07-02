<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class GetMeEmployeeTest extends TestCase
{
    public function test_get_me_unauthenticated()
    {
        $response = $this->get("/api/employee/me");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_me_as_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/employee/me");

        $response->assertStatus(401);
    }

    public function test_get_me_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/me");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "name",
            "email",
            "avatar_key",
            "store_id",
            "employment" => [
                "branch_id",
                "roles" => [
                    [
                        "role" => ["name"],
                    ],
                ],
            ],
        ]);
    }
}
