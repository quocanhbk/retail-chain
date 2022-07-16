<?php

namespace Tests\Feature\QuantityCheckingSheet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UpdateQuantityCheckingSheetNoteTest extends TestCase
{
    use RefreshDatabase, QueryEmployeeTrait;

    public function testUpdateNoteUnauthenticated()
    {
        $response = $this->put("/api/quantity-checking-sheet/1/note");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateNoteWithInvalidPermission()
    {
        $employee = $this->getEmployeeWithoutPermission(1, "update-quantity-checking-sheet");

        $quantity_checking_sheet_id = $employee->employment->branch->quantity_checking_sheets->first()->id;

        $response = $this->actingAs($employee)->put("/api/quantity-checking-sheet/{$quantity_checking_sheet_id}/note", [
            "note" => "This is a note",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("quantity_checking_sheets", [
            "id" => $quantity_checking_sheet_id,
            "note" => "This is a note",
        ]);
    }

    public function testUpdateNoteWithValidPermission()
    {
        $employee = $this->getEmployeeWithPermission(1, "update-quantity-checking-sheet");

        $quantity_checking_sheet_id = $employee->employment->branch->quantity_checking_sheets->first()->id;

        $response = $this->actingAs($employee)->put("/api/quantity-checking-sheet/{$quantity_checking_sheet_id}/note", [
            "note" => "This is a note",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("quantity_checking_sheets", [
            "id" => $quantity_checking_sheet_id,
            "note" => "This is a note",
        ]);
    }

    public function testUpdateNoteWithNoInput()
    {
        $employee = $this->getEmployeeWithPermission(1, "update-quantity-checking-sheet");

        $quantity_checking_sheet_id = $employee->employment->branch->quantity_checking_sheets->first()->id;

        $response = $this->actingAs($employee)->put("/api/quantity-checking-sheet/{$quantity_checking_sheet_id}/note");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateNoteWithInvalidNote()
    {
        $employee = $this->getEmployeeWithPermission(1, "update-quantity-checking-sheet");

        $quantity_checking_sheet_id = $employee->employment->branch->quantity_checking_sheets->first()->id;

        $response = $this->actingAs($employee)->put("/api/quantity-checking-sheet/{$quantity_checking_sheet_id}/note", [
            "note" => str_repeat("a", 256),
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("quantity_checking_sheets", [
            "id" => $quantity_checking_sheet_id,
            "note" => str_repeat("a", 256),
        ]);
    }

    public function testUpdateNoteNotFound()
    {
        $employee = $this->getEmployeeWithPermission(1, "update-quantity-checking-sheet");

        $response = $this->actingAs($employee)->put("/api/quantity-checking-sheet/9999/note", [
            "note" => "This is a note",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
