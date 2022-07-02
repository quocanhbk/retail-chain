<?php

namespace Tests\Feature\Shift;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UpdateShiftTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;


    public function test_update_shift_unauthenticated()
    {
        $response = $this->put("/api/shift/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_shift_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "update-shift");

        $shift = $store->branches->first()->shifts->first();

        $response = $this->actingAs($employee)->put("/api/shift/{$shift->id}");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_shift_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "update-shift");

        $shift = $store->branches->first()->shifts->first();

        $response = $this->actingAs($employee)->put("/api/shift/{$shift->id}", [
            "name" => "New Shift Name",
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            "name" => "New Shift Name",
            "start_time" => $shift->start_time,
            "end_time" => $shift->end_time,
            "branch_id" => $shift->branch_id,
        ]);

        $response->assertJsonStructure(["name", "start_time", "end_time", "branch_id"]);

        $this->assertDatabaseHas("shifts", [
            "name" => "New Shift Name",
            "start_time" => $shift->start_time,
            "end_time" => $shift->end_time,
            "branch_id" => $shift->branch_id,
        ]);
    }

    public function test_update_shift_as_admin()
    {
        $store = Store::first();

        $shift = $store->branches->first()->shifts->first();

        $response = $this->actingAs($store, "stores")->put("/api/shift/{$shift->id}", [
            "name" => "Shift Name Updated",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("shifts", [
            "name" => "Shift Name Updated",
            "start_time" => $shift->start_time,
            "end_time" => $shift->end_time,
            "branch_id" => $shift->branch_id,
        ]);

        $response->assertJson([
            "name" => "Shift Name Updated",
            "start_time" => $shift->start_time,
            "end_time" => $shift->end_time,
            "branch_id" => $shift->branch_id,
        ]);
    }

    public function test_update_shift_not_found()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "update-shift");

        $response = $this->actingAs($employee)->put("/api/shift/9999", [
            "name" => "Shift Name Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
