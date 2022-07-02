<?php

namespace Tests\Feature\Category;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetDeletedCategoriesTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_get_deleted_categories_unauthenticated()
    {
        $response = $this->get("/api/item-category/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_deleted_categories_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-category");

        $response = $this->actingAs($employee)->get("/api/item-category/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_deleted_categories_as_admin()
    {
        $store = Store::first();

        $store->categories()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/item-category/deleted");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "description", "created_at", "updated_at"]]);
    }

    public function test_get_deleted_categories_with_search()
    {
        $store = Store::first();

        $category = $store->categories->first();

        $store->categories()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/item-category/deleted?search={$category->name}");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            "id" => $category->id,
            "name" => $category->name,
            "description" => $category->description,
        ]);

        $response->assertJsonCount(1);
    }

    public function test_get_delted_categories_with_pagination()
    {
        $store = Store::first();

        $store->categories()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/item-category/deleted?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }
}
