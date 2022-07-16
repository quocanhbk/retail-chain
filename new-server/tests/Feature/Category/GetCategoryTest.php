<?php

namespace Tests\Feature\Category;

use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetCategoryTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetCategoryUnauthenticated()
    {
        $response = $this->get("/api/category/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetCategoryAsEmployee()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-category");

        $category = $store->categories->first();

        $response = $this->actingAs($employee)->get("/api/category/{$category->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "description", "created_at", "updated_at", "items"]);
    }

    public function testGetCategoryAsAdmin()
    {
        $store = Store::find(1);

        $category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->get("/api/category/{$category->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "description", "created_at", "updated_at", "items"]);
    }

    public function testGetCategoryNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-category");

        $response = $this->actingAs($employee)->get("/api/category/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
