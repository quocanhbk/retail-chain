<?php

namespace Tests\Feature\Role;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_roles_unauthenticated()
    {
        $response = $this->get("/api/role");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_roles_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/role");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_roles_as_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/role");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "description", "created_at", "updated_at"]]);
    }

    public function test_get_roles_with_search()
    {
        $store = Store::first();

        $role = Role::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->get("/api/role?search={$role->name}");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "description", "created_at", "updated_at"]]);

        $response->assertJsonFragment([
            "id" => $role->id,
            "name" => $role->name,
            "description" => $role->description,
        ]);

        $response->assertJsonCount(1);
    }

    public function test_get_roles_with_pagination()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/role?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "description", "created_at", "updated_at"]]);

        $response->assertJsonCount(1);
    }
}
