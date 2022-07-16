<?php

namespace Tests\Feature\Item;

use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetItemTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetItemUnauthenticated()
    {
        $response = $this->get("/api/item/one?id=1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetItemWithNoIdOrBarcode()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item/one");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetItemByIdAsAdmin()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $item = $store->items->first();

        $response = $this->actingAs($employee)->get("/api/item/one?id={$item->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "code", "barcode", "image", "image_key", "store_id", "category"]);

        $response->assertJson([
            "id" => $item->id,
            "name" => $item->name,
            "code" => $item->code,
            "barcode" => $item->barcode,
            "image" => $item->image,
            "image_key" => $item->image_key,
            "store_id" => $store->id,
        ]);
    }

    public function testGetItemByBarcodeAsAdmin()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $item = $store->items->first();

        $response = $this->actingAs($employee)->get("/api/item/one?barcode={$item->barcode}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "code", "barcode", "image", "image_key", "store_id", "category"]);

        $response->assertJson([
            "id" => $item->id,
            "name" => $item->name,
            "code" => $item->code,
            "barcode" => $item->barcode,
            "image" => $item->image,
            "image_key" => $item->image_key,
            "store_id" => $store->id,
        ]);
    }

    public function testGetItemAsAdminSuccessfully()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        $response = $this->actingAs($store, "stores")->get("/api/item/one?id={$item->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "code", "barcode", "image", "image_key", "store_id", "category"]);

        $response->assertJson([
            "id" => $item->id,
            "name" => $item->name,
            "code" => $item->code,
            "barcode" => $item->barcode,
            "image" => $item->image,
            "image_key" => $item->image_key,
            "store_id" => $store->id,
        ]);
    }

    public function testGetItemNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item/one?id=9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
