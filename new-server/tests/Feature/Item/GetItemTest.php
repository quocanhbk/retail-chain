<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetItemTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    protected $seed = true;

    public function test_get_item_unauthenticated()
    {
        $response = $this->get('/api/item/one?id=1');

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_item_with_no_id_or_barcode()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item/one");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_item_by_id_successfully()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $item = $store->items->first();

        $response = $this->actingAs($employee)->get("/api/item/one?id={$item->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "name",
            "code",
            "barcode",
            "image",
            "image_key",
            "store_id",
            "category"
        ]);

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

    public function test_get_item_by_barcode_successfully()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $item = $store->items->first();

        $response = $this->actingAs($employee)->get("/api/item/one?barcode={$item->barcode}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "name",
            "code",
            "barcode",
            "image",
            "image_key",
            "store_id",
            "category"
        ]);

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

    public function test_get_item_as_admin_successfully()
    {
        $store = Store::first();

        $item = $store->items->first();

        $response = $this->actingAs($store, "stores")->get("/api/item/one?id={$item->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "name",
            "code",
            "barcode",
            "image",
            "image_key",
            "store_id",
            "category"
        ]);

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

    public function test_get_item_not_found()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item/one?id=9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
