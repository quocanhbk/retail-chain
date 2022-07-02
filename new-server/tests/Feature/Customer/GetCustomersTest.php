<?php

namespace Tests\Feature\Customer;

use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetCustomersTest extends TestCase
{
    use QueryEmployeeTrait;

    public function test_get_customers_unauthenticated()
    {
        $response = $this->get("/api/customer");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customers_by_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/customer");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "code", "name", "email", "created_at", "updated_at"]]);
    }

    public function test_get_customers_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customers_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "code", "name", "email", "created_at", "updated_at"]]);
    }

    public function test_get_customers_with_search()
    {
        $store = Store::first();

        $customer = $store->customers()->first();

        $response = $this->actingAs($store, "stores")->get("/api/customer?search=" . $customer->name);

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }

    public function test_get_customers_with_pagination()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/customer?from=0&to=2");

        $response->assertStatus(200);

        $response->assertJsonCount(2);
    }
}
