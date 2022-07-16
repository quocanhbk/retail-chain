<?php

namespace Tests\Feature\WorkSchedule;

use App\Models\Store;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetWorkSchedulesTest extends TestCase
{
    use QueryEmployeeTrait;

    public function testGetWorkSchedulesUnauthenticated()
    {
        $response = $this->get("/api/work-schedule");

        $response->assertStatus(401);
    }

    public function testGetWorkSchedulesAsEmployee()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-work-schedule");

        $response = $this->actingAs($employee)->get("/api/work-schedule");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            [
                "id",
                "shift_id",
                "employee_id",
                "date",
                "created_at",
                "updated_at",
                "shift" => ["id", "name", "start_time", "end_time", "branch_id"],
                "employee" => ["id", "name", "email", "phone", "avatar", "avatar_key"],
            ],
        ]);
    }

    public function testGetWorkSchedulesByDate()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "view-work-schedule");

        $work_schedule = $employee->employment->branch->shifts->first()->workSchedules->first();

        $response = $this->actingAs($employee)->get("/api/work-schedule?date={$work_schedule->date}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            [
                "id",
                "shift_id",
                "employee_id",
                "date",
                "created_at",
                "updated_at",
                "shift" => ["id", "name", "start_time", "end_time", "branch_id"],
                "employee" => ["id", "name", "email", "phone", "avatar", "avatar_key"],
            ],
        ]);

        $response->assertJsonFragment(["date" => $work_schedule->date]);
    }
}
