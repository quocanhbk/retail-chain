<?php

namespace Tests\Feature\Supplier;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class DeleteSupplierTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_delete_supplier_unauthenticated()
    {
        $response = $this->delete("/api/supplier/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_supplier_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "delete-supplier");

        $response = $this->actingAs($employee)->delete("/api/supplier/1");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_supplier_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "delete-supplier");

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($employee)->delete("/api/supplier/{$supplier->id}");

        $this->assertSoftDeleted("suppliers", [
            "name" => $supplier->name,
            "email" => $supplier->email,
            "phone" => $supplier->phone,
            "store_id" => $supplier->store_id,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_supplier_as_admin()
    {
        $store = Store::first();

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->delete("/api/supplier/" . $supplier->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted("suppliers", [
            "name" => $supplier->name,
            "email" => $supplier->email,
            "phone" => $supplier->phone,
            "store_id" => $supplier->store_id,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_supplier_not_found()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "delete-supplier");

        $response = $this->actingAs($employee)->delete("/api/supplier/999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_force_delete_supplier_as_employee()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "delete-supplier");

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($employee)->delete("/api/supplier/{$supplier->id}?force=true");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_force_delete_supplier_as_admin()
    {
        $store = Store::first();

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->delete("/api/supplier/{$supplier->id}?force=true");

        $response->assertStatus(200);

        $this->assertDeleted("suppliers", [
            "id" => $supplier->id,
        ]);

        $response->assertJsonStructure(["message"]);
    }
}
