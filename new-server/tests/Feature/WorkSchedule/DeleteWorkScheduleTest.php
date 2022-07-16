<?php

namespace Tests\Feature\WorkSchedule;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class DeleteWorkScheduleTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testDeleteWorkScheduleUnauthenticated()
    {
        $response = $this->delete("/api/work-schedule/1");

        $response->assertStatus(401);
    }

    public function testDeleteWorkScheduleWithInvalidPermission()
    {
        $store = Store::find(1);

        $work_schedule = $store->branches
            ->first()
            ->shifts->first()
            ->workSchedules->first();

        $employee = $this->getEmployeeWithoutPermission($store->id, "delete-work-schedule");

        $response = $this->actingAs($employee)->delete("/api/work-schedule/{$work_schedule->shift_id}");

        $response->assertStatus(403);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteWorkScheduleWithValidPermission()
    {
        $store = Store::find(1);

        $work_schedule = $store->branches
            ->first()
            ->shifts->first()
            ->workSchedules->first();

        $employee = $this->getEmployeeWithPermission($store->id, "delete-work-schedule");

        $response = $this->actingAs($employee)->delete("/api/work-schedule/{$work_schedule->shift_id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseMissing("work_schedules", [
            "shift_id" => $work_schedule->shift_id,
            "employee_id" => $work_schedule->employee_id,
            "date" => $work_schedule->date,
        ]);
    }

    public function testDeleteWorkScheduleNotFound()
    {
        $store = Store::find(1);

        $employee = $this->getEmployeeWithPermission($store->id, "delete-work-schedule");

        $response = $this->actingAs($employee)->delete("/api/work-schedule/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
