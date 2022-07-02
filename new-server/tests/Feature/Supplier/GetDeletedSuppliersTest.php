<?php

namespace Tests\Feature\Supplier;

use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetDeletedSuppliersTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_get_deleted_suppliers_unauthenticated()
    {
        $response = $this->get("/api/supplier/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_deleted_suppliers_as_employee()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-supplier");

        $response = $this->actingAs($employee)->get("/api/supplier/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_deleted_suppliers_as_admin()
    {
        $store = Store::first();

        $store->suppliers()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/supplier/deleted");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "address", "phone", "email", "tax_number", "created_at", "updated_at"],
        ]);
    }

    public function test_get_deleted_suppliers_with_search()
    {
        $store = Store::first();

        $store->suppliers()->delete();

        $supplier = Supplier::onlyTrashed()->first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier/deleted?search={$supplier->name}");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }

    public function test_get_deleted_suppliers_with_pagination()
    {
        $store = Store::first();

        $store->suppliers()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/supplier/deleted?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }
}
