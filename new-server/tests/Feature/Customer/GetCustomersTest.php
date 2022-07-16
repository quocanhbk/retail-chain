<?php

namespace Tests\Feature\Customer;

use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetCustomersTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetCustomersUnauthenticated()
    {
        $response = $this->get("/api/customer");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetCustomersByAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/customer");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "code", "name", "email", "created_at", "updated_at"]]);
    }

    public function testGetCustomersWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetCustomersWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "code", "name", "email", "created_at", "updated_at"]]);
    }

    public function testGetCustomersWithSearch()
    {
        $store = Store::find(1);

        $customer = $store->customers()->first();

        $response = $this->actingAs($store, "stores")->get("/api/customer?search=" . $customer->name);

        $response->assertStatus(200);

        $response->assertJsonFragment(["name" => $customer->name]);
    }

    public function testGetCustomersWithPagination()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/customer?from=0&to=2");

        $response->assertStatus(200);

        $response->assertJsonCount(2);
    }
}
