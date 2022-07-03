<?php

namespace Tests\Feature\Item;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetItemsTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    protected $seed = true;

    public function test_get_items_unauthenticated()
    {
        $response = $this->get('/api/item');

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_items_as_employee()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item");

        $response->assertStatus(200);

        $response->assertJsonStructure([
                [
                    "id",
                    "name",
                    "code",
                    "barcode",
                    "image",
                    "image_key",
                    "store_id",
                    "item_category"
                ],
        ]);
    }

    public function test_get_items_as_admin()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item");

        $response->assertStatus(200);

        $response->assertJsonStructure([
                [
                    "id",
                    "name",
                    "code",
                    "barcode",
                    "image",
                    "image_key",
                    "store_id",
                    "item_category"
                ],
        ]);
    }

    public function test_get_items_with_pagination()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $response = $this->actingAs($employee)->get("/api/item?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonStructure([
                [
                    "id",
                    "name",
                    "code",
                    "barcode",
                    "image",
                    "image_key",
                    "store_id",
                    "item_category"
                ],
        ]);

        $response->assertJsonCount(1);
    }

    public function test_get_items_with_search()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-item");

        $item = $store->items->first();

        $response = $this->actingAs($employee)->get("/api/item?search={$item->name}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
                [
                    "id",
                    "name",
                    "code",
                    "barcode",
                    "image",
                    "image_key",
                    "store_id",
                    "item_category"
                ],
        ]);

        $response->assertJsonCount(1);
    }
}
