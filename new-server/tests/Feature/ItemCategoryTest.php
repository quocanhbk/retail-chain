<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\ItemCategory;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_create_item_category_by_admin_successfully()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/item-category", [
            "name" => "Test Category",
            "description" => "Test Description",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("item_categories", [
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

    public function test_create_item_category_by_employee_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/item-category", [
            "name" => "Test Category",
            "description" => "Test Description",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("item_categories", [
            "store_id" => $employee->store_id,
            "name" => "Test Category",
            "description" => "Test Description",
        ]);

        $response->assertJson([
            "store_id" => $employee->store_id,
            "name" => "Test Category",
            "description" => "Test Description",
        ]);

        $response->assertJsonStructure(["id", "store_id", "name", "description", "created_at", "updated_at"]);
    }

    public function test_create_item_category_unauthorized()
    {
        $response = $this->post("/api/item-category", [
            "name" => "Test Category",
            "description" => "Test Description",
        ]);

        $response->assertStatus(401);
    }

    public function test_create_item_category_duplicate_name()
    {
        $store = Store::first();

        $item_category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->post("/api/item-category", [
            "name" => $item_category->name,
            "description" => "Test Description",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_item_category_with_no_description()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/item-category", [
            "name" => "Test Category",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("item_categories", [
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

    public function test_create_many_item_categories_by_admin_successfully()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/item-category/many", [
            "item_categories" => [
                [
                    "name" => "Test Category",
                    "description" => "Test Description",
                ],
                [
                    "name" => "Test Category 2",
                    "description" => "Test Description 2",
                ],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("item_categories", [
            "store_id" => $store->id,
            "name" => "Test Category",
            "description" => "Test Description",
        ]);

        $this->assertDatabaseHas("item_categories", [
            "store_id" => $store->id,
            "name" => "Test Category 2",
            "description" => "Test Description 2",
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_many_item_categories_unauthorized()
    {
        $response = $this->post("/api/item-category/many", [
            "item_categories" => [
                [
                    "name" => "Test Category",
                    "description" => "Test Description",
                ],
                [
                    "name" => "Test Category 2",
                    "description" => "Test Description 2",
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_create_many_item_categories_duplicate_with_database()
    {
        $store = Store::first();

        $item_category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->post("/api/item-category/many", [
            "item_categories" => [
                [
                    "name" => $item_category->name,
                    "description" => "Test Description",
                ],
            ],
        ]);

        $response->assertStatus(400);
    }

    public function test_create_many_item_categories_duplicate_input()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/item-category/many", [
            "item_categories" => [
                [
                    "name" => "Test Category",
                    "description" => "Test Description",
                ],
                [
                    "name" => "Test Category",
                    "description" => "Test Description",
                ],
            ],
        ]);

        $response->assertStatus(400);
    }

    public function test_get_item_categories_by_admin_successfully()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/item-category");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);
    }

    public function test_get_item_categories_by_employee_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/item-category");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);
    }

    public function test_get_item_categories_unauthorized()
    {
        $response = $this->get("/api/item-category");

        $response->assertStatus(401);
    }

    public function test_get_item_categories_with_search()
    {
        $store = Store::first();

        $item_category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->get("/api/item-category?search={$item_category->name}");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);

        $response->assertJsonFragment([
            "name" => $item_category->name,
        ]);

        $response->assertJsonCount(1);
    }

    public function test_get_categories_with_pagination()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/item-category?from=0&to=3");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);

        $response->assertJsonCount(3);
    }

    public function test_get_categories_with_order()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/item-category?order_by=id&order_type=desc");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);
    }

    public function test_update_item_category_by_admin_successfully()
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

        $response->assertJsonStructure([
            "message"
        ]);
    }

    public function test_update_item_category_by_employee_successfully()
    {
        $employee = Employee::first();

        $item_category = $employee->store->categories->first();

        $response = $this->actingAs($employee)->put("/api/item-category/{$item_category->id}", [
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("item_categories", [
            "store_id" => $employee->store_id,
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertJsonStructure([
            "message"
        ]);
    }

    public function test_update_item_category_unauthorized()
    {
        $item_category = ItemCategory::first();

        $response = $this->put("/api/item-category/{$item_category->id}", [
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure([
            "message"
        ]);
    }

    public function test_update_item_category_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->put("/api/item-category/0", [
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure([
            "message"
        ]);
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

        $response->assertJsonStructure([
            "message"
        ]);
    }

    public function test_delete_item_category_with_existing_items()
    {
        $store = Store::first();

        $item_category = ItemCategory::withCount("items")->get()->where("items_count", ">", 0)->first();

        $response = $this->actingAs($store, "stores")->delete("/api/item-category/{$item_category->id}");

        $response->assertStatus(400);

        $response->assertJsonStructure([
            "message"
        ]);
    }

    public function test_delete_item_category_with_no_existing_items()
    {
        $store = Store::first();

        $item_category = ItemCategory::withCount("items")->get()->where("items_count", "=", 0)->first();

        $response = $this->actingAs($store, "stores")->delete("/api/item-category/{$item_category->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "message"
        ]);

        $this->assertDatabaseMissing("item_categories", [
            "id" => $item_category->id,
        ]);
    }

    public function test_delete_item_category_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->delete("/api/item-category/0");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            "message"
        ]);
    }

    public function test_delete_item_category_unauthorized()
    {
        $item_category = ItemCategory::first();

        $response = $this->delete("/api/item-category/{$item_category->id}");

        $response->assertStatus(401);

        $response->assertJsonStructure([
            "message"
        ]);
    }
}
