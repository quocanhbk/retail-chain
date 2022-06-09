<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRole;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_create_employee_successfully()
    {
        $store = Store::first();

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->post("/api/employee", [
            "name" => "Employee Name",
            "email" => "employee@gmail.com",
            "password" => "123456",
            "password_confirmation" => "123456",
            "branch_id" => $branch->id,
            "roles" => ["purchase"],
        ]);

        $response->assertStatus(200);

        // should create new employee record
        $this->assertDatabaseHas("employees", [
            "name" => "Employee Name",
            "email" => "employee@gmail.com",
        ]);

        // should create new employment record
        $this->assertDatabaseHas("employments", [
            "branch_id" => $branch->id,
            "employee_id" => Employee::where("email", "employee@gmail.com")->first()->id,
            "to" => null,
        ]);

        $employment = Employment::where(
            "employee_id",
            Employee::where("email", "employee@gmail.com")->first()->id
        )->first();

        // should create new employment role record
        $this->assertDatabaseHas("employment_roles", [
            "employment_id" => $employment->id,
            "role" => "purchase",
        ]);

        $response->assertJson([
            "name" => "Employee Name",
            "email" => "employee@gmail.com",
        ]);
    }

    public function test_create_many_employees_successfully()
    {
        $store = Store::first();

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->post("/api/employee/many", [
            "employees" => [
                [
                    "name" => "Employee 1",
                    "email" => "employee1@gmail.com",
                    "password" => "123456",
                    "password_confirmation" => "123456",
                    "branch_id" => $branch->id,
                    "roles" => ["purchase"],
                ],
                [
                    "name" => "Employee 2",
                    "email" => "employee2@gmail.com",
                    "password" => "123456",
                    "password_confirmation" => "123456",
                    "branch_id" => $branch->id,
                    "roles" => ["manage"],
                ],
            ],
        ]);

        $response->assertStatus(200);

        // should create new employee record
        $this->assertDatabaseHas("employees", [
            "name" => "Employee 1",
            "email" => "employee1@gmail.com",
        ])->assertDatabaseHas("employees", [
            "name" => "Employee 2",
            "email" => "employee2@gmail.com",
        ]);

        // should create new employment record
        $this->assertDatabaseHas("employments", [
            "branch_id" => $branch->id,
            "employee_id" => Employee::where("email", "employee1@gmail.com")->first()->id,
            "to" => null,
        ])->assertDatabaseHas("employments", [
            "branch_id" => $branch->id,
            "employee_id" => Employee::where("email", "employee2@gmail.com")->first()->id,
            "to" => null,
        ]);

        $employment1 = Employment::where(
            "employee_id",
            Employee::where("email", "employee1@gmail.com")->first()->id
        )->first();
        $employment2 = Employment::where(
            "employee_id",
            Employee::where("email", "employee2@gmail.com")->first()->id
        )->first();

        // should create new employment role record
        $this->assertDatabaseHas("employment_roles", [
            "employment_id" => $employment1->id,
            "role" => "purchase",
        ])->assertDatabaseHas("employment_roles", [
            "employment_id" => $employment2->id,
            "role" => "manage",
        ]);
    }

    public function test_update_employee_avatar_successfully()
    {
        $store = Store::first();

        $employee = $store->employees()->first();

        Storage::fake("local");

        $avatar = UploadedFile::fake()->image("avatar.jpg");

        $response = $this->actingAs($store, "stores")->put("/api/employee/" . $employee->id . "/avatar", [
            "avatar" => $avatar,
        ]);

        $response->assertStatus(200);

        $employee = Employee::find($employee->id);

        Storage::disk("local")->assertExists($employee->avatar);
    }

    public function test_update_employee_successfully()
    {
        $store = Store::first();

        $employee = $store->employees()->first();

        $response = $this->actingAs($store, "stores")->put("/api/employee/" . $employee->id, [
            "name" => "Employee Name Updated",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("employees", [
            "name" => "Employee Name Updated",
            "email" => $employee->email,
        ]);
    }

    public function test_update_employee_change_roles_successfully()
    {
        $store = Store::first();

        $employee = $store->employees()->first();

        $current_role_ids = $employee
            ->employment()
            ->first()
            ->roles()
            ->pluck("id")
            ->toArray();

        $response = $this->actingAs($store, "stores")->put("/api/employee/" . $employee->id, [
            "roles" => ["purchase", "manage"],
        ]);

        $response->assertStatus(200);

        foreach ($current_role_ids as $id) {
            $this->assertDatabaseMissing("employment_roles", [
                "id" => $id,
            ]);
        }

        $this->assertDatabaseHas("employment_roles", [
            "employment_id" => Employment::where("employee_id", $employee->id)->first()->id,
            "role" => "purchase",
        ])->assertDatabaseHas("employment_roles", [
            "employment_id" => Employment::where("employee_id", $employee->id)->first()->id,
            "role" => "manage",
        ]);
    }

    public function test_update_employee_change_branch_successfully()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        $current_branch_id = $employee->employment->branch_id;

        $other_branch = $store->branches->where("id", "!=", $current_branch_id)->first();

        $current_roles = $employee->employment->roles->pluck("role")->toArray();

        $response = $this->actingAs($store, "stores")->put("/api/employee/" . $employee->id, [
            "branch_id" => $other_branch->id,
        ]);

        $response->assertStatus(200);

        // should have new employment
        $this->assertDatabaseHas("employments", [
            "branch_id" => $other_branch->id,
            "employee_id" => $employee->id,
            "to" => null,
        ]);

        // should terminate old employment
        $this->assertDatabaseHas("employments", [
            "branch_id" => $current_branch_id,
            "employee_id" => $employee->id,
            "to" => date("Y/m/d"),
        ]);

        $new_employment = Employment::where("employee_id", $employee->id)
            ->where("branch_id", $other_branch->id)
            ->first();

        // should create new employment roles
        foreach ($current_roles as $role) {
            $this->assertDatabaseHas("employment_roles", [
                "employment_id" => $new_employment->id,
                "role" => $role,
            ]);
        }
    }

    public function test_update_employee_change_branch_and_roles_successfully()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        $current_branch_id = $employee->employment->branch_id;

        $other_branch = $store->branches->where("id", "!=", $current_branch_id)->first();

        $response = $this->actingAs($store, "stores")->put("/api/employee/" . $employee->id, [
            "branch_id" => $other_branch->id,
            "roles" => ["purchase", "manage"],
        ]);

        $response->assertStatus(200);

        // should have new employment
        $this->assertDatabaseHas("employments", [
            "branch_id" => $other_branch->id,
            "employee_id" => $employee->id,
            "to" => null,
        ]);

        // should terminate old employment
        $this->assertDatabaseHas("employments", [
            "branch_id" => $current_branch_id,
            "employee_id" => $employee->id,
            "to" => date("Y/m/d"),
        ]);

        $new_employment = Employment::where("employee_id", $employee->id)
            ->where("branch_id", $other_branch->id)
            ->first();

        // should create new employment roles
        foreach (["purchase", "manage"] as $role) {
            $this->assertDatabaseHas("employment_roles", [
                "employment_id" => $new_employment->id,
                "role" => $role,
            ]);
        }
    }

    public function test_get_employee_avatar_successfully()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        $avatar_extension = pathinfo($employee->avatar, PATHINFO_EXTENSION);

        $response = $this->actingAs($store, "stores")->get("/api/employee/avatar/" . $employee->avatar_key);

        $response->assertStatus(200);

        $response->assertHeader("Content-Type", "image/" . $avatar_extension);
    }

    public function test_get_employee_avatar_not_found()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/avatar/abc");

        $response->assertStatus(404);
    }

    public function test_get_employee_avatar_unauthorized()
    {
        $response = $this->get("/api/employee/avatar/abc");

        $response->assertStatus(401);
    }

    public function test_get_employees_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee");

        $response->assertStatus(401);
    }

    public function test_get_employees_successfully()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/employee");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            [
                "id",
                "name",
                "email",
                "phone",
                "avatar",
                "avatar_key",
                "employment" => ["id", "branch_id", "to", "roles" => [["id", "role"]]],
            ],
        ]);
    }

    public function test_get_employee_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/employee/123");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_employy_unauthorized()
    {
        $response = $this->get("/api/employee/123");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_employee_successfully()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        $response = $this->actingAs($store, "stores")->get("/api/employee/" . $employee->id);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "name",
            "email",
            "phone",
            "avatar",
            "avatar_key",
            "employment" => ["id", "branch_id", "to", "roles" => [["id", "role"]]],
        ]);
    }

    public function test_get_employee_by_branch_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/branch/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_get_employee_by_branch()
    {
        $store = Store::first();

        $branch = $store->branches->first();

        $response = $this->actingAs($store, "stores")->get("/api/employee/branch/" . $branch->id);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            [
                "id",
                "name",
                "email",
                "phone",
                "avatar",
                "avatar_key",
                "employment" => ["id", "branch_id", "to", "roles" => [["id", "role"]]],
            ],
        ]);

        $response->assertJson([
            [
                "employment" => [
                    "branch_id" => $branch->id,
                ],
            ],
        ]);
    }

    public function test_login_employee_failed()
    {
        $response = $this->post("/api/employee/login", [
            "email" => "hexagon@gmail.com",
            "password" => "123456",
        ]);

        $response->assertStatus(401);
    }

    public function test_login_employee_successfully()
    {
        $employee = Employee::first();

        $response = $this->post("/api/employee/login", [
            "email" => $employee->email,
            "password" => "123456",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            "id",
            "name",
            "email",
            "phone",
            "avatar",
            "avatar_key",
            "employment" => ["id", "branch_id", "to", "roles" => [["id", "role"]]],
        ]);

        $response->assertJson([
            "email" => $employee->email,
        ]);
    }

    public function test_logout_employee_unauthorized()
    {
        $response = $this->post("/api/employee/logout");

        $response->assertStatus(401);
    }

    public function test_logout_employee_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee/logout");

        $response->assertStatus(200);
        $response->assertJsonStructure(["message"]);
    }

    public function test_get_employee_info_unauthorized()
    {
        $response = $this->get("/api/employee/me");

        $response->assertStatus(401);
    }

    public function test_get_employee_info_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/employee/me");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "id",
            "name",
            "email",
            "phone",
            "avatar",
            "avatar_key",
            "employment" => ["id", "branch_id", "to", "roles" => [["id", "role"]]],
        ]);
        $response->assertJson([
            "email" => $employee->email,
        ]);
    }

    public function test_transfer_employee_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee/transfer");

        $response->assertStatus(401);
    }

    public function test_transfer_employee_successfully()
    {
        $store = Store::first();

        $employee = Employee::first();

        $other_branch = $store->branches->where("id", "!=", $employee->employment->branch_id)->first();

        $response = $this->actingAs($store, "stores")->post("/api/employee/transfer", [
            "branch_id" => $other_branch->id,
            "employee_id" => $employee->id,
            "roles" => ["manage"],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(["message"]);

        // terminated old employment
        $this->assertDatabaseHas("employments", [
            "id" => $employee->employment->id,
            "to" => date("Y/m/d"),
        ]);

        // new employment
        $this->assertDatabaseHas("employments", [
            "employee_id" => $employee->id,
            "branch_id" => $other_branch->id,
            "from" => date("Y/m/d"),
            "to" => null,
        ]);

        $new_employment = Employment::where("employee_id", $employee->id)
            ->where("branch_id", $other_branch->id)
            ->first();

        // new roles
        $this->assertDatabaseHas("employment_roles", [
            "role" => "manage",
            "employment_id" => $new_employment->id,
        ]);
    }

    public function test_transfer_many_employees_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee/transfer/many");

        $response->assertStatus(401);
    }

    public function test_transfer_many_employees_successfully()
    {
        $store = Store::first();

        $employee = Employee::first();

        $other_branch = $store->branches->where("id", "!=", $employee->employment->branch_id)->first();

        $response = $this->actingAs($store, "stores")->post("/api/employee/transfer/many", [
            "branch_id" => $other_branch->id,
            "employees" => [
                [
                    "id" => $employee->id,
                    "roles" => ["manage", "sale"],
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(["message"]);

        // terminated old employment
        $this->assertDatabaseHas("employments", [
            "id" => $employee->employment->id,
            "to" => date("Y/m/d"),
        ]);

        // new employment
        $this->assertDatabaseHas("employments", [
            "employee_id" => $employee->id,
            "branch_id" => $other_branch->id,
            "from" => date("Y/m/d"),
            "to" => null,
        ]);

        $new_employment = Employment::where("employee_id", $employee->id)
            ->where("branch_id", $other_branch->id)
            ->first();

        // new roles
        foreach (["manage", "sale"] as $role) {
            $this->assertDatabaseHas("employment_roles", [
                "role" => $role,
                "employment_id" => $new_employment->id,
            ]);
        }
    }

    public function test_delete_employee_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->delete("/api/employee/{$employee->id}");

        $response->assertStatus(401);
    }

    public function test_delete_employee_successfully()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee, "stores")->delete("/api/employee/{$employee->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertSoftDeleted("employees", [
            "id" => $employee->id,
        ]);

        $this->assertDatabaseHas("employments", [
            "id" => $employee->employment->id,
            "to" => date("Y/m/d"),
        ]);
    }
}
