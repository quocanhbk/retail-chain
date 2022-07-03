<?php

namespace Tests\Feature\Category;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreateCategoryTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_create_category_unauthenticated()
    {
        $response = $this->post("/api/category", [
            "name" => "Test Category",
            "description" => "Test Category Description",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_category_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-category");

        $response = $this->actingAs($employee)->post("/api/category", [
            "name" => "Test Category",
            "description" => "Test Category Description",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_category_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-category");

        $response = $this->actingAs($employee)->post("/api/category", [
            "name" => "Test Category",
            "description" => "Test Category Description",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "description", "store_id", "created_at", "updated_at"]);

        $this->assertDatabaseHas("categories", [
            "name" => "Test Category",
            "description" => "Test Category Description",
            "store_id" => $store->id,
        ]);

        $response->assertJson([
            "name" => "Test Category",
            "description" => "Test Category Description",
            "store_id" => $store->id,
        ]);
    }

    public function test_create_category_by_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/category", [
            "name" => "Test Category",
            "description" => "Test Description",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("categories", [
            "store_id" => $store->id,
            "name" => "Test Category",
            "description" => "Test Description",
        ]);

        $response->assertJson([
            "store_id" => $store->id,
            "name" => "Test Category",
            "description" => "Test Description",
        ]);

        $response->assertJsonStructure(["id", "store_id", "name", "description", "created_at", "updated_at"]);
    }

    public function test_create_category_duplicate_name()
    {
        $store = Store::first();

        $category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->post("/api/category", [
            "name" => $category->name,
            "description" => "Test Description",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_category_with_no_description()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/category", [
            "name" => "Test Category",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("categories", [
            "store_id" => $store->id,
            "name" => "Test Category",
            "description" => null,
        ]);

        $response->assertJson([
            "store_id" => $store->id,
            "name" => "Test Category",
            "description" => null,
        ]);
    }
}
