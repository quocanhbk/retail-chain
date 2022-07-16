<?php

namespace Tests\Feature\Supplier;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreateSupplierTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testCreateSupplierUnauthenticated()
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

    public function testCreateSupplierWithInvalidPermission()
    {
        $store = Store::find(1);

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

    public function testCreateSupplierWithValidPermission()
    {
        $store = Store::find(1);

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

    public function testCreateSupplierWithEmail()
    {
        $store = Store::find(1);

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

    public function testCreateSupplierWithPhone()
    {
        $store = Store::find(1);

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

    public function testCreateSupplierWithDuplicateEmail()
    {
        $store = Store::find(1);

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
            "email" => $supplier->email,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateSupplierWithDuplicatePhone()
    {
        $store = Store::find(1);

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
            "phone" => $supplier->phone,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateSupplierWithNoPhoneOrEmail()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateSupplierWithDuplicateCode()
    {
        $store = Store::find(1);

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->post("/api/supplier", [
            "name" => "My supplier",
            "code" => $supplier->code,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateSupplierWithCodeProvided()
    {
        $store = Store::find(1);

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
