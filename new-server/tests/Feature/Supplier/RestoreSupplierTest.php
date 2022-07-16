<?php

namespace Tests\Feature\Supplier;

use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class RestoreSupplierTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testRestoreSupplierUnauthenticated()
    {
        $store = Store::find(1);

        $store->suppliers()->delete();

        $supplier = Supplier::onlyTrashed()
            ->where("store_id", $store->id)
            ->first();

        $response = $this->post("/api/supplier/{$supplier->id}/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testRestoreSupplierAsEmployee()
    {
        $store = Store::find(1);

        $employee = $store->employees->first();

        $store->suppliers()->delete();

        $supplier = Supplier::onlyTrashed()
            ->where("store_id", $store->id)
            ->first();

        $response = $this->actingAs($employee)->post("/api/supplier/{$supplier->id}/restore");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testRestoreSupplierAsAdmin()
    {
        $store = Store::find(1);

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

    public function testRestoreSupplierNotFound()
    {
        $store = Store::find(1);

        $store->suppliers()->delete();

        $response = $this->actingAs($store, "stores")->post("/api/supplier/9999/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
