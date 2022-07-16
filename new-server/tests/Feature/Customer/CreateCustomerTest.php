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

    public function testCreateCustomerUnauthenticated()
    {
        $store = Store::find(1);

        $response = $this->post("/api/customer");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateCustomerByAdmin()
    {
        $store = Store::find(1);

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

    public function testCreateCustomerWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-customer");

        $response = $this->actingAs($employee)->post("/api/customer", [
            "name" => "Test Customer",
            "email" => "customer@gmail.com",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateCustomerWithValidPermission()
    {
        $store = Store::find(1);

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

    public function testCreateCustomerInvalidInput()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-customer");

        $response = $this->actingAs($employee)->post("/api/customer", [
            "name" => "Test Customer",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateCustomerDuplicateEmail()
    {
        $store = Store::find(1);

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
