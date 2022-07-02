<?php

namespace Tests\Feature\Role;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_role_unauthenticated()
    {
        $response = $this->post("/api/role", [
            "name" => "Test Role",
            "description" => "Test Role Description",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_role_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/role", [
            "name" => "Test Role",
            "description" => "Test Role Description",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_role_as_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/role", [
            "name" => "Test Role",
            "description" => "Test Role Description",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "description", "created_at", "updated_at"]);

        $this->assertDatabaseHas("roles", [
            "store_id" => $store->id,
            "name" => "Test Role",
            "description" => "Test Role Description",
        ]);
    }

    public function test_create_role_with_name_too_long()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/role", [
            "name" => "This name is kinda too long, isn't it? Yep, it is very very very long !!!",
            "description" => "Test Role Description",
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure(["message"]);
    }

    public function test_create_role_without_description()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/role", [
            "name" => "Test Role",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "created_at", "updated_at"]);

        $this->assertDatabaseHas("roles", [
            "store_id" => $store->id,
            "name" => "Test Role",
        ]);
    }
}
