<?php

namespace Tests\Feature\Customer;

use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetCustomerTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetCustomerUnauthenticated()
    {
        $response = $this->get("/api/customer/one?id=1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetCustomerWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer/one?id=1");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetCustomerWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $customer = $store->customers->first();

        $response = $this->actingAs($employee)->get("/api/customer/one?id={$customer->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function testGetCustomerNoInput()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer/one");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetCustomerByAdmin()
    {
        $store = Store::find(1);

        $customer = $store->customers->first();

        $response = $this->actingAs($store, "stores")->get("/api/customer/one?id={$customer->id}");

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $customer->id,
            "code" => $customer->code,
            "name" => $customer->name,
            "email" => $customer->email,
        ]);

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function testGetCustomerNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer/one?id=9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetCustomerByCodeByAdmin()
    {
        $store = Store::find(1);

        $customer = $store->customers->first();

        $response = $this->actingAs($store, "stores")->get("/api/customer/one?code={$customer->code}");

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $customer->id,
            "code" => $customer->code,
            "name" => $customer->name,
            "email" => $customer->email,
        ]);

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function testGetCustomerByCodeByEmployee()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $customer = $employee->store->customers->first();

        $response = $this->actingAs($employee)->get("/api/customer/one?code={$customer->code}");

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $customer->id,
            "code" => $customer->code,
            "name" => $customer->name,
            "email" => $customer->email,
        ]);

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function testGetCustomerByCodeNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer/one?code=abc");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
