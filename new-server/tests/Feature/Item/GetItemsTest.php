<?php

namespace Tests\Feature\Item;

use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetItemsTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetItemsUnauthenticated()
    {
        $response = $this->get("/api/item");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetItemsAsEmployee()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "code", "barcode", "image", "image_key", "store_id", "category"],
        ]);
    }

    public function testGetItemsAsAdmin()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "code", "barcode", "image", "image_key", "store_id", "category"],
        ]);
    }

    public function testGetItemsWithPagination()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "code", "barcode", "image", "image_key", "store_id", "category"],
        ]);

        $response->assertJsonCount(1);
    }

    public function testGetItemsWithSearch()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $item = $store->items->first();

        $response = $this->actingAs($employee)->get("/api/item?search={$item->name}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "code", "barcode", "image", "image_key", "store_id", "category"],
        ]);

        $response->assertJsonFragment(["name" => $item->name]);
    }
}
