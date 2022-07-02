<?php

namespace Tests\Feature\WorkSchedule;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetWorkSchedulesTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_get_work_schedules_unauthenticated()
    {
        $response = $this->get("/api/work-schedule");

        $response->assertStatus(401);
    }

    public function test_get_work_schedules_as_employee()
    {
        $store = Store::first();

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

    public function test_get_work_schedules_by_date()
    {
        $store = Store::first();

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
