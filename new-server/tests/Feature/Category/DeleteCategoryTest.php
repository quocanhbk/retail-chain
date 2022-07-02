<?php

namespace Tests\Feature\Category;

use App\Models\ItemCategory;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class DeleteCategoryTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;


    public function test_delete_item_category_unauthenticated()
    {
        $response = $this->delete("/api/item-category/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_item_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "delete-category");

        $response = $this->actingAs($employee)->delete("/api/item-category/1");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_item_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "delete-category");

        $category = $store->categories->first();

        $response = $this->actingAs($employee)->delete("/api/item-category/{$category->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertSoftDeleted("item_categories", [
            "id" => $category->id,
        ]);
    }

    public function test_delete_item_as_admin()
    {
        $store = Store::first();

        $category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->delete("/api/item-category/{$category->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertSoftDeleted("item_categories", [
            "id" => $category->id,
        ]);
    }

    public function test_delete_item_category_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->delete("/api/item-category/0");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_force_delete_item_category_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "delete-category");

        $category = $store->categories->first();

        $response = $this->actingAs($employee)->delete("/api/item-category/{$category->id}?force=true");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_force_delete_item_category_as_admin()
    {
        $store = Store::first();

        $category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->delete("/api/item-category/{$category->id}?force=true");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("item_categories", [
            "id" => $category->id,
        ]);
    }
}
