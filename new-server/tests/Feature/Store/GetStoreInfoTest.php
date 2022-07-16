<?php

namespace Tests\Feature\Store;

use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class GetStoreInfoTest extends TestCase
{
    public function testGetStoreInfo()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/store/me");

        $response->assertStatus(200);

        $response->assertJson([
            "name" => $store->name,
            "email" => $store->email,
        ]);
    }

    public function testGetStoreInfoUnauthenticated()
    {
        $response = $this->get("/api/store/me");

        $response->assertStatus(401);
    }

    public function testGetGuardAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/guard");

        $response->assertStatus(200);

        $response->assertJson([
            "guard" => "store",
        ]);
    }

    public function testGetGuardAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee, "employees")->get("/api/guard");

        $response->assertStatus(200);

        $response->assertJson([
            "guard" => "employee",
        ]);
    }

    public function testGetGuardAsGuest()
    {
        $response = $this->get("/api/guard");

        $response->assertStatus(200);

        $response->assertJson([
            "guard" => "guest",
        ]);
    }
}
