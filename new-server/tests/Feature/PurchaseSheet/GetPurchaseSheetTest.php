<?php

namespace Tests\Feature\PurchaseSheet;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetPurchaseSheetTest extends TestCase
{
    public function testGetPurchaseSheetUnauthenticated()
    {
        $response = $this->get("/api/purchase-sheet");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetPurchaseSheetAsEmployee()
    {
        $employee = Store::find(1)->employees->first();

        $purchase_sheet = $employee->employment->branch->purchase_sheets->first();

        $response = $this->actingAs($employee)->get("/api/purchase-sheet/{$purchase_sheet->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "code",
            "discount",
            "discount_type",
            "paid_amount",
            "note",
            "supplier",
            "employee",
            "items" => [["price", "quantity", "discount", "discount_type", "item" => ["name"]]],
            "branch",
        ]);
    }

    public function testGetPurchaseSheetAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/purchase-sheet");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetPurchaseSheetNotFound()
    {
        $employee = Store::find(1)->employees->first();

        $response = $this->actingAs($employee)->get("/api/purchase-sheet/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
