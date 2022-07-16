<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class GetMeEmployeeTest extends TestCase
{
    public function testGetMeUnauthenticated()
    {
        $response = $this->get("/api/employee/me");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetMeAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/employee/me");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetMeAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/me");

        $response->assertStatus(200);

        $response->dump();

        $response->assertJsonStructure([
            "id",
            "name",
            "email",
            "avatar_key",
            "store_id",
            "employment" => ["branch_id", "roles" => [["role" => ["name"]]]],
            "permissions" => [["action_slug", "action"]],
        ]);
    }
}
