<?php

namespace Tests\Feature\WorkSchedule;

use App\Models\Store;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreateWorkScheduleTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_create_work_schedule_unauthenticated()
    {
        $response = $this->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1, 2, 3],
            "date" => "2022-06-01",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_work_schedule_with_invalid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-work-schedule");

        $response = $this->actingAs($employee)->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1, 2, 3],
            "date" => "2022-06-01",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_work_schedule_with_valid_permission()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-work-schedule");

        $response = $this->actingAs($employee)->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1],
            "date" => date("Y-m-d", strtotime("+99 day")),
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("work_schedules", [
            "shift_id" => 1,
            "employee_id" => 1,
            "date" => date("Y-m-d", strtotime("+1 day")),
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_work_schedule_with_invalid_date()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-work-schedule");

        $response = $this->actingAs($employee)->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1, 2, 3],
            "date" => date("Y-m-d", strtotime("-1 day")),
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_work_schedule_with_duplicate_employee_ids()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-work-schedule");

        $response = $this->actingAs($employee)->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1, 1],
            "date" => date("Y-m-d", strtotime("+1 day")),
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_work_schedule_with_duplicate_schedule()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "create-work-schedule");

        $work_schedule = $employee->employment->branch->shifts
            ->first()
            ->workSchedules->where("date", ">", date("Y-m-d"))
            ->first();

        $response = $this->actingAs($employee)->post("/api/work-schedule", [
            "shift_id" => $work_schedule->shift_id,
            "employee_ids" => [$work_schedule->employee_id],
            "date" => $work_schedule->date,
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
