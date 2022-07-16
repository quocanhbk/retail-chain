<?php

namespace Tests\Feature\Item;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class RestoreItemTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testRestoreItemUnauthenticated()
    {
        $response = $this->post("/api/item/1/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testRestoreItemAsEmployee()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "delete-item");

        $response = $this->actingAs($employee)->post("/api/item/1/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testRestoreItemAsAdmin()
    {
        $store = Store::find(1);

        $store->items()->delete();

        $item = $store
            ->items()
            ->onlyTrashed()
            ->first();

        $response = $this->actingAs($store, "stores")->post("/api/item/{$item->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("items", ["id" => $item->id, "deleted_at" => null]);
    }

    public function testRestoreItemNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/item/1/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
