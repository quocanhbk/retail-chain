<?php

namespace Tests\Feature\Item;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UpdateItemTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testUpdateItemUnauthenticated()
    {
        $response = $this->put("/api/item/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateItemWithInvalidPermisison()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "update-item");

        $item = $store->items->first();

        $response = $this->actingAs($employee)->put("/api/item/{$item->id}");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateItemWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-item");

        $item = $store->items->first();

        $response = $this->actingAs($employee)->put("/api/item/{$item->id}", [
            "name" => "New Item Name",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "code", "barcode", "store_id"]);

        $response->assertJson([
            "name" => "New Item Name",
            "store_id" => $store->id,
        ]);

        $this->assertDatabaseHas("items", [
            "name" => "New Item Name",
            "store_id" => $store->id,
        ]);
    }

    public function testUpdateItemAsAdmin()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-item");

        $item = $store->items->first();

        $response = $this->actingAs($employee)->put("/api/item/{$item->id}", [
            "name" => "New Item Name",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "code", "barcode", "store_id"]);

        $response->assertJson([
            "name" => "New Item Name",
            "store_id" => $store->id,
        ]);

        $this->assertDatabaseHas("items", [
            "name" => "New Item Name",
            "store_id" => $store->id,
        ]);
    }

    public function testUpdateItemWithImage()
    {
        $store = Store::find(1);

        $item = $store->items->first();

        Storage::fake("local");

        $image = UploadedFile::fake()->image("test_image_updated.jpg");

        $response = $this->actingAs($store, "stores")->put("/api/item/{$item->id}", [
            "image" => $image,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "code", "barcode", "store_id", "image", "image_key"]);

        Storage::disk("local")->assertExists($response->json("image"));

        Storage::disk("local")->assertMissing($item->image);

        $this->assertNotNull($response->json("image_key"));

        $this->assertTrue(Str::startsWith($response->json("image"), "images/{$store->id}/items/"));
    }

    public function testUpdateItemNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-item");

        $response = $this->actingAs($employee)->put("/api/item/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
