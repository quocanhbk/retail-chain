<?php

namespace Tests\Feature\Branch;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateBranchTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_branch_unauthenticated()
    {
        $response = $this->post("/api/branch", [
            "name" => "Branch Name",
            "address" => "Branch Address",
        ]);

        $response->assertStatus(401);
    }

    public function test_create_branch_successfully()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/branch", [
            "name" => "Branch Name",
            "address" => "Branch Address",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("branches", [
            "name" => "Branch Name",
            "address" => "Branch Address",
            "image" => "images/default/branch.png",
            "image_key" => "default",
            "store_id" => $store->id,
        ]);

        $response->assertJson([
            "name" => "Branch Name",
            "address" => "Branch Address",
        ]);
    }

    public function test_create_branch_with_invalid_data()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/branch", [
            "name" => "Branch Name",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function test_create_branch_with_new_employees()
    {
        $store = Store::first();

        $roles = $store->roles;

        $response = $this->actingAs($store, "stores")->post("/api/branch", [
            "name" => "Branch Name",
            "address" => "Branch Address",
            "new_employees" => [
                [
                    "name" => "Employee Name",
                    "email" => "employee@email.com",
                    "role_ids" => [$roles->first()->id],
                ],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("employees", [
            "store_id" => $store->id,
            "name" => "Employee Name",
            "email" => "employee@email.com",
        ]);

        $employee_id = Employee::where(["store_id" => $store->id, "email" => "employee@email.com"])->first()->id;

        $this->assertDatabaseHas("employments", [
            "branch_id" => $response->json("id"),
            "employee_id" => $employee_id,
        ]);

        $employment_id = Employment::where("employee_id", $employee_id)->first()->id;

        $this->assertDatabaseHas("employment_roles", [
            "employment_id" => $employment_id,
            "role_id" => $roles->first()->id,
        ]);
    }

    public function test_create_branch_with_transfered_employees()
    {
        $store = Store::first();

        $selected_employee = Employee::first();

        $response = $this->actingAs($store, "stores")->post("/api/branch", [
            "name" => "Branch Name",
            "address" => "Branch Address",
            "transfered_employees" => [
                [
                    "id" => $selected_employee->id,
                    "role_ids" => [$store->roles->first()->id],
                ],
            ],
        ]);

        $employment_id = Employment::where("employee_id", $selected_employee->id)
            ->where("to", null)
            ->first()->id;

        $response->assertStatus(200);

        // old employment is terminated
        $this->assertDatabaseHas("employments", [
            "employee_id" => Employee::first()->id,
            "to" => date("Y/m/d"),
        ]);

        // new employment is created
        $this->assertDatabaseHas("employments", [
            "branch_id" => $response->json("id"),
            "employee_id" => Employee::first()->id,
            "from" => date("Y/m/d"),
            "to" => null,
        ]);

        $this->assertDatabaseHas("employment_roles", [
            "employment_id" => $employment_id,
            "role_id" => $store->roles->first()->id,
        ]);
    }
}
