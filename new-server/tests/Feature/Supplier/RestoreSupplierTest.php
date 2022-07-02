<?php

namespace Tests\Feature\Supplier;

use App\Models\Employee;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class RestoreSupplierTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_restore_supplier_unauthenticated()
    {
        $store = Store::first();

        $store->suppliers()->delete();

        $supplier = Supplier::onlyTrashed()
            ->where("store_id", $store->id)
            ->first();

        $response = $this->post("/api/supplier/{$supplier->id}/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_restore_supplier_as_employee()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        $store->suppliers()->delete();

        $supplier = Supplier::onlyTrashed()
            ->where("store_id", $store->id)
            ->first();

        $response = $this->actingAs($employee)->post("/api/supplier/{$supplier->id}/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_restore_supplier_as_admin()
    {
        $store = Store::first();

        $store->suppliers()->delete();

        $supplier = Supplier::onlyTrashed()
            ->where("store_id", $store->id)
            ->first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier/{$supplier->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("suppliers", [
            "id" => $supplier->id,
            "deleted_at" => null,
        ]);
    }

    public function test_restore_supplier_not_found()
    {
        $store = Store::first();

        $store->suppliers()->delete();

        $response = $this->actingAs($store, "stores")->post("/api/supplier/9999/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
