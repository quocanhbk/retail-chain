<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UpdateCategoryTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testUpdateCategoryUnauthenticated()
    {
        $response = $this->put("/api/category/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateCategoryWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "update-category");

        $response = $this->actingAs($employee)->put("/api/category/1");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateCategoryWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-category");

        $category = $store->categories->first();

        $response = $this->actingAs($employee)->put("/api/category/{$category->id}", [
            "name" => "New Category",
            "description" => "New Description",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("categories", [
            "id" => $category->id,
            "name" => "New Category",
            "description" => "New Description",
        ]);
    }

    public function testUpdateCategoryAsAdmin()
    {
        $store = Store::find(1);

        $category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->put("/api/category/{$category->id}", [
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("categories", [
            "store_id" => $store->id,
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateCategoryNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->put("/api/category/0", [
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateCategoryOfOtherStore()
    {
        $store = Store::find(1);

        $category = Category::where("store_id", "!=", $store->id)->first();

        $response = $this->actingAs($store, "stores")->put("/api/category/{$category->id}", [
            "name" => "Test Category Updated",
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateCategoryWithTooLongName()
    {
        $store = Store::find(1);

        $category = $store->categories->first();

        $response = $this->actingAs($store, "stores")->put("/api/category/{$category->id}", [
            "name" => str_repeat("a", 256),
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("categories", [
            "store_id" => $store->id,
            "name" => str_repeat("a", 256),
            "description" => "Test Description Updated",
        ]);
    }

    public function testUpdateCategoryWithDuplicateName()
    {
        $store = Store::find(1);

        $category = $store->categories->first();

        $other_category = $store->categories->where("id", "!=", $category->id)->first();

        $response = $this->actingAs($store, "stores")->put("/api/category/{$category->id}", [
            "name" => $other_category->name,
            "description" => "Test Description Updated",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
