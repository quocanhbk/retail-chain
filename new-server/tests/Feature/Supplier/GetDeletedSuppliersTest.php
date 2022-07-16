<?php

namespace Tests\Feature\Supplier;

use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetDeletedSuppliersTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testGetDeletedSuppliersUnauthenticated()
    {
        $response = $this->get("/api/supplier/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetDeletedSuppliersAsEmployee()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-supplier");

        $response = $this->actingAs($employee)->get("/api/supplier/deleted");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetDeletedSuppliersAsAdmin()
    {
        $store = Store::find(1);

        $store->suppliers()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/supplier/deleted");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            ["id", "name", "address", "phone", "email", "tax_number", "created_at", "updated_at"],
        ]);
    }

    public function testGetDeletedSuppliersWithSearch()
    {
        $store = Store::find(1);

        $store->suppliers()->delete();

        $supplier = Supplier::onlyTrashed()->first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier/deleted?search={$supplier->name}");

        $response->assertStatus(200);

        $response->assertJsonFragment(["name" => $supplier->name]);
    }

    public function testGetDeletedSuppliersWithPagination()
    {
        $store = Store::find(1);

        $store->suppliers()->delete();

        $response = $this->actingAs($store, "stores")->get("/api/supplier/deleted?from=0&to=1");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }
}
