<?php

namespace Tests\Feature\Role;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_role_unauthenticated()
    {
        $response = $this->put("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_role_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->put("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_role_as_admin()
    {
        $store = Store::first();

        $role = Role::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->put("/api/role/{$role->id}", [
            "name" => "Role Updated",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "description", "created_at", "updated_at"]);

        $response->assertJson([
            "id" => $role->id,
            "name" => "Role Updated",
        ]);

        $this->assertDatabaseHas("roles", [
            "id" => $role->id,
            "name" => "Role Updated",
        ]);
    }

    public function test_update_role_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->put("/api/role/9999", [
            "name" => "Role Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_role_with_long_name()
    {
        $store = Store::first();

        $role = Role::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->put("/api/role/{$role->id}", [
            "name" => str_repeat("a", 256),
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
