<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAvatarTest extends TestCase
{
    public function test_get_avatar_unauthenticated()
    {
        $response = $this->get("/api/employee/avatar/key");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_avatar_as_admin()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        $response = $this->actingAs($store, "stores")->get("/api/employee/avatar/{$employee->avatar_key}");

        $response->assertStatus(200);

        $response->assertHeader("Content-Type", "image/*");
    }

    public function test_get_avatar_as_employee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/avatar/{$employee->avatar_key}");

        $response->assertStatus(200);

        $response->assertHeader("Content-Type", "image/*");
    }

    public function test_get_avatar_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/employee/avatar/invalid");

        $response->assertStatus(404);
    }
}
