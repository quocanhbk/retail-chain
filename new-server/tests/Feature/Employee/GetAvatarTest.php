<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class GetAvatarTest extends TestCase
{
    public function testGetAvatarUnauthenticated()
    {
        $response = $this->get("/api/employee/avatar/key");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetAvatarAsAdmin()
    {
        $store = Store::find(1);

        $employee = $store->employees->first();

        $response = $this->actingAs($store, "stores")->get("/api/employee/avatar/{$employee->avatar_key}");

        $response->assertStatus(200);

        $response->assertHeader("Content-Type", "image/*");
    }

    public function testGetAvatarAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/avatar/{$employee->avatar_key}");

        $response->assertStatus(200);

        $response->assertHeader("Content-Type", "image/*");
    }

    public function testGetAvatarNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/employee/avatar/invalid");

        $response->assertStatus(404);
    }
}
