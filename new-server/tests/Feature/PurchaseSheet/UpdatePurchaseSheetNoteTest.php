<?php

namespace Tests\Feature\PurchaseSheet;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UpdatePurchaseSheetNoteTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testUpdateNoteUnauthenticated()
    {
        $response = $this->put("/api/purchase-sheet/1/note");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateNoteWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "update-purchase-sheet");

        $purchase_sheet_id = $employee->employment->branch->purchase_sheets->first()->id;

        $response = $this->actingAs($employee)->put("/api/purchase-sheet/{$purchase_sheet_id}/note");

        $response->assertStatus(403);
    }

    public function testUpdateNoteWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-purchase-sheet");

        $purchase_sheet_id = $employee->employment->branch->purchase_sheets->first()->id;

        $response = $this->actingAs($employee)->put("/api/purchase-sheet/{$purchase_sheet_id}/note", [
            "note" => "This is a note",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("purchase_sheets", [
            "id" => $purchase_sheet_id,
            "note" => "This is a note",
        ]);
    }

    public function testUpdateNoteWithInvalidInput()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-purchase-sheet");

        $purchase_sheet_id = $employee->employment->branch->purchase_sheets->first()->id;

        $response = $this->actingAs($employee)->put("/api/purchase-sheet/{$purchase_sheet_id}/note");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateNoteNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-purchase-sheet");

        $response = $this->actingAs($employee)->put("/api/purchase-sheet/9999/note", [
            "note" => "This is a note",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
