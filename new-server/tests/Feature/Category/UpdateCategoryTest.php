<?php

namespace Tests\Feature\Category;

use App\Models\Employee;
use App\Models\ItemCategory;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UpdateCategoryTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_update_category_unauthenticated()
    {
        $response = $this->put("/api/item-category/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_category_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "update-category");

        $response = $this->actingAs($employee)->put("/api/item-category/1");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_category_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "update-category");

        $category = $store->categories->first();

        $response = $this->actingAs($employee)->put("/api/item-category/{$category->id}", [
            "name" => "New Category",
            "description" => "New Description",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("item_categories", [
            "id" => $category->id,
            "name" => "New Category",
            "description" => "New Description",
        ]);
    }

    public function test_update_item_category_as_admin()
    {
        $store = Store::first();

        $item_category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->put("/api/item-category/{$item_category->id}", [
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("item_categories", [
            "store_id" => $store->id,
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_item_category_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->put("/api/item-category/0", [
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_item_category_of_other_store()
    {
        $store = Store::first();

        $item_category = ItemCategory::where("store_id", "!=", $store->id)->first();

        $response = $this->actingAs($store, "stores")->put("/api/item-category/{$item_category->id}", [
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_item_category_with_too_long_name()
    {
        $store = Store::first();

        $item_category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->put("/api/item-category/{$item_category->id}", [
            "name" => str_repeat("a", 256),
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("item_categories", [
            "store_id" => $store->id,
            "name" => str_repeat("a", 256),
            "description" => "Test Description Updated",
        ]);
    }

    public function test_update_item_category_with_duplicate_name()
    {
        $store = Store::first();

        $item_category = $store->categories->first();

        $other_category = $store->categories->where("id", "!=", $item_category->id)->first();

        $response = $this->actingAs($store, "stores")->put("/api/item-category/{$item_category->id}", [
            "name" => $other_category->name,
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
