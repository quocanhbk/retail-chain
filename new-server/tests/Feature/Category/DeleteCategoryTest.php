<?php

namespace Tests\Feature\Category;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class DeleteCategoryTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testDeleteCategoryUnauthenticated()
    {
        $response = $this->delete("/api/category/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteCategoryWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "delete-category");

        $response = $this->actingAs($employee)->delete("/api/category/1");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteCategoryWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "delete-category");

        $category = $store->categories->first();

        $response = $this->actingAs($employee)->delete("/api/category/{$category->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertSoftDeleted("categories", [
            "id" => $category->id,
        ]);
    }

    public function testDeleteCategoryAsAdmin()
    {
        $store = Store::find(1);

        $category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->delete("/api/category/{$category->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertSoftDeleted("categories", [
            "id" => $category->id,
        ]);
    }

    public function testDeleteCategoryNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->delete("/api/category/0");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testForceDeleteCategoryWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "delete-category");

        $category = $store->categories->first();

        $response = $this->actingAs($employee)->delete("/api/category/{$category->id}?force=true");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testForceDeleteCategoryAsAdmin()
    {
        $store = Store::find(1);

        $category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->delete("/api/category/{$category->id}?force=true");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("categories", [
            "id" => $category->id,
        ]);
    }
}
