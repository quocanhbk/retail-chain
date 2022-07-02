<?php

namespace Tests\Feature\Category;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetCategoriesTest extends TestCase
{
    use QueryEmployeeTrait;

    public function test_get_item_categories_unauthenticated()
    {
        $response = $this->get("/api/item-category");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_item_categories_by_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/item-category");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);
    }

    public function test_get_item_categories_by_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/item-category");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);
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
}
