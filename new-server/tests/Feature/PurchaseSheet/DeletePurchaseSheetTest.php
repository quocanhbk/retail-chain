<?php

namespace Tests\Feature\PurchaseSheet;

use App\Models\ItemProperty;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class DeletePurchaseSheetTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testDeletePurchaseSheetUnauthenticated()
    {
        $response = $this->delete("/api/purchase-sheet/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeletePurchaseSheetWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "delete-purchase-sheet");

        $purchase_sheet_id = $employee->employment->branch->purchase_sheets->first()->id;

        $response = $this->actingAs($employee)->delete("/api/purchase-sheet/{$purchase_sheet_id}");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeletePurchaseSheetWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "delete-purchase-sheet");

        $creator = $this->getEmployeeWithPermission($store->id, "create-purchase-sheet");

        $item_property = ItemProperty::where("branch_id", $employee->employment->branch_id)->first();

        $create_purchase_sheet_response = $this->actingAs($creator)->post("/api/purchase-sheet", [
            "items" => [
                [
                    "id" => $item_property->item_id,
                    "quantity" => 1,
                    "price" => 100_000,
                ],
            ],
        ]);

        $purchase_sheet_id = $create_purchase_sheet_response->json("id");

        $response = $this->actingAs($employee)->delete("/api/purchase-sheet/{$purchase_sheet_id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("purchase_sheets", [
            "id" => $purchase_sheet_id,
        ]);

        $this->assertDatabaseMissing("purchase_sheet_items", [
            "purchase_sheet_id" => $purchase_sheet_id,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "id" => $item_property->id,
            "item_id" => $item_property->item_id,
            "quantity" => $item_property->quantity,
        ]);
    }

    public function testDeletePurchaseSheetNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "delete-purchase-sheet");

        $response = $this->actingAs($employee)->delete("/api/purchase-sheet/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
