<?php

namespace Tests\Feature\Role;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateRoleTest extends TestCase
{
    use RefreshDatabase;

    public function testUpdateRoleUnauthenticated()
    {
        $response = $this->put("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateRoleAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->put("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateRoleAsAdmin()
    {
        $store = Store::find(1);

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

    public function testUpdateRoleNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->put("/api/role/9999", [
            "name" => "Role Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateRoleWithLongName()
    {
        $store = Store::find(1);

        $role = Role::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->put("/api/role/{$role->id}", [
            "name" => str_repeat("a", 256),
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
