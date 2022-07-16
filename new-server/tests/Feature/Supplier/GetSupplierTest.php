<?php

namespace Tests\Feature\Supplier;

use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetSupplierTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetSupplierUnauthenticated()
    {
        $response = $this->get("/api/supplier/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetSupplierAsAdmin()
    {
        $store = Store::find(1);

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier/" . $supplier->id);

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $supplier->id,
            "name" => $supplier->name,
        ]);
    }

    public function testGetSupplierWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "view-supplier");

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($employee)->get("/api/supplier/" . $supplier->id);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetSupplierWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-supplier");

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($employee)->get("/api/supplier/" . $supplier->id);

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $supplier->id,
            "name" => $supplier->name,
        ]);
    }

    public function testGetSupplierNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-supplier");

        $response = $this->actingAs($employee)->get("/api/supplier/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
