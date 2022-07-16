<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function testTransferEmployeeUnauthenticated()
    {
        $response = $this->post("/api/employee/transfer");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testTransferEmployeeAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee/transfer");

        $response->assertStatus(401);
    }

    public function testTransferEmployeeAsAdmin()
    {
        $store = Store::find(1);

        $employee = $store->employees->first();

        $old_branch = $employee->employment->branch;

        $branch = $store->branches->where("id", "!=", $old_branch->id)->first();

        $role = $store->roles->first();

        $response = $this->actingAs($store, "stores")->post("/api/employee/transfer", [
            "branch_id" => $branch->id,
            "employees" => [
                [
                    "id" => $employee->id,
                    "role_ids" => [$role->id],
                ],
            ],
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("employments", [
            "employee_id" => $employee->id,
            "branch_id" => $old_branch->id,
            "to" => date("Y/m/d"),
        ]);

        $this->assertDatabaseHas("employments", [
            "employee_id" => $employee->id,
            "branch_id" => $branch->id,
            "from" => date("Y/m/d"),
            "to" => null,
        ]);

        $this->assertDatabaseHas("employment_roles", [
            "employment_id" => $employee->fresh()->employment->id,
            "role_id" => $role->id,
        ]);
    }
}
