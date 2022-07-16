<?php

namespace Tests\Feature\Supplier;

use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class ForceDeleteSupplierTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testForceDeleteSupplierUnauthenticated()
    {
        $response = $this->delete("/api/supplier/1/force");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testForceDeleteSupplierAsEmployee()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "delete-supplier");

        $response = $this->actingAs($employee)->delete("/api/supplier/1/force");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testForceDeleteSupplierAsAdmin()
    {
        $store = Store::find(1);

        $store->suppliers()->delete();

        $supplier = Supplier::onlyTrashed()
            ->where("store_id", $store->id)
            ->first();

        $response = $this->actingAs($store, "stores")->delete("/api/supplier/{$supplier->id}/force");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("suppliers", ["id" => $supplier->id]);
    }

    public function testForceDeleteSupplierNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->delete("/api/supplier/9999/force");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
