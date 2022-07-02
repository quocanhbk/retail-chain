<?php

namespace Tests\Feature\Customer;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreateCustomerTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_create_customer_unauthenticated()
    {
        $store = Store::first();

        $response = $this->post("/api/customer");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_customer_by_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/customer", [
            "name" => "Test Customer",
            "email" => "customer@gmail.com",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("customers", [
            "name" => "Test Customer",
            "email" => "customer@gmail.com",
        ]);

        $response->assertJson([
            "name" => "Test Customer",
            "email" => "customer@gmail.com",
        ]);

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function test_create_customer_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-customer");

        $response = $this->actingAs($employee)->post("/api/customer", [
            "name" => "Test Customer",
            "email" => "customer@gmail.com",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_customer_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-customer");

        $response = $this->actingAs($employee)->post("/api/customer", [
            "name" => "Test Customer",
            "email" => "customer@gmail.com",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("customers", [
            "name" => "Test Customer",
            "email" => "customer@gmail.com",
        ]);

        $response->assertJson([
            "name" => "Test Customer",
            "email" => "customer@gmail.com",
        ]);
    }

    public function test_create_customer_invalid_input()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-customer");

        $response = $this->actingAs($employee)->post("/api/customer", [
            "name" => "Test Customer",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_customer_duplicate_email()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-customer");

        $customer = $employee->store->customers->first();

        $response = $this->actingAs($employee)->post("/api/customer", [
            "name" => "Test Customer",
            "email" => $customer->email,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
