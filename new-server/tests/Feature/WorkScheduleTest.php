<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_create_work_schedule_unauthorized()
    {
        $response = $this->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1, 2, 3],
            "date" => "2022-06-01",
        ]);

        $response->assertStatus(401);
    }

    public function test_create_work_schedule_in_the_past()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1, 2, 3],
            "date" => "2020-06-01",
        ]);

        $response->assertStatus(400);
    }

    public function test_create_work_schedule_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/work-schedule", [
            "shift_id" => 1,
            "employee_ids" => [1],
            "date" => date("Y-m-d", strtotime("+1 day")),
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("work_schedules", [
            "shift_id" => 1,
            "employee_id" => 1,
            "date" => date("Y-m-d", strtotime("+1 day")),
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_work_schedules_unauthorized()
    {
        $response = $this->get("/api/work-schedule");

        $response->assertStatus(401);
    }

    public function test_get_work_schedules_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/work-schedule");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "shift_id", "employee_id", "date"]]);
    }

    public function test_get_work_schedules_by_date_unauthorized()
    {
        $response = $this->get("/api/work-schedule/2020-06-01");

        $response->assertStatus(401);
    }

    public function test_get_work_schedules_by_date_successfully()
    {
        $employee = Employee::first();

        $date = strtotime(WorkSchedule::first()->date);

        $response = $this->actingAs($employee)->get(
            "/api/work-schedule/" . date("Y-m-d", $date)
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "shift_id", "employee_id", "date"]]);

        $response->assertJson([[
            "date" => date("Y-m-d", $date)
        ]]);
    }

    public function test_update_work_schedule_unauthorized()
    {
        $response = $this->put("/api/work-schedule/1", [
            "shift_id" => 1,
            "note" => "Something happens"
        ]);

        $response->assertStatus(401);
    }

    public function test_update_work_schedule_not_found()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->put("/api/work-schedule/99", [
            "shift_id" => 1,
            "note" => "Something happens"
        ]);

        $response->assertStatus(400);
    }

    public function test_update_work_schedule_successfully()
    {
        $employee = Employee::first();

        $workSchedule = WorkSchedule::first();

        $response = $this->actingAs($employee)->put("/api/work-schedule/{$workSchedule->id}", [
            "note" => "Something happens"
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("work_schedules", [
            "id" => $workSchedule->id,
            "note" => "Something happens"
        ]);

        $response->assertJsonStructure(["id", "shift_id", "employee_id", "date", "note"]);

        $response->assertJson([
            "id" => $workSchedule->id,
            "note" => "Something happens",
        ]);
    }

    public function test_delete_work_schedule_not_found()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->delete("/api/work-schedule/99");

        $response->assertStatus(404);
    }

    public function test_delete_work_schedule_in_the_past()
    {
        $employee = Employee::first();

        $past_work_schedule = WorkSchedule::where("date", "<", date("Y-m-d"))->first();

        $response = $this->actingAs($employee)->delete("/api/work-schedule/" . $past_work_schedule->id);

        $response->assertStatus(400);
    }

    public function test_delete_work_schedule_successfully()
    {
        $employee = Employee::first();

        $future_work_schedule = WorkSchedule::where("date", ">", date("Y-m-d"))->first();

        $response = $this->actingAs($employee)->delete("/api/work-schedule/" . $future_work_schedule->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing("work_schedules", [
            "id" => $future_work_schedule->id,
        ]);

        $response->assertJsonStructure(["id", "shift_id", "employee_id", "date"]);

        $response->assertJson([
            "id" => $future_work_schedule->id,
            "shift_id" => $future_work_schedule->shift_id,
        ]);
    }
}
