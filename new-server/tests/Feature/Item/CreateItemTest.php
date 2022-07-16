<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreateItemTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testCreateItemUnauthenticated()
    {
        $response = $this->post("/api/item", [
            "name" => "Test Item",
            "barcode" => "123456789",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateItemWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-item");

        $response = $this->actingAs($employee)->post("/api/item", [
            "name" => "Test Item",
            "barcode" => "123456789",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateItemWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-item");

        $response = $this->actingAs($employee)->post("/api/item", [
            "name" => "Test Item",
            "barcode" => "123456789",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "code", "barcode"]);

        $response->assertJson([
            "name" => "Test Item",
            "barcode" => "123456789",
        ]);

        $this->assertDatabaseHas("items", [
            "store_id" => $store->id,
            "name" => "Test Item",
            "barcode" => "123456789",
        ]);
    }

    public function testCreateItemAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/item", [
            "name" => "Test Item",
            "barcode" => "123456789",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "code", "barcode"]);

        $response->assertJson([
            "name" => "Test Item",
            "barcode" => "123456789",
        ]);

        $this->assertDatabaseHas("items", [
            "store_id" => $store->id,
            "name" => "Test Item",
            "barcode" => "123456789",
            "image" => null,
            "image_key" => null,
        ]);
    }

    public function testCreateItemWithImage()
    {
        $store = Store::find(1);

        Storage::fake("local");

        $image = UploadedFile::fake()->image("test_image.jpg");

        $response = $this->actingAs($store, "stores")->post("/api/item", [
            "name" => "Test Item",
            "barcode" => "123456789",
            "image" => $image,
        ]);

        $response->assertStatus(200);

        Storage::disk("local")->assertExists($response->json("image"));

        $this->assertDatabaseHas("items", [
            "store_id" => $store->id,
            "name" => "Test Item",
            "barcode" => "123456789",
            "image" => $response->json("image"),
            "image_key" => $response->json("image_key"),
        ]);

        $this->assertNotNull($response->json("image_key"));

        $this->assertTrue(Str::startsWith($response->json("image"), "images/{$store->id}/items/"));
    }

    public function testCreateItemWithDuplicateCode()
    {
        $store = Store::find(1);

        $item = Item::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->post("/api/item", [
            "name" => "Test Item",
            "barcode" => "123456789",
            "code" => $item->code,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateItemWithDuplicateBarcode()
    {
        $store = Store::find(1);

        $item = Item::where("store_id", $store->id)->first();

        $response = $this->actingAs($store, "stores")->post("/api/item", [
            "name" => "Test Item",
            "barcode" => $item->barcode,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateItemWithUnexistCategory()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/item", [
            "name" => "Test Item",
            "barcode" => "123456789",
            "category_id" => 99999,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateItemWithNoName()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/item", [
            "barcode" => "123456789",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
