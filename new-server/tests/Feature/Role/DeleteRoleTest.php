<?php

namespace Tests\Feature\Role;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteRoleTest extends TestCase
{
    use RefreshDatabase;

    public function testDeleteRoleUnauthenticated()
    {
        $response = $this->delete("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteRoleAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->delete("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteActiveRole()
    {
        $store = Store::find(1);

        $active_role = Role::where("store_id", $store->id)
            ->whereRelation("employmentRoles.employment", "to", null)
            ->first();

        $response = $this->actingAs($store, "stores")->delete("/api/role/{$active_role->id}");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteInactiveRole()
    {
        $store = Store::find(1);

        $inactive_role = Role::where("store_id", $store->id)
            ->whereDoesntHave("employmentRoles.employment", function (Builder $query) {
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
