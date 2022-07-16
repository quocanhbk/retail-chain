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

    public function testGetDeletedCategoriesUnauthenticated()
    {
        $response = $this->get("/api/category/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetDeletedCategoriesWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-category");

        $response = $this->actingAs($employee)->get("/api/category/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetDeletedCategoriesAsAdmin()
    {
        $store = Store::find(1);

        $store->categories()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/category/deleted");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "description", "created_at", "updated_at"]]);
    }

    public function testGetDeletedCategoriesWithSearch()
    {
        $store = Store::find(1);

        $category = $store->categories->first();

        $store->categories()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/category/deleted?search={$category->name}");

        $response->assertStatus(200);

        $response->assertJsonFragment(["name" => $category->name]);
    }

    public function testGetDeltedCategoriesWithPagination()
    {
        $store = Store::find(1);

        $store->categories()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/category/deleted?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }
}
