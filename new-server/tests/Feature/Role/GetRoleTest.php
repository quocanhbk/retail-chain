<?php

namespace Tests\Feature\Role;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_role_unauthenticated()
    {
        $response = $this->get("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_role_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/role/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_role_as_admin()
    {
        $store = Store::first();

        $role = Role::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->get("/api/role/{$role->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "description", "created_at", "updated_at"]);
    }

    public function test_get_role_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/role/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
