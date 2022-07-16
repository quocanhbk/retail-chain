<?php

namespace Tests\Feature\PurchaseSheet;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetPurchaseSheetsTest extends TestCase
{
    use RefreshDatabase;

    public function testGetPurchaseSheetUnauthenticated()
    {
        $response = $this->get("/api/purchase-sheet");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetPurchaseSheetAsEmployee()
    {
        $employee = Store::find(1)->employees->first();

        $response = $this->actingAs($employee)->get("/api/purchase-sheet");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "code", "discount", "discount_type", "paid_amount", "note", "supplier", "employee"],
        ]);
    }

    public function testGetPurchaseSheetAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/purchase-sheet");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetPurchaseSheetWithSearch()
    {
        $employee = Store::find(1)->employees->first();

        $purchase_sheet = $employee->employment->branch->purchase_sheets->first();

        $response = $this->actingAs($employee)->get("/api/purchase-sheet?search={$purchase_sheet->code}");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            "id" => $purchase_sheet->id,
            "code" => $purchase_sheet->code,
        ]);

        $response->assertJsonCount(1);
    }

    public function testGetPurchaseSheetWithPagination()
    {
        $employee = Store::find(1)->employees->first();

        $response = $this->actingAs($employee)->get("/api/purchase-sheet?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }
}
