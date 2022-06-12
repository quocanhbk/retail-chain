<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_create_customer_by_admin_successfully()
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

    public function test_create_customer_by_sale_staff_successfully()
    {
        $employee = Employee::first();

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

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function test_create_customer_unauthorized()
    {
        $response = $this->post("/api/customer", [
            "name" => "Test Customer",
            "email" => "customer@gmail.com",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_customer_invalid_input()
    {
        $response = $this->actingAs(Employee::first())->post("/api/customer", [
            "name" => "Test Customer",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_customer_duplicate_email()
    {
        $employee = Employee::first();

        $customer = $employee->store->customers->first();

        $response = $this->actingAs($employee)->post("/api/customer", [
            "name" => "Test Customer",
            "email" => $customer->email,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_customer_duplicate_email_different_store()
    {
        $employee = Employee::first();

        $customer = Store::where("id", "!=", $employee->store_id)
            ->first()
            ->customers->first();

        $response = $this->actingAs($employee)->post("/api/customer", [
            "name" => "Test Customer",
            "email" => $customer->email,
        ]);

        $response->assertStatus(200);
    }

    public function test_get_customers_by_admin_successfully()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/customer");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "code", "name", "email", "created_at", "updated_at"]]);
    }

    public function test_get_customers_by_sale_staff_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/customer");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "code", "name", "email", "created_at", "updated_at"]]);
    }

    public function test_get_customers_unauthorized()
    {
        $response = $this->get("/api/customer");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customer_by_admin_successfully()
    {
        $store = Store::first();

        $customer = $store->customers->first();

        $response = $this->actingAs($store, "stores")->get("/api/customer/{$customer->id}");

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $customer->id,
            "code" => $customer->code,
            "name" => $customer->name,
            "email" => $customer->email,
        ]);

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function test_get_customer_by_sale_staff_successfully()
    {
        $employee = Employee::first();

        $customer = $employee->store->customers->first();

        $response = $this->actingAs($employee)->get("/api/customer/{$customer->id}");

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $customer->id,
            "code" => $customer->code,
            "name" => $customer->name,
            "email" => $customer->email,
        ]);

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function test_get_customer_unauthorized()
    {
        $response = $this->get("/api/customer/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customer_not_found()
    {
        $response = $this->actingAs(Employee::first())->get("/api/customer/99");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customer_of_other_store()
    {
        $employee = Employee::first();

        $customer = Store::where("id", "!=", $employee->store_id)
            ->first()
            ->customers->first();

        $response = $this->actingAs($employee)->get("/api/customer/{$customer->id}");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customer_by_code_by_admin_successfully()
    {
        $store = Store::first();

        $customer = $store->customers->first();

        $response = $this->actingAs($store, "stores")->get("/api/customer/code/{$customer->code}");

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $customer->id,
            "code" => $customer->code,
            "name" => $customer->name,
            "email" => $customer->email,
        ]);

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function test_get_customer_by_code_by_sale_staff_successfully()
    {
        $employee = Employee::first();

        $customer = $employee->store->customers->first();

        $response = $this->actingAs($employee)->get("/api/customer/code/{$customer->code}");

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $customer->id,
            "code" => $customer->code,
            "name" => $customer->name,
            "email" => $customer->email,
        ]);

        $response->assertJsonStructure(["id", "code", "name", "email", "created_at", "updated_at"]);
    }

    public function test_get_customer_by_code_unauthorized()
    {
        $response = $this->get("/api/customer/code/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customer_by_code_not_found()
    {
        $response = $this->actingAs(Employee::first())->get("/api/customer/code/99");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_customer_by_code_of_other_store()
    {
        $employee = Employee::first();

        $customer = Store::where("id", "!=", $employee->store_id)
            ->first()
            ->customers->first();

        $response = $this->actingAs($employee)->get("/api/customer/code/{$customer->code}");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_customer_by_admin_successfully()
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

    public function test_update_customer_by_sale_staff_successfully()
    {
        $employee = Employee::first();

        $customer = $employee->store->customers->first();

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

    public function test_update_customer_unauthorized()
    {
        $response = $this->put("/api/customer/1", [
            "name" => "Test Customer Updated",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_customer_not_found()
    {
        $response = $this->actingAs(Employee::first())->put("/api/customer/99", [
            "name" => "Test Customer Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_customer_of_other_store()
    {
        $employee = Employee::first();

        $customer = Store::where("id", "!=", $employee->store_id)
            ->first()
            ->customers->first();

        $response = $this->actingAs($employee)->put("/api/customer/{$customer->id}", [
            "name" => "Test Customer Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_add_point_to_customer_by_admin_successfully()
    {
        $store = Store::first();

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

    public function test_add_point_to_customer_by_sale_staff_successfully()
    {
        $employee = Employee::first();

        $customer = $employee->store->customers->first();

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

    public function test_add_point_to_customer_unauthorized()
    {
        $response = $this->post("/api/customer/add-point/1", [
            "point" => 10,
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_add_point_to_customer_not_found()
    {
        $response = $this->actingAs(Employee::first())->post("/api/customer/add-point/99", [
            "point" => 10,
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_add_point_to_customer_of_other_store()
    {
        $employee = Employee::first();

        $customer = Store::where("id", "!=", $employee->store_id)
            ->first()
            ->customers->first();

        $response = $this->actingAs($employee)->post("/api/customer/add-point/{$customer->id}", [
            "point" => 10,
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_use_point_to_customer_by_admin_successfully()
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

    public function test_use_point_to_customer_by_sale_staff_successfully()
    {
        $employee = Employee::first();

        $customer = $employee->store->customers->first();

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

    public function test_use_point_to_customer_unauthorized()
    {
        $response = $this->post("/api/customer/use-point/1", [
            "point" => 10,
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_use_point_to_customer_not_found()
    {
        $response = $this->actingAs(Employee::first())->post("/api/customer/use-point/99", [
            "point" => 10,
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_use_point_to_customer_of_other_store()
    {
        $employee = Employee::first();

        $customer = Store::where("id", "!=", $employee->store_id)
            ->first()
            ->customers->first();

        $response = $this->actingAs($employee)->post("/api/customer/use-point/{$customer->id}", [
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
