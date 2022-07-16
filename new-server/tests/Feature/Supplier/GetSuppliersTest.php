<?php

namespace Tests\Feature\Supplier;

use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetSuppliersTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetSupplierUnauthenticated()
    {
        $response = $this->get("/api/supplier/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetSupplierInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "view-supplier");

        $response = $this->actingAs($employee)->get("/api/supplier");

        $response->assertStatus(403);
    }

    public function testGetSupplierWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-supplier");

        $response = $this->actingAs($employee)->get("/api/supplier");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "address", "code", "phone", "email", "tax_number", "note", "store_id"],
        ]);
    }

    public function testGetSuppliersAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/supplier");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "address", "code", "phone", "email", "tax_number", "note", "store_id"],
        ]);
    }

    public function testGetSuppliersWithSearch()
    {
        $store = Store::find(1);

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier?search=" . $supplier->name);

        $response->assertStatus(200);

        $response->assertJsonFragment(["name" => $supplier->name]);
    }

    public function testGetSuppliersWithPagination()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/supplier?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }
}
