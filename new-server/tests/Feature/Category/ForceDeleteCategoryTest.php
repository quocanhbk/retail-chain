<?php

namespace Tests\Feature\Category;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class ForceDeleteCategoryTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;


    public function test_force_delete_category_unauthenticated()
    {
        $response = $this->delete("/api/item-category/1/force");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_force_delete_category_as_employee()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-category");

        $response = $this->actingAs($employee)->delete("/api/item-category/1/force");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_force_delete_category_as_admin()
    {
        $store = Store::first();

        $category = $store->categories->first();

        $store->categories()->delete();

        $response = $this->actingAs($store, "stores")->delete("/api/item-category/{$category->id}/force");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("item_categories", [
            "id" => $category->id,
        ]);
    }

    public function test_force_delete_category_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->delete("/api/item-category/9999/force");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
