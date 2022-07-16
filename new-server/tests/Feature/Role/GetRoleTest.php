<?php

namespace Tests\Feature\Role;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Store;
use Tests\TestCase;

class GetRoleTest extends TestCase
{
    public function testGetRoleUnauthenticated()
    {
        $response = $this->get("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetRoleAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetRoleAsAdmin()
    {
        $store = Store::find(1);

        $role = Role::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->get("/api/role/{$role->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "description", "created_at", "updated_at"]);
    }

    public function testGetRoleNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/role/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
