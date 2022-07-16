<?php

namespace Tests\Feature\Category;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class RestoreCategoryTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testRestoreCategoryUnauthenticated()
    {
        $response = $this->post("/api/category/1/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testRestoreCategoryAsEmployee()
    {
        $store = Store::find(1);

        $employee = $store->employees->first();

        $response = $this->actingAs($employee)->post("/api/category/1/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testRestoreCategoryAsAdmin()
    {
        $store = Store::find(1);

        $category = $store->categories->first();

        $store->categories()->delete();

        $response = $this->actingAs($store, "stores")->post("/api/category/{$category->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("categories", [
            "id" => $category->id,
            "name" => $category->name,
            "description" => $category->description,
            "deleted_at" => null,
        ]);
    }

    public function testRestoreCategoryNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/category/9999/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
