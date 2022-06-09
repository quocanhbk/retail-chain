<?php

namespace Tests\Feature;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_create_supplier_with_email()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
            "email" => "supplier@gmail.com",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("suppliers", [
            "name" => "My supplier",
            "email" => "supplier@gmail.com",
            "phone" => null,
            "store_id" => $store->id,
        ]);
    }

    public function test_create_supplier_with_phone()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
            "phone" => "081234567890",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("suppliers", [
            "name" => "My supplier",
            "email" => null,
            "phone" => "081234567890",
            "store_id" => $store->id,
        ]);
    }

    public function test_create_supplier_with_duplicate_email()
    {
        $store = Store::first();

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
            "email" => $supplier->email,
        ]);

        $response->assertStatus(400);
    }

    public function test_create_supplier_with_duplicate_phone()
    {
        $store = Store::first();

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
            "phone" => $supplier->phone,
        ]);

        $response->assertStatus(400);
    }

    public function test_create_supplier_with_no_phone_or_email()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
        ]);

        $response->assertStatus(400);
    }

    public function test_create_supplier_with_duplicate_code()
    {
        $store = Store::first();

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
            "code" => $supplier->code,
        ]);

        $response->assertStatus(400);
    }

    public function test_create_supplier_with_code_provided()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
            "phone" => "081234567890",
            "code" => "SUP123456",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("suppliers", [
            "name" => "My supplier",
            "phone" => "081234567890",
            "store_id" => $store->id,
            "code" => "SUP123456",
        ]);

        $response->assertJson([
            "name" => "My supplier",
            "phone" => "081234567890",
            "code" => "SUP123456",
        ]);
    }

    public function test_get_suppliers_successfully()
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

    public function test_get_suppliers_with_from_to()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier?from=0&to=2");

        $response->assertStatus(200);

        $response->assertJsonCount(2);
    }

    public function test_get_supplier_successfully()
    {
        $store = Store::first();

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier/" . $supplier->id);

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $supplier->id,
            "name" => $supplier->name,
            "address" => $supplier->address,
            "code" => $supplier->code,
            "phone" => $supplier->phone,
            "email" => $supplier->email,
            "tax_number" => $supplier->tax_number,
            "note" => $supplier->note,
            "store_id" => $supplier->store_id,
        ]);
    }

    public function test_get_supplier_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/supplier/0");

        $response->assertStatus(404);
    }

    public function test_update_supplier_successfully()
    {
        $store = Store::first();

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->put("/api/supplier/" . $supplier->id, [
            "name" => "My supplier updated",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("suppliers", [
            "name" => "My supplier updated",
            "email" => $supplier->email,
            "phone" => $supplier->phone,
            "store_id" => $supplier->store_id,
        ]);

        $response->assertJson([
            "name" => "My supplier updated",
            "phone" => $supplier->phone,
            "email" => $supplier->email,
        ]);
    }

    public function test_update_supplier_not_found()
    {
        $store = Store::first();

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->put("/api/supplier/99", [
            "name" => "My supplier updated",
        ]);

        $response->assertStatus(400);
    }

    public function test_delete_supplier_successfully()
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
    }

    public function test_delete_supplier_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->delete("/api/supplier/99");

        $response->assertStatus(404);
    }
}
