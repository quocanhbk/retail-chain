<?php

namespace Tests\Feature\Role;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Store;
use Tests\TestCase;

class GetRolesTest extends TestCase
{
    public function testGetRolesUnauthenticated()
    {
        $response = $this->get("/api/role");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetRolesAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/role");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetRolesAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/role");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "description", "created_at", "updated_at"]]);
    }

    public function testGetRolesWithSearch()
    {
        $store = Store::find(1);

        $role = Role::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->get("/api/role?search={$role->name}");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "description", "created_at", "updated_at"]]);

        $response->assertJsonFragment([
            "name" => $role->name,
        ]);
    }

    public function testGetRolesWithPagination()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/role?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "description", "created_at", "updated_at"]]);

        $response->assertJsonCount(1);
    }
}
