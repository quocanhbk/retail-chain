<?php

namespace Tests\Feature\Supplier;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UpdateSupplierTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testUpdateSupplierUnauthenticated()
    {
        $response = $this->put("/api/supplier/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateSupplierAsAdmin()
    {
        $store = Store::find(1);

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($store, "stores")->put("/api/supplier/" . $supplier->id, [
            "name" => "New Name",
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $supplier->id,
            "name" => "New Name",
        ]);

        $this->assertDatabaseHas("suppliers", [
            "id" => $supplier->id,
            "name" => "New Name",
        ]);
    }

    public function testUpdateSupplierWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "update-supplier");

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($employee)->put("/api/supplier/" . $supplier->id, [
            "name" => "New Name",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateSupplierWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-supplier");

        $supplier = $store->suppliers->first();

        $response = $this->actingAs($employee)->put("/api/supplier/" . $supplier->id, [
            "name" => "New Name",
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $supplier->id,
            "name" => "New Name",
        ]);

        $this->assertDatabaseHas("suppliers", [
            "id" => $supplier->id,
            "name" => "New Name",
        ]);
    }

    public function testUpdateSupplierNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-supplier");

        $response = $this->actingAs($employee)->put("/api/supplier/9999", [
            "name" => "New Name",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateSupplierWithDuplicateCode()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-supplier");

        $supplier = $store->suppliers->first();

        $other_supplier = $store->suppliers->where("id", "!=", $supplier->id)->first();

        $response = $this->actingAs($employee)->put("/api/supplier/" . $supplier->id, [
            "code" => $other_supplier->code,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateSupplierWithDuplicatePhone()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-supplier");

        $supplier = $store->suppliers->first();

        $other_supplier = $store->suppliers->where("id", "!=", $supplier->id)->first();

        $response = $this->actingAs($employee)->put("/api/supplier/" . $supplier->id, [
            "phone" => $other_supplier->phone,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateSupplierWithDuplicateEmail()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-supplier");

        $supplier = $store->suppliers->first();

        $other_supplier = $store->suppliers->where("id", "!=", $supplier->id)->first();

        $response = $this->actingAs($employee)->put("/api/supplier/" . $supplier->id, [
            "email" => $other_supplier->email,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
