<?php

namespace Tests\Feature\Customer;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetCustomerTest extends TestCase
{
    use QueryEmployeeTrait;

    public function test_get_customer_unauthenticated()
    {
        $response = $this->get("/api/customer/one?id=1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customer_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer/one?id=1");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customer_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $customer = $store->customers->first();

        $response = $this->actingAs($employee)->get("/api/customer/one?id={$customer->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function test_get_customer_no_input()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer/one");

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customer_by_admin()
    {
        $store = Store::first();

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

    public function test_get_customer_not_found()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer/one?id=9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customer_by_code_by_admin()
    {
        $store = Store::first();

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

    public function test_get_customer_by_code_by_employee()
    {
        $store = Store::first();

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

    public function test_get_customer_by_code_not_found()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-customer");

        $response = $this->actingAs($employee)->get("/api/customer/one?code=abc");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
