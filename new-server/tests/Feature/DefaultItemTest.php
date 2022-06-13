<?php

namespace Tests\Feature;

use App\Models\DefaultCategory;
use App\Models\DefaultItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DefaultItemTest extends TestCase
{
    public function test_get_default_items_successfully()
    {
        $response = $this->get("/api/default-item");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            [
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
            ],
        ]);
    }

    public function test_get_default_items_with_search_successfully()
    {
        $default_item = DefaultItem::first();

        $response = $this->get("/api/default-item?search=" . $default_item->product_name);

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }

    public function test_get_default_items_with_pagination_successfully()
    {
        $response = $this->get("/api/default-item?from=0&to=10");

        $response->assertStatus(200);

        $response->assertJsonCount(10);
    }

    public function test_get_default_items_with_gibberish_search_successfully()
    {
        $response = $this->get("/api/default-item?search=gibberish");

        $response->assertStatus(200);

        $response->assertJsonCount(0);
    }

    public function test_get_item_by_barcode_successfully()
    {
        $default_item = DefaultItem::first();

        $response = $this->get("/api/default-item/barcode/" . $default_item->bar_code);

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

    public function test_get_item_by_barcode_not_found()
    {
        $response = $this->get("/api/default-item/barcode/112233445566");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_items_by_category_successfully()
    {
        $category = DefaultCategory::first();

        $response = $this->get("/api/default-item/category/" . $category->id);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            "category_id" => $category->id,
        ]);

        $response->assertJsonStructure([
            [
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
            ],
        ]);
    }

    public function test_get_items_with_gibberish_category()
    {
        $response = $this->get("/api/default-item/category/99");

        $response->assertJsonCount(0);
    }
}
