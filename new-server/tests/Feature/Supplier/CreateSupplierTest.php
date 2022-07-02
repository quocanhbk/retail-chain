<?php

namespace Tests\Feature\Supplier;

use App\Models\Employee;
use App\Models\PermissionRole;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreateSupplierTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_create_supplier_unauthenticated()
    {
        $response = $this->post("/api/supplier", [
            "name" => "My supplier",
            "phone" => "081234567890",
        ]);

        $response->assertStatus(401);

        $this->assertDatabaseMissing("suppliers", [
            "name" => "My supplier",
            "email" => null,
            "phone" => "081234567890",
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_supplier_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-supplier");

        $response = $this->actingAs($employee)->post("/api/supplier", [
            "name" => "My supplier",
            "phone" => "081234567890",
        ]);

        $this->assertDatabaseMissing("suppliers", [
            "name" => "My supplier",
            "email" => null,
            "phone" => "081234567890",
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_supplier_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-supplier");

        $response = $this->actingAs($employee)->post("/api/supplier", [
            "name" => "My supplier",
            "phone" => "081234567890",
        ]);

        $this->assertDatabaseHas("suppliers", [
            "name" => "My supplier",
            "email" => null,
            "phone" => "081234567890",
        ]);

        $response->assertJson([
            "name" => "My supplier",
            "email" => null,
            "phone" => "081234567890",
            "store_id" => $store->id,
        ]);
    }

    public function test_create_supplier_with_email()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
            "email" => "supplier@gmail.com",
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            "name" => "My supplier",
            "email" => "supplier@gmail.com",
            "phone" => null,
            "store_id" => $store->id,
        ]);

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

        $response->assertJson([
            "name" => "My supplier",
            "email" => null,
            "phone" => "081234567890",
            "store_id" => $store->id,
        ]);

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

        $response->assertJsonStructure(["message"]);
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

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_supplier_with_no_phone_or_email()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
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

        $response->assertJsonStructure(["message"]);
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
}
