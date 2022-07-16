<?php

namespace Tests\Feature\Item;

use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetSellingItemsTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetSellingItemsUnauthenticated()
    {
        $response = $this->get("/api/item/selling");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetSellingItemsAsEmployee()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item/selling");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "code", "barcode", "image", "image_key", "store_id", "category", "properties"],
        ]);
    }

    public function testGetSellingItemsAsAdminWithNoBranchId()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/item/selling");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetSellingItemsAsAdminWithBranchId()
    {
        $store = Store::find(1);

        $branch = $store->branches->first();

        $response = $this->actingAs($store, "stores")->get("/api/item/selling?branch_id={$branch->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "code", "barcode", "image", "image_key", "store_id", "category", "properties"],
        ]);
    }

    public function testGetSellingItemsWithPagination()
    {
        $store = Store::find(1);

        $branch = $store->branches->first();

        $response = $this->actingAs($store, "stores")->get("/api/item/selling?branch_id={$branch->id}&from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "code", "barcode", "image", "image_key", "store_id", "category", "properties"],
        ]);

        $response->assertJsonCount(1);
    }
}
