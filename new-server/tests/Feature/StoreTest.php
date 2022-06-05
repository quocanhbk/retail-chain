<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_register_failed_with_invalid_input()
    {
        $input = [
            "name" => "Store Name",
        ];

        $response = $this->post("/api/store/register", $input);

        $response->assertStatus(400);
    }

    public function test_register_successfully_with_valid_data()
    {
        $data = [
            "name" => "Store Name",
            "email" => "quocanhbk17@gmail.com",
            "password" => "123456",
            "password_confirmation" => "123456",
        ];

        $response = $this->post("/api/store/register", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas("stores", [
            "name" => $data["name"],
            "email" => $data["email"],
        ]);

        $this->assertDatabaseHas("item_categories", [
            "store_id" => Store::where("email", $data["email"])->first()->id,
        ]);
    }

    public function test_login_successfully_with_valid_data()
    {
        $data = [
            "email" => "hexagon@gmail.com",
            "password" => "hexagon",
        ];

        $response = $this->post("/api/store/login", $data);

        $response->assertStatus(200);
    }

    public function test_login_failed_with_wrong_credentials()
    {
        $data = [
            "email" => "hexagon@gmail.com",
            "password" => "123456",
        ];

        $response = $this->post("/api/store/login", $data);

        $response->assertStatus(401);
    }

    public function test_login_failed_with_invalid_input()
    {
        $data = [
            "email" => "email",
            "password" => "password",
        ];

        $response = $this->post("/api/store/login", $data);

        $response->assertStatus(400);
    }

    public function test_logout_successfully()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/store/logout");

        $response->assertStatus(200);
    }

    public function test_logout_failed()
    {
        $response = $this->post("/api/store/logout");

        $response->assertStatus(401);
    }

    public function test_get_store_info_successfully()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/store/me");

        $response->assertStatus(200);

        $response->assertJson([
            "name" => $store->name,
            "email" => $store->email,
        ]);
    }

    public function test_get_store_info_failed()
    {
        $response = $this->get("/api/store/me");

        $response->assertStatus(401);
    }

    public function test_get_guard_as_admin()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/guard");

        $response->assertStatus(200);

        $response->assertJson([
            "guard" => "store",
        ]);
    }

    public function test_get_guard_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee, "employees")->get("/api/guard");

        $response->assertStatus(200);

        $response->assertJson([
            "guard" => "employee",
        ]);
    }

    public function test_get_guard_as_guest()
    {
        $response = $this->get("/api/guard");

        $response->assertStatus(200);

        $response->assertJson([
            "guard" => "guest",
        ]);
    }
}
