<?php

namespace Tests\Feature\Item;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetDeletedItemsTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testGetDeletedItemsUnauthenticated()
    {
        $response = $this->get("/api/item/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetDeletedItemsAsEmployee()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "delete-item");

        $response = $this->actingAs($employee)->get("/api/item/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetDeletedItemsAsAdmin()
    {
        $store = Store::find(1);

        $store->items()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/item/deleted");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "store_id", "barcode", "name", "image", "image_key", "category", "code"],
        ]);

        $response->assertJsonFragment(["store_id" => $store->id]);

        $response->assertJsonCount(
            min(
                $store
                    ->items()
                    ->onlyTrashed()
                    ->count(),
                10
            )
        );
    }

    public function testGetDeletedItemsWithSearch()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        $store->items()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/item/deleted?search={$item->barcode}");

        $response->assertStatus(200);

        $response->assertJsonFragment(["store_id" => $store->id, "barcode" => $item->barcode]);

        $response->assertJsonCount(1);
    }

    public function testGetDeletedItemsWithPagination()
    {
        $store = Store::find(1);

        $store->items()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/item/deleted?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }
}
