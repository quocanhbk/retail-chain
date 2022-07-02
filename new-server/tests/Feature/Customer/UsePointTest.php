<?php

namespace Tests\Feature\Customer;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UsePointTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_use_point_to_customer_unauthenticated()
    {
        $response = $this->post("/api/customer/use-point/1", [
            "point" => 10,
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_use_point_to_customer_by_admin()
    {
        $store = Store::first();

        $customer = $store->customers->first();

        $response = $this->actingAs($store, "stores")->post("/api/customer/use-point/{$customer->id}", [
            "point" => $customer->point,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("customers", [
            "id" => $customer->id,
            "point" => 0,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_use_point_to_customer_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "update-customer");

        $customer = $store->customers->first();

        $response = $this->actingAs($employee)->post("/api/customer/use-point/{$customer->id}", [
            "point" => $customer->point,
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_use_point_to_customer_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "update-customer");

        $customer = $store->customers->first();

        $response = $this->actingAs($employee)->post("/api/customer/use-point/{$customer->id}", [
            "point" => $customer->point,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("customers", [
            "id" => $customer->id,
            "point" => 0,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_use_point_to_customer_not_found()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "update-customer");

        $response = $this->actingAs($employee)->post("/api/customer/use-point/99", [
            "point" => 10,
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_use_point_not_enough_point()
    {
        $store = Store::first();

        $customer = $store->customers->first();

        $response = $this->actingAs($store, "stores")->post("/api/customer/use-point/{$customer->id}", [
            "point" => $customer->point + 10,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
