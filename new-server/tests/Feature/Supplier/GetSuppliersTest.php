<?php

namespace Tests\Feature\Supplier;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetSuppliersTest extends TestCase
{
    use QueryEmployeeTrait;
    use RefreshDatabase;

    public function test_get_supplier_unauthenticated()
    {
        $response = $this->get("/api/supplier/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_supplier_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "view-supplier");

        $response = $this->actingAs($employee)->get("/api/supplier");

        $response->assertStatus(403);
    }

    public function test_get_supplier_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-supplier");

        $response = $this->actingAs($employee)->get("/api/supplier");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "address", "code", "phone", "email", "tax_number", "note", "store_id"],
        ]);
    }

    public function test_get_suppliers_as_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "address", "code", "phone", "email", "tax_number", "note", "store_id"],
        ]);
    }

    public function test_get_suppliers_with_search()
    {
        $store = Store::first();

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier?search=" . $supplier->name);

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }

    public function test_get_suppliers_with_pagination()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier?from=0&to=2");

        $response->assertStatus(200);

        $response->assertJsonCount(2);
    }
}
