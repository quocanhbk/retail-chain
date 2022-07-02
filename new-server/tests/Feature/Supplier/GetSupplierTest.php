<?php

namespace Tests\Feature\Supplier;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetSupplierTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_get_supplier_unauthenticated()
    {
        $response = $this->get("/api/supplier/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_supplier_as_admin()
    {
        $store = Store::first();

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier/" . $supplier->id);

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $supplier->id,
            "name" => $supplier->name,
        ]);
    }

    public function test_get_supplier_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "view-supplier");

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($employee)->get("/api/supplier/" . $supplier->id);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_supplier_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-supplier");

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($employee)->get("/api/supplier/" . $supplier->id);

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $supplier->id,
            "name" => $supplier->name,
        ]);
    }

    public function test_get_supplier_not_found()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-supplier");

        $response = $this->actingAs($employee)->get("/api/supplier/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
