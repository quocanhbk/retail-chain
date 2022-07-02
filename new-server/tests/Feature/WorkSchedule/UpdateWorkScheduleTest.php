<?php

namespace Tests\Feature\WorkSchedule;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UpdateWorkScheduleTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_update_work_schedule_unauthenticated()
    {
        $response = $this->put("/api/work-schedule/1", [
            "note" => "Something",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_work_schedule_with_invalid_permission()
    {
        $store = Store::first();

        $work_schedule = $store->branches
            ->first()
            ->shifts->first()
            ->workSchedules->first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "update-work-schedule");

        $response = $this->actingAs($employee)->put("/api/work-schedule/{$work_schedule->shift_id}", [
            "note" => "Note Updated",
        ]);

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_work_schedule_with_valid_permission()
    {
        $store = Store::first();

        $work_schedule = $store->branches
            ->first()
            ->shifts->first()
            ->workSchedules->first();

        $employee = $this->getEmployeeWithPermission($store->id, "update-work-schedule");

        $response = $this->actingAs($employee)->put("/api/work-schedule/{$work_schedule->shift_id}", [
            "note" => "Note Updated",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "shift_id", "note", "employee_id", "date", "is_absent"]);

        $this->assertDatabaseHas("work_schedules", [
            "id" => $work_schedule->id,
            "note" => "Note Updated",
        ]);
    }

    public function test_update_work_schedule_not_found()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "update-work-schedule");

        $response = $this->actingAs($employee)->put("/api/work-schedule/9999", [
            "note" => "Note Updated",
        ]);

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
