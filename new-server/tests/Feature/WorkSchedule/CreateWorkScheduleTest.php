<?php

namespace Tests\Feature\WorkSchedule;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class CreateWorkScheduleTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testCreateWorkScheduleUnauthenticated()
    {
        $response = $this->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1, 2, 3],
            "date" => "2022-06-01",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateWorkScheduleWithInvalidPermission()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithoutPermission($store->id, "create-work-schedule");

        $response = $this->actingAs($employee)->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1, 2, 3],
            "date" => "2022-06-01",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateWorkScheduleWithValidPermission()
    {
        $store = Store::find(1);

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

    public function testCreateWorkScheduleWithInvalidDate()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-work-schedule");

        $response = $this->actingAs($employee)->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1, 2, 3],
            "date" => date("Y-m-d", strtotime("-1 day")),
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateWorkScheduleWithDuplicateEmployeeIds()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "create-work-schedule");

        $response = $this->actingAs($employee)->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1, 1],
            "date" => date("Y-m-d", strtotime("+1 day")),
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateWorkScheduleWithDuplicateSchedule()
    {
        $store = Store::find(1);

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
