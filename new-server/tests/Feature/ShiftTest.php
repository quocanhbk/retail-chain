<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_create_shift_unauthorized()
    {
        $response = $this->post("/api/shift", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(401);
    }

    public function test_create_shift_invalid()
    {
        $response = $this->actingAs(Employee::first())->post("/api/shift", [
            "name" => "Shift Name",
            "start_time" => "09:00",
        ]);

        $response->assertStatus(400);
    }

    public function test_create_shirt_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/shift", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("shifts", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
            "branch_id" => $employee->employment->branch_id,
        ]);
    }

    public function test_get_shifts_unauthorized()
    {
        $response = $this->get("/api/shift");

        $response->assertStatus(401);
    }

    public function test_get_shifts_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/shift");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "start_time", "end_time", "branch_id"]]);
    }

    public function test_get_shift_unauthorized()
    {
        $response = $this->get("/api/shift/1");

        $response->assertStatus(401);
    }

    public function test_get_shift_not_found()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/shift/999");

        $response->assertStatus(404);
    }

    public function test_get_shift_successfully()
    {
        $employee = Employee::first();

        $shift = $employee->employment->branch->shifts->first();

        $response = $this->actingAs($employee)->get("/api/shift/" . $shift->id);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "start_time", "end_time", "branch_id"]);
    }

    public function test_update_shift_unauthorized()
    {
        $response = $this->put("/api/shift/1", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(401);
    }

    public function test_update_shift_not_found()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->put("/api/shift/999", [
            "name" => "Shift Name",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(404);
    }

    public function test_update_shift_successfully()
    {
        $employee = Employee::first();

        $shift = $employee->employment->branch->shifts->first();

        $response = $this->actingAs($employee)->put("/api/shift/" . $shift->id, [
            "name" => "Shift Name Updated",
            "start_time" => "09:00",
            "end_time" => "18:00",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("shifts", [
            "name" => "Shift Name Updated",
            "start_time" => "09:00",
            "end_time" => "18:00",
            "branch_id" => $employee->employment->branch_id,
        ]);
    }

    public function test_delete_shift_unauthorized()
    {
        $response = $this->delete("/api/shift/1");

        $response->assertStatus(401);
    }

    public function test_delete_shift_not_found()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->delete("/api/shift/999");

        $response->assertStatus(404);
    }

    public function test_delete_shift_successfully()
    {
        $employee = Employee::first();

        $shift = $employee->employment->branch->shifts()->first();

        $response = $this->actingAs($employee)->delete("/api/shift/" . $shift->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted("shifts", [
            "id" => $shift->id,
        ]);
    }
}
