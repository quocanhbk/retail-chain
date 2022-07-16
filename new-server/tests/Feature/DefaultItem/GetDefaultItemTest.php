<?php

namespace Tests\Feature\DefaultItem;

use App\Models\DefaultItem;
use Tests\TestCase;

class GetDefaultItemTest extends TestCase
{
    public function testGetItemByIdSuccessfully()
    {
        $default_item = DefaultItem::first();

        $response = $this->get("/api/default-item/one?id={$default_item->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "category_id",
            "product_name",
            "bar_code",
            "qr_code",
            "image_url",
            "brand",
            "made_in",
            "unit",
            "mfg_date",
            "exp_date",
            "description",
            "source_url",
            "date",
            "is_duplicate",
            "category" => ["id", "name"],
        ]);

        $response->assertJson([
            "bar_code" => $default_item->bar_code,
            "product_name" => $default_item->product_name,
        ]);
    }

    public function testGetItemByIdNotFound()
    {
        $response = $this->get("/api/default-item/one?id=9999999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetItemByBarcodeSuccessfully()
    {
        $default_item = DefaultItem::first();

        $response = $this->get("/api/default-item/one?barcode={$default_item->bar_code}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "category_id",
            "product_name",
            "bar_code",
            "qr_code",
            "image_url",
            "brand",
            "made_in",
            "unit",
            "mfg_date",
            "exp_date",
            "description",
            "source_url",
            "date",
            "is_duplicate",
            "category" => ["id", "name"],
        ]);

        $response->assertJson([
            "bar_code" => $default_item->bar_code,
            "product_name" => $default_item->product_name,
        ]);
    }

    public function testGetItemByBarcodeNotFound()
    {
        $response = $this->get("/api/default-item/one?barcode=112233445566");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetItemWithNoIdAndBarcode()
    {
        $response = $this->get("/api/default-item/one");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
