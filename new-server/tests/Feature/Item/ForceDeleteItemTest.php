<?php

namespace Tests\Feature\Item;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class ForceDeleteItemTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testForceDeleteItemUnauthenticated()
    {
        $response = $this->delete("/api/item/1/force");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testForceDeleteItemAsEmployee()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        $employee = $this->getEmployeeWithPermission($store->id, "delete-item");

        $response = $this->actingAs($employee)->delete("/api/item/{$item->id}/force");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("items", ["id" => $item->id]);
    }

    public function testForceDeleteUndeletedItemAsAdmin()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        $response = $this->actingAs($store, "stores")->delete("/api/item/{$item->id}/force");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("items", ["id" => $item->id]);
    }

    public function testForceDeleteDeletedItemAsAdmin()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        $store->items()->delete();

        $response = $this->actingAs($store, "stores")->delete("/api/item/{$item->id}/force");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("items", ["id" => $item->id]);
    }

    public function testForceDeleteItemNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->delete("/api/item/9999/force");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
