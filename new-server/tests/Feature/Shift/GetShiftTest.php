<?php

namespace Tests\Feature\Shift;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class GetShiftTest extends TestCase
{
    use RefreshDatabase;

    use QueryEmployeeTrait;


    public function test_get_shift_unauthenticated()
    {
        $response = $this->get("/api/shift/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_shift_as_employee()
    {
        $store = Store::first();

        $employee = $this->getEmployeeWithPermission($store->id, "view-shift");

        $shift = $employee->employment->branch->shifts->first();

        $response = $this->actingAs($employee)->get("/api/shift/{$shift->id}");

        $response->assertStatus(200);

        $response->assertJson([
            "name" => $shift->name,
            "start_time" => $shift->start_time,
            "end_time" => $shift->end_time,
            "branch_id" => $shift->branch_id,
        ]);

        $response->assertJsonStructure(["id", "branch_id", "name", "start_time", "end_time", "work_schedules"]);
    }

    public function test_get_shift_as_admin()
    {
        $store = Store::first();

        $shift = $store->branches->first()->shifts->first();

        $response = $this->actingAs($store, "stores")->get("/api/shift/{$shift->id}");

        $response->assertStatus(200);

        $response->assertJson([
            "name" => $shift->name,
            "start_time" => $shift->start_time,
            "end_time" => $shift->end_time,
            "branch_id" => $shift->branch_id,
        ]);

        $response->assertJsonStructure(["id", "branch_id", "name", "start_time", "end_time", "work_schedules"]);
    }

    public function test_get_shift_not_found()
    {
        $response = $this->actingAs(Employee::first())->get("/api/shift/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
