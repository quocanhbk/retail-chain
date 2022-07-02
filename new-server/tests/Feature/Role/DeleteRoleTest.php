<?php

namespace Tests\Feature\Role;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_role_unauthenticated()
    {
        $response = $this->delete("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_role_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->delete("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_active_role()
    {
        $store = Store::first();

        $active_role = Role::where("store_id", $store->id)
            ->whereHas("employmentRoles.employment", function ($query) {
                $query->where("to", null);
            })
            ->first();

        $response = $this->actingAs($store, "stores")->delete("/api/role/{$active_role->id}");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_inactive_role()
    {
        $store = Store::first();

        $inactive_role = Role::where("store_id", $store->id)
            ->whereDoesntHave("employmentRoles.employment", function ($query) {
                $query->where("to", null);
            })
            ->first();

        $response = $this->actingAs($store, "stores")->delete("/api/role/{$inactive_role->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("roles", [
            "id" => $inactive_role->id,
        ]);

        $this->assertDatabaseMissing("employment_roles", [
            "role_id" => $inactive_role->id,
        ]);

        $this->assertDatabaseMissing("permission_roles", [
            "role_id" => $inactive_role->id,
        ]);
    }
}
