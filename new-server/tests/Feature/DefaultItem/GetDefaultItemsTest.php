<?php

namespace Tests\Feature\DefaultItem;

use App\Models\DefaultCategory;
use App\Models\DefaultItem;
use Tests\TestCase;

class GetDefaultItemsTest extends TestCase
{
    public function testGetDefaultItemsSuccessfully()
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

    public function testGetDefaultItemsWithSearch()
    {
        $default_item = DefaultItem::first();

        $response = $this->get("/api/default-item?search=" . $default_item->product_name);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            "product_name" => $default_item->product_name,
        ]);
    }

    public function testGetDefaultItemsWithPagination()
    {
        $response = $this->get("/api/default-item?from=0&to=10");

        $response->assertStatus(200);

        $response->assertJsonCount(10);
    }

    public function testGetDefaultItemsWithGibberishSearch()
    {
        $response = $this->get("/api/default-item?search=gibberish");

        $response->assertStatus(200);

        $response->assertJsonCount(0);
    }

    public function testGetDefaultItemsWithCategoryId()
    {
        $default_category = DefaultCategory::first();

        $response = $this->get("/api/default-item?category_id=" . $default_category->id);

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

        $response->assertJsonFragment([
            "category_id" => $default_category->id,
        ]);
    }
}
