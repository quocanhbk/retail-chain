<?php

namespace Tests\Feature\Category;

use App\Models\Employee;
use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetCategoriesTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetCategoriesUnauthenticated()
    {
        $response = $this->get("/api/category");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetCategoriesByAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/category");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);
    }

    public function testGetCategoriesByEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/category");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);
    }

    public function testGetCategoriesWithSearch()
    {
        $store = Store::find(1);

        $category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->get("/api/category?search={$category->name}");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);

        $response->assertJsonFragment(["name" => $category->name]);
    }

    public function testGetCategoriesWithPagination()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/category?from=0&to=3");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "store_id", "name", "description", "created_at", "updated_at"]]);

        $response->assertJsonCount(3);
    }
}
