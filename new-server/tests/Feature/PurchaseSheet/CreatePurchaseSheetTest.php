<?php

namespace Tests\Feature\PurchaseSheet;

use App\Models\Item;
use App\Models\ItemProperty;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreatePurchaseSheetTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    private function getTestData()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-purchase-sheet");

        $item_property1 = ItemProperty::where("branch_id", $employee->employment->branch_id)->first();

        $item_property2 = ItemProperty::where("branch_id", $employee->employment->branch_id)
            ->skip(1)
            ->first();

        return [$employee, $item_property1, $item_property2];
    }

    public function testCreatePurchaseSheetUnauthenticated()
    {
        $response = $this->post("/api/purchase-sheet");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreatePurchaseSheetWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-purchase-sheet");

        $response = $this->actingAs($employee)->post("/api/purchase-sheet");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreatePurchaseSheetWithValidPermission()
    {
        [$employee, $item_property] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "items" => [["id" => $item_property->item_id, "quantity" => 10, "price" => 100]],
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "employee_id", "branch_id", "total", "paid_amount", "note"]);

        $this->assertDatabaseHas("purchase_sheets", [
            "branch_id" => $employee->employment->branch_id,
            "employee_id" => $employee->id,
            "total" => 1000,
            "paid_amount" => 0,
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property->item_id,
            "quantity" => 10,
            "price" => 100,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property->item_id,
            "quantity" => $item_property->quantity + 10,
            "base_price" => round(
                ($item_property->base_price * $item_property->quantity + 100 * 10) / ($item_property->quantity + 10)
            ),
            "last_purchase_price" => 100,
        ]);
    }

    public function testCreatePurchaseSheetWithUnsoldItem()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-purchase-sheet");

        $item = Item::factory()->create([
            "store_id" => $store->id,
        ]);

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "items" => [["id" => $item->id, "quantity" => 10, "price" => 100]],
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "employee_id", "branch_id", "total", "paid_amount", "note"]);

        $this->assertDatabaseHas("purchase_sheets", [
            "branch_id" => $employee->employment->branch_id,
            "employee_id" => $employee->id,
            "total" => 1000,
            "paid_amount" => 0,
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item->id,
            "quantity" => 10,
            "price" => 100,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item->id,
            "quantity" => 10,
            "base_price" => 100,
            "last_purchase_price" => 100,
        ]);
    }

    public function testCreatePurchaseSheetWithDiscountAndWithoutDiscountType()
    {
        [$employee, $item_property] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "discount" => 10,
            "items" => [["id" => $item_property->item_id, "quantity" => 10, "price" => 100]],
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreatePurchaseSheetWithDiscountTypeAndWithoutDiscount()
    {
        [$employee, $item_property] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "discount_type" => "percent",
            "items" => [["id" => $item_property->item_id, "quantity" => 10, "price" => 100]],
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreatePurchaseSheetWithDiscountByAmount()
    {
        [$employee, $item_property1, $item_property2] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "discount_type" => "amount",
            "discount" => 100_000,
            "items" => [
                ["id" => $item_property1->item_id, "quantity" => 10, "price" => 50_000],
                ["id" => $item_property2->item_id, "quantity" => 10, "price" => 20_000],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("purchase_sheets", [
            "branch_id" => $employee->employment->branch_id,
            "employee_id" => $employee->id,
            "total" => 600_000,
            "paid_amount" => 0,
            "discount" => 100_000,
            "discount_type" => "amount",
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property1->item_id,
            "quantity" => 10,
            "price" => 50_000,
            "discount" => null,
            "discount_type" => null,
            "total" => 500_000,
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property2->item_id,
            "quantity" => 10,
            "price" => 20_000,
            "discount" => null,
            "discount_type" => null,
            "total" => 200_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property1->item_id,
            "quantity" => $item_property1->quantity + 10,
            "base_price" => round(
                ($item_property1->base_price * $item_property1->quantity + (500_000 - (100_000 * 500_000) / 700_000)) /
                    ($item_property1->quantity + 10)
            ),
            "last_purchase_price" => 50_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property2->item_id,
            "quantity" => $item_property2->quantity + 10,
            "base_price" => round(
                ($item_property2->base_price * $item_property2->quantity + (200_000 - (100_000 * 200_000) / 700_000)) /
                    ($item_property2->quantity + 10)
            ),
            "last_purchase_price" => 20_000,
        ]);
    }

    public function testCreatePurchaseSheetWithDiscountByPercent()
    {
        [$employee, $item_property1, $item_property2] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "discount_type" => "percent",
            "discount" => 10,
            "items" => [
                ["id" => $item_property1->item_id, "quantity" => 10, "price" => 50_000],
                ["id" => $item_property2->item_id, "quantity" => 10, "price" => 20_000],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("purchase_sheets", [
            "branch_id" => $employee->employment->branch_id,
            "employee_id" => $employee->id,
            "total" => 630_000,
            "paid_amount" => 0,
            "discount" => 10,
            "discount_type" => "percent",
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property1->item_id,
            "quantity" => 10,
            "price" => 50_000,
            "discount" => null,
            "discount_type" => null,
            "total" => 500_000,
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property2->item_id,
            "quantity" => 10,
            "price" => 20_000,
            "discount" => null,
            "discount_type" => null,
            "total" => 200_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property1->item_id,
            "quantity" => $item_property1->quantity + 10,
            "base_price" => round(
                ($item_property1->base_price * $item_property1->quantity + (500_000 - (70_000 * 500_000) / 700_000)) /
                    ($item_property1->quantity + 10)
            ),
            "last_purchase_price" => 50_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property2->item_id,
            "quantity" => $item_property2->quantity + 10,
            "base_price" => round(
                ($item_property2->base_price * $item_property2->quantity + (200_000 - (70_000 * 200_000) / 700_000)) /
                    ($item_property2->quantity + 10)
            ),
            "last_purchase_price" => 20_000,
        ]);
    }

    public function testCreatePurchaseSheetWithItemDiscountAndWithoutItemDiscountType()
    {
        [$employee, $item_property] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "items" => [["id" => $item_property->item_id, "quantity" => 10, "price" => 100, "discount" => 10]],
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreatePurchaseSheetWithItemDiscountTypeAndWithoutItemDiscount()
    {
        [$employee, $item_property] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "items" => [
                ["id" => $item_property->item_id, "quantity" => 10, "price" => 100, "discount_type" => "percent"],
            ],
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreatePurchaseSheetWithItemDiscountByAmount()
    {
        [$employee, $item_property1, $item_property2] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "items" => [
                [
                    "id" => $item_property1->item_id,
                    "quantity" => 10,
                    "price" => 50_000,
                    "discount" => 10_000,
                    "discount_type" => "amount",
                ],
                [
                    "id" => $item_property2->item_id,
                    "quantity" => 10,
                    "price" => 20_000,
                    "discount" => 5_000,
                    "discount_type" => "amount",
                ],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("purchase_sheets", [
            "branch_id" => $employee->employment->branch_id,
            "employee_id" => $employee->id,
            "total" => 550_000,
            "paid_amount" => 0,
            "discount" => null,
            "discount_type" => null,
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property1->item_id,
            "quantity" => 10,
            "price" => 50_000,
            "discount" => 10_000,
            "discount_type" => "amount",
            "total" => 400_000,
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property2->item_id,
            "quantity" => 10,
            "price" => 20_000,
            "discount" => 5_000,
            "discount_type" => "amount",
            "total" => 150_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property1->item_id,
            "quantity" => $item_property1->quantity + 10,
            "base_price" => round(
                ($item_property1->base_price * $item_property1->quantity + 400_000) / ($item_property1->quantity + 10)
            ),
            "last_purchase_price" => 50_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property2->item_id,
            "quantity" => $item_property2->quantity + 10,
            "base_price" => round(
                ($item_property2->base_price * $item_property2->quantity + 150_000) / ($item_property2->quantity + 10)
            ),
            "last_purchase_price" => 20_000,
        ]);
    }

    public function testCreatePurchaseSheetWithItemDiscountByPercent()
    {
        [$employee, $item_property1, $item_property2] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "items" => [
                [
                    "id" => $item_property1->item_id,
                    "quantity" => 10,
                    "price" => 50_000,
                    "discount" => 10,
                    "discount_type" => "percent",
                ],
                [
                    "id" => $item_property2->item_id,
                    "quantity" => 10,
                    "price" => 20_000,
                    "discount" => 5,
                    "discount_type" => "percent",
                ],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("purchase_sheets", [
            "branch_id" => $employee->employment->branch_id,
            "employee_id" => $employee->id,
            "total" => 640_000,
            "paid_amount" => 0,
            "discount" => null,
            "discount_type" => null,
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property1->item_id,
            "quantity" => 10,
            "price" => 50_000,
            "discount" => 10,
            "discount_type" => "percent",
            "total" => 450_000,
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property2->item_id,
            "quantity" => 10,
            "price" => 20_000,
            "discount" => 5,
            "discount_type" => "percent",
            "total" => 190_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property1->item_id,
            "quantity" => $item_property1->quantity + 10,
            "base_price" => round(
                ($item_property1->base_price * $item_property1->quantity + 450_000) / ($item_property1->quantity + 10)
            ),
            "last_purchase_price" => 50_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property2->item_id,
            "quantity" => $item_property2->quantity + 10,
            "base_price" => round(
                ($item_property2->base_price * $item_property2->quantity + 190_000) / ($item_property2->quantity + 10)
            ),
            "last_purchase_price" => 20_000,
        ]);
    }

    public function testCreatePurchaseSheetWithDiscountAndItemDiscountByAmount()
    {
        [$employee, $item_property1, $item_property2] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "items" => [
                [
                    "id" => $item_property1->item_id,
                    "quantity" => 10,
                    "price" => 50_000,
                    "discount" => 10_000,
                    "discount_type" => "amount",
                ],
                [
                    "id" => $item_property2->item_id,
                    "quantity" => 10,
                    "price" => 20_000,
                    "discount" => 5_000,
                    "discount_type" => "amount",
                ],
            ],
            "discount" => 100_000,
            "discount_type" => "amount",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("purchase_sheets", [
            "branch_id" => $employee->employment->branch_id,
            "employee_id" => $employee->id,
            "total" => 450_000,
            "paid_amount" => 0,
            "discount" => 100_000,
            "discount_type" => "amount",
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property1->item_id,
            "quantity" => 10,
            "price" => 50_000,
            "discount" => 10_000,
            "discount_type" => "amount",
            "total" => 400_000,
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property2->item_id,
            "quantity" => 10,
            "price" => 20_000,
            "discount" => 5_000,
            "discount_type" => "amount",
            "total" => 150_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property1->item_id,
            "quantity" => $item_property1->quantity + 10,
            "base_price" => round(
                ($item_property1->base_price * $item_property1->quantity + (400_000 - (100_000 * 400_000) / 550_000)) /
                    ($item_property1->quantity + 10)
            ),
            "last_purchase_price" => 50_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property2->item_id,
            "quantity" => $item_property2->quantity + 10,
            "base_price" => round(
                ($item_property2->base_price * $item_property2->quantity + (150_000 - (100_000 * 150_000) / 550_000)) /
                    ($item_property2->quantity + 10)
            ),
            "last_purchase_price" => 20_000,
        ]);
    }

    public function testCreatePurchaseSheetWithDiscountAndItemDiscountByPercent()
    {
        [$employee, $item_property1, $item_property2] = $this->getTestData();

        $response = $this->actingAs($employee)->post("/api/purchase-sheet", [
            "items" => [
                [
                    "id" => $item_property1->item_id,
                    "quantity" => 10,
                    "price" => 50_000,
                    "discount" => 10,
                    "discount_type" => "percent",
                ],
                [
                    "id" => $item_property2->item_id,
                    "quantity" => 10,
                    "price" => 20_000,
                    "discount" => 5,
                    "discount_type" => "percent",
                ],
            ],
            "discount" => 10,
            "discount_type" => "percent",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("purchase_sheets", [
            "branch_id" => $employee->employment->branch_id,
            "employee_id" => $employee->id,
            "total" => 576_000,
            "paid_amount" => 0,
            "discount" => 10,
            "discount_type" => "percent",
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property1->item_id,
            "quantity" => 10,
            "price" => 50_000,
            "discount" => 10,
            "discount_type" => "percent",
            "total" => 450_000,
        ]);

        $this->assertDatabaseHas("purchase_sheet_items", [
            "purchase_sheet_id" => $response->json("id"),
            "item_id" => $item_property2->item_id,
            "quantity" => 10,
            "price" => 20_000,
            "discount" => 5,
            "discount_type" => "percent",
            "total" => 190_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property1->item_id,
            "quantity" => $item_property1->quantity + 10,
            "base_price" => round(
                ($item_property1->base_price * $item_property1->quantity + (450_000 - (64_000 * 450_000) / 640_000)) /
                    ($item_property1->quantity + 10)
            ),
            "last_purchase_price" => 50_000,
        ]);

        $this->assertDatabaseHas("item_properties", [
            "item_id" => $item_property2->item_id,
            "quantity" => $item_property2->quantity + 10,
            "base_price" => round(
                ($item_property2->base_price * $item_property2->quantity + (190_000 - (64_000 * 190_000) / 640_000)) /
                    ($item_property2->quantity + 10)
            ),
            "last_purchase_price" => 20_000,
        ]);
    }
}
