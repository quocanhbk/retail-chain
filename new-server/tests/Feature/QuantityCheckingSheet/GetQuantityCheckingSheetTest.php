<?php

namespace Tests\Feature\QuantityCheckingSheet;

use App\Models\QuantityCheckingSheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetQuantityCheckingSheetTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetSheetUnauthenticated()
    {
        $response = $this->get("/api/quantity-checking-sheet/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetSheetAsEmployee()
    {
        $employee = $this->getEmployeeWithPermission(1, "view-quantity-checking-sheet");

        $sheet = QuantityCheckingSheet::where("branch_id", $employee->employment->branch_id)->first();

        $response = $this->actingAs($employee)->get("/api/quantity-checking-sheet/{$sheet->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "code",
            "branch_id",
            "note",
            "employee" => ["name"],
            "items" => [["id", "actual_quantity", "expected_quantity", "total", "item" => ["name"]]],
        ]);
    }

    public function testGetSheetNotFound()
    {
        $employee = $this->getEmployeeWithPermission(1, "view-quantity-checking-sheet");

        $response = $this->actingAs($employee)->get("/api/quantity-checking-sheet/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
