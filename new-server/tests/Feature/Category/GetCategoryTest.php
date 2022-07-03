<?php

namespace Tests\Feature\Category;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetCategoryTest extends TestCase
{
    use QueryEmployeeTrait;


    public function test_get_category_unauthenticated()
    {
        $response = $this->get("/api/category/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_category_as_employee()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-category");

        $category = $store->categories->first();

        $response = $this->actingAs($employee)->get("/api/category/{$category->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "description", "created_at", "updated_at", "items"]);
    }

    public function test_get_category_as_admin()
    {
        $store = Store::first();

        $category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->get("/api/category/{$category->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "description", "created_at", "updated_at", "items"]);
    }

    public function test_get_category_not_found()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-category");

        $response = $this->actingAs($employee)->get("/api/category/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
