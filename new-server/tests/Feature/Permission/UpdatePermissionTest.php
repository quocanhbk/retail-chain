<?php

namespace Tests\Feature\Permission;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function testUpdatePermissionUnauthenticated()
    {
        $response = $this->put("/api/permission/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdatePermissionAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->put("/api/permission/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdatePermissionNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->put("/api/permission/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdatePermissionInvalidInput()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->put("/api/permission/1");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdatePermisssionInputNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->put("/api/permission/1", [
            "role_ids" => [9999],
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdatePermissionAsAdmin()
    {
        $store = Store::find(1);

        $permission = Permission::first();

        $role_ids = $store
            ->roles()
            ->get()
            ->pluck("id")
            ->toArray();

        $response = $this->actingAs($store, "stores")->put("/api/permission/{$permission->id}", [
            "role_ids" => $role_ids,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        foreach ($role_ids as $role_id) {
            $this->assertDatabaseHas("permission_roles", [
                "store_id" => $store->id,
                "permission_id" => $permission->id,
                "role_id" => $role_id,
            ]);
        }
    }
}
