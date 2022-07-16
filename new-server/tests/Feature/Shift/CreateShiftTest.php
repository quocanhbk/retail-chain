<?php

namespace Tests\Feature\Shift;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreateShiftTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testCreateShiftUnauthenticated()
    {
        $response = $this->post("/api/shift", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(401);
    }

    public function testCreateShiftWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-shift");

        $response = $this->actingAs($employee)->post("/api/shift", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateShiftWithValidPermission()
    {
        $store = Store::find(1);

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

    public function testCreateShiftAsAdmin()
    {
        $store = Store::find(1);

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

    public function testCreateShiftWithInvalidInput()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-shift");

        $response = $this->actingAs($employee)->post("/api/shift", [
            "name" => "Shift Name",
            "start_time" => "09:00",
        ]);

        $response->assertStatus(400);
    }
}
