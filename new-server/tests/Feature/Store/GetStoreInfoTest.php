<?php

namespace Tests\Feature\Store;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetStoreInfoTest extends TestCase
{
    use RefreshDatabase;


    public function test_get_store_info()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/store/me");

        $response->assertStatus(200);

        $response->assertJson([
            "name" => $store->name,
            "email" => $store->email,
        ]);
    }

    public function test_get_store_info_unauthenticated()
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
