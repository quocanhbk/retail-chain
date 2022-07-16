<?php

namespace Tests\Feature\QuantityCheckingSheet;

use App\Models\ItemProperty;
use App\Models\QuantityCheckingSheet;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class DeleteQuantityCheckingSheetTest extends TestCase
{
    use RefreshDatabase, QueryEmployeeTrait;

    public function testDeleteSheetUnauthenticated()
    {
        $response = $this->delete("/api/quantity-checking-sheet/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteSheetWithInvalidPermission()
    {
        $employee = $this->getEmployeeWithoutPermission(1, "delete-quantity-checking-sheet");

        $response = $this->actingAs($employee)->delete("/api/quantity-checking-sheet/1");

        $response->assertStatus(403);
    }

    public function testDeleteSheetWithValidPermission()
    {
        $employee = $this->getEmployeeWithPermission(1, "delete-quantity-checking-sheet");

        $creator = $this->getEmployeeWithPermission(1, "create-quantity-checking-sheet");

        $item_property = ItemProperty::where("branch_id", $employee->employment->branch_id)->first();

        $response = $this->actingAs($creator)->post("/api/quantity-checking-sheet", [
            "items" => [
                [
                    "id" => $item_property->item_id,
                    "actual_quantity" => $item_property->quantity + 10,
                ],
            ],
        ]);

        $res = $this->actingAs($employee)->delete("/api/quantity-checking-sheet/{$response->json("id")}");

        $res->assertStatus(200);

        $res->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("item_properties", [
            "id" => $item_property->id,
            "quantity" => $item_property->quantity,
        ]);
    }

    public function testDeleteSheetNotFound()
    {
        $employee = $this->getEmployeeWithPermission(1, "delete-quantity-checking-sheet");

        $response = $this->actingAs($employee)->delete("/api/quantity-checking-sheet/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
