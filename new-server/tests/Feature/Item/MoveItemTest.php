<?php

namespace Tests\Feature\Item;

use App\Models\DefaultItem;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class MoveItemTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testMoveItemUnauthenticated()
    {
        $response = $this->post("/api/item/move");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testMoveItemWithInvalidPermisison()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-item");

        $response = $this->actingAs($employee)->post("/api/item/move");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testMoveItemWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-item");

        $response = $this->actingAs($employee)->post("/api/item/move", [
            "barcode" => DefaultItem::first()->bar_code,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "code", "barcode", "store_id"]);

        $response->assertJson([
            "barcode" => DefaultItem::first()->bar_code,
            "store_id" => $store->id,
        ]);

        $this->assertDatabaseHas("items", [
            "barcode" => DefaultItem::first()->bar_code,
            "store_id" => $store->id,
        ]);
    }

    public function testMoveItemAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/item/move", [
            "barcode" => DefaultItem::first()->bar_code,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "code", "barcode", "store_id"]);

        $response->assertJson([
            "barcode" => DefaultItem::first()->bar_code,
            "store_id" => $store->id,
        ]);

        $this->assertDatabaseHas("items", [
            "barcode" => DefaultItem::first()->bar_code,
            "store_id" => $store->id,
        ]);
    }

    public function testMoveItemNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-item");

        $response = $this->actingAs($employee)->post("/api/item/move", [
            "barcode" => "not-found",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testMoveItemWithNoBarcode()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-item");

        $response = $this->actingAs($employee)->post("/api/item/move");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
