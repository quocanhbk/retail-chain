<?php

namespace Tests\Feature\Category;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class ForceDeleteCategoryTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testForceDeleteCategoryUnauthenticated()
    {
        $response = $this->delete("/api/category/1/force");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testForceDeleteCategoryAsEmployee()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-category");

        $response = $this->actingAs($employee)->delete("/api/category/1/force");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testForceDeleteCategoryAsAdmin()
    {
        $store = Store::find(1);

        $category = $store->categories->first();

        $store->categories()->delete();

        $response = $this->actingAs($store, "stores")->delete("/api/category/{$category->id}/force");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("categories", [
            "id" => $category->id,
        ]);
    }

    public function testForceDeleteCategoryNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->delete("/api/category/9999/force");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
