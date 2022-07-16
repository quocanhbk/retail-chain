<?php

namespace Tests\Feature\Item;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class DeleteItemTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testDeleteItemUnauthenticated()
    {
        $response = $this->delete("/api/item/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteItemWithInvalidPermission()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "delete-item");

        $response = $this->actingAs($employee)->delete("/api/item/{$item->id}");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);

        $this->assertNotSoftDeleted("items", ["id" => $item->id]);
    }

    public function testDeleteItemWithValidPermission()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        $employee = $this->getEmployeeWithPermission($store->id, "delete-item");

        $response = $this->actingAs($employee)->delete("/api/item/{$item->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertSoftDeleted("items", ["id" => $item->id]);
    }

    public function testDeleteItemAsAdmin()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        $response = $this->actingAs($store, "stores")->delete("/api/item/{$item->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertSoftDeleted("items", ["id" => $item->id]);
    }

    public function testForceDeleteItemAsEmployee()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        $employee = $this->getEmployeeWithPermission($store->id, "delete-item");

        $response = $this->actingAs($employee)->delete("/api/item/{$item->id}?force=true");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("items", ["id" => $item->id]);
    }

    public function testForceDeleteItemAsAdmin()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        $response = $this->actingAs($store, "stores")->delete("/api/item/{$item->id}?force=true");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("items", ["id" => $item->id]);
    }

    public function testDeleteItemNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->delete("/api/item/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
