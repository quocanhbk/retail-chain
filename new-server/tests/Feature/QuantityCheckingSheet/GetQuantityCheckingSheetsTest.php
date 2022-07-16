<?php

namespace Tests\Feature\QuantityCheckingSheet;

use App\Models\QuantityCheckingSheet;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetQuantityCheckingSheetsTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetSheetsUnauthenticated()
    {
        $response = $this->get("/api/quantity-checking-sheet");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetSheetAsEmployee()
    {
        $employee = $this->getEmployeeWithPermission(1, "view-quantity-checking-sheet");

        $response = $this->actingAs($employee)->get("/api/quantity-checking-sheet");

        $response->assertStatus(200);

        $response->assertJsonStructure([[
            "id",
            "code",
            "branch_id",
            "note",
            "employee" => [
                "name",
            ]
        ]]);
    }

    public function testGetSheetWithPagination()
    {
        $employee = $this->getEmployeeWithPermission(1, "view-quantity-checking-sheet");

        $response = $this->actingAs($employee)->get("/api/quantity-checking-sheet?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonStructure([[
            "id",
            "code",
            "branch_id",
            "note",
            "employee" => [
                "name",
            ]
        ]]);

        $response->assertJsonCount(1);
    }

    public function testGetSheetWithSearch()
    {
        $employee = $this->getEmployeeWithPermission(1, "view-quantity-checking-sheet");

        $quantity_checking_sheet = QuantityCheckingSheet::where("branch_id", $employee->employment->branch_id)->first();

        $response = $this->actingAs($employee)->get("/api/quantity-checking-sheet?search={$quantity_checking_sheet->code}");

        $response->assertStatus(200);

        $response->assertJsonStructure([[
            "id",
            "code",
            "branch_id",
            "note",
            "employee" => [
                "name",
            ]
        ]]);

        $response->assertJsonCount(1);

        $response->assertJsonFragment([
            "code" => $quantity_checking_sheet->code,
        ]);
    }
}
