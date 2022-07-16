<?php

namespace Tests\Feature\QuantityCheckingSheet;

use App\Models\ItemProperty;
use App\Models\QuantityCheckingSheet;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreateQuantityCheckingSheetTest extends TestCase
{
    use RefreshDatabase, QueryEmployeeTrait;

    public function testCreateSheetUnauthenticated()
    {
        $response = $this->post("/api/quantity-checking-sheet");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateSheetWithInvalidPermission()
    {
        $employee = $this->getEmployeeWithoutPermission(1, "create-quantity-checking-sheet");

        $response = $this->actingAs($employee)->post("/api/quantity-checking-sheet");

        $response->assertStatus(403);
    }

    public function testCreateSheetWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-quantity-checking-sheet");

        $item_property = ItemProperty::where("branch_id", $employee->employment->branch_id)->first();

        $response = $this->actingAs($employee)->post("/api/quantity-checking-sheet", [
            "items" => [[
                "id" =>  $item_property->item_id,
                "actual_quantity" => $item_property->quantity + 10,
            ]],
            "note" => "test",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "code", "branch_id", "employee_id", "note"]);

        $response->assertJson([
            "employee_id" => $employee->id,
            "branch_id" => $employee->employment->branch_id,
            "note" => "test",
        ]);

        $this->assertStringStartsWith("QS", $response->json("code"));

        $this->assertDatabaseHas("quantity_checking_sheets", [
            "code" => $response->json("code"),
            "employee_id" => $employee->id,
            "branch_id" => $employee->employment->branch_id,
            "note" => "test",
        ]);

        $this->assertDatabaseHas("quantity_checking_items", [
            "quantity_checking_sheet_id" => $response->json("id"),
            "item_id" => $item_property->item_id,
            "expected_quantity" => $item_property->quantity,
            "actual_quantity" => $item_property->quantity + 10,
            "total" => $item_property->base_price * 10,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property->item_id,
            "quantity" => $item_property->quantity + 10,
        ]);
    }

    public function testCreateSheetWithInvalidItems()
    {
        $employee = $this->getEmployeeWithPermission(1, "create-quantity-checking-sheet");

        $response = $this->actingAs($employee)->post("/api/quantity-checking-sheet", [
            "items" => [],
            "note" => "test",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateSheetWithLosingQuantity()
    {
        $employee = $this->getEmployeeWithPermission(1, "create-quantity-checking-sheet");

        $item_property = ItemProperty::where("branch_id", $employee->employment->branch_id)->where("quantity", ">", 10)->first();

        $response = $this->actingAs($employee)->post("/api/quantity-checking-sheet", [
            "items" => [[
                "id" =>  $item_property->item_id,
                "actual_quantity" => $item_property->quantity - 10,
            ]],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("quantity_checking_items", [
            "quantity_checking_sheet_id" => $response->json("id"),
            "item_id" => $item_property->item_id,
            "expected_quantity" => $item_property->quantity,
            "actual_quantity" => max($item_property->quantity - 10, 0),
            "total" => $item_property->base_price * (-10),
        ]);
    }

    public function testCreateSheetWithCustomCode()
    {
        $employee = $this->getEmployeeWithPermission(1, "create-quantity-checking-sheet");

        $response = $this->actingAs($employee)->post("/api/quantity-checking-sheet", [
            "code" => "QSS9999",
            "items" => [[
                "id" =>  1,
                "actual_quantity" => 10,
            ]],
            "note" => "test",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("quantity_checking_sheets", [
            "code" => "QSS9999",
            "employee_id" => $employee->id,
            "branch_id" => $employee->employment->branch_id,
            "note" => "test",
        ]);
    }

    public function testCreateSheetWithDuplicateCode()
    {
        $employee = $this->getEmployeeWithPermission(1, "create-quantity-checking-sheet");

        $quantity_checking_sheet = QuantityCheckingSheet::where("branch_id", $employee->employment->branch_id)->first();

        $response = $this->actingAs($employee)->post("/api/quantity-checking-sheet", [
            "code" => $quantity_checking_sheet->code,
            "items" => [[
                "id" =>  1,
                "actual_quantity" => 10,
            ]],
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
