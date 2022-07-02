<?php

namespace Tests\Feature\Customer;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UpdateCustomerTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_update_customer_unauthenticated()
    {
        $response = $this->put("/api/customer/1", [
            "name" => "Test Customer Updated",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_customer_by_admin()
    {
        $store = Store::first();

        $customer = $store->customers->first();

        $response = $this->actingAs($store, "stores")->put("/api/customer/{$customer->id}", [
            "name" => "Test Customer Updated",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("customers", [
            "id" => $customer->id,
            "name" => "Test Customer Updated",
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_customer_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "update-customer");

        $customer = $employee->store->customers->first();

        $response = $this->actingAs($employee)->put("/api/customer/{$customer->id}", [
            "name" => "Test Customer Updated",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_customer_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "update-customer");

        $customer = $store->customers->first();

        $response = $this->actingAs($employee)->put("/api/customer/{$customer->id}", [
            "name" => "Test Customer Updated",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("customers", [
            "id" => $customer->id,
            "name" => "Test Customer Updated",
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_customer_not_found()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "update-customer");

        $response = $this->actingAs($employee)->put("/api/customer/9999", [
            "name" => "Test Customer Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
