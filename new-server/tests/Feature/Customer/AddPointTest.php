<?php

namespace Tests\Feature\Customer;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class AddPointTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testAddPointToCustomerUnauthenticated()
    {
        $customer = Store::first()->customers->first();

        $response = $this->post("/api/customer/add-point/{$customer->id}", [
            "point" => 1,
        ]);

        $response->assertStatus(401);
    }

    public function testAddPointToCustomerByAdmin()
    {
        $store = Store::find(1);

        $customer = $store->customers->first();

        $response = $this->actingAs($store, "stores")->post("/api/customer/add-point/{$customer->id}", [
            "point" => 10,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("customers", [
            "id" => $customer->id,
            "point" => $customer->point + 10,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function testAddPointToCustomerWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "update-customer");

        $customer = $store->customers->first();

        $response = $this->actingAs($employee)->post("/api/customer/add-point/{$customer->id}", [
            "point" => 10,
        ]);

        $response->assertStatus(403);
    }

    public function testAddPointToCustomerWithValidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-customer");

        $customer = $store->customers->first();

        $response = $this->actingAs($employee)->post("/api/customer/add-point/{$customer->id}", [
            "point" => 10,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("customers", [
            "id" => $customer->id,
            "point" => $customer->point + 10,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function testAddPointToCustomerNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "update-customer");

        $response = $this->actingAs($employee)->post("/api/customer/add-point/99", [
            "point" => 10,
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
