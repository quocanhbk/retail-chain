<?php

namespace Tests\Feature\Shift;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreateShiftTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_create_shift_unauthenticated()
    {
        $response = $this->post("/api/shift", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(401);
    }

    public function test_create_shift_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-shift");

        $response = $this->actingAs($employee)->post("/api/shift", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_shift_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-shift");

        $response = $this->actingAs($employee)->post("/api/shift", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $this->assertDatabaseHas("shifts", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);
    }

    public function test_create_shift_as_admin()
    {
        $store = Store::first();

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->post("/api/shift", [
            "branch_id" => $branch->id,
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("shifts", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
            "branch_id" => $branch->id,
        ]);
    }

    public function test_create_shift_with_invalid_input()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-shift");

        $response = $this->actingAs($employee)->post("/api/shift", [
            "name" => "Shift Name",
            "start_time" => "09:00",
        ]);

        $response->assertStatus(400);
    }
}
