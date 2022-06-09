<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_create_branch_failed_with_invalid_roles()
    {
        $response = $this->post("/api/branch", [
            "name" => "Branch Name",
            "address" => "Branch Address",
        ]);

        $response->assertStatus(401);
    }

    public function test_create_branch_successfully_with_valid_data()
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
            "store_id" => $store->id,
        ]);

        $response->assertJson([
            "name" => "Branch Name",
            "address" => "Branch Address",
        ]);
    }

    public function test_create_branch_failed_with_invalid_data()
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

        $response = $this->actingAs($store, "stores")->post("/api/branch", [
            "name" => "Branch Name",
            "address" => "Branch Address",
            "new_employees" => [
                [
                    "name" => "Employee Name",
                    "email" => "employee@email.com",
                    "password" => "123456",
                    "password_confirmation" => "123456",
                    "roles" => ["manage"],
                ],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("employees", [
            "store_id" => $store->id,
            "name" => "Employee Name",
            "email" => "employee@email.com",
        ]);

        $employee_id = Employee::where("email", "employee@email.com")->first()->id;

        $this->assertDatabaseHas("employments", [
            "employee_id" => $employee_id,
        ]);

        $employment_id = Employment::where("employee_id", $employee_id)->first()->id;

        $this->assertDatabaseHas("employment_roles", [
            "employment_id" => $employment_id,
            "role" => "manage",
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
                    "roles" => ["manage"],
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
            "employee_id" => Employee::first()->id,
            "from" => date("Y/m/d"),
            "to" => null,
        ]);

        $this->assertDatabaseHas("employment_roles", [
            "employment_id" => $employment_id,
            "role" => "manage",
        ]);
    }

    public function test_get_branch_image_successfully()
    {
        $store = Store::first();

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->get("/api/branch/image/" . $branch->image_key);

        $response->assertStatus(200);

        $response->assertHeader("Content-Type", "image/jpeg");
    }

    public function test_get_branch_image_failed()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/branch/image/invalid");

        $response->assertStatus(404);
    }

    public function test_update_branch_image_successfully()
    {
        $store = Store::first();
        $branch = $store->branches()->first();

        Storage::fake("local");

        $image = UploadedFile::fake()->image("image.jpg");

        $response = $this->actingAs($store, "stores")->put("/api/branch/" . $branch->id . "/image", [
            "image" => $image,
        ]);

        $branch = Branch::find($branch->id);

        $response->assertStatus(200);
        Storage::disk("local")->assertExists($branch->image);
    }

    public function test_update_branch_image_failed()
    {
        $store = Store::first();

        Storage::fake("local");

        $image = UploadedFile::fake()->image("image.jpg");

        $response = $this->actingAs($store, "stores")->put("/api/branch/99/image", [
            "image" => $image,
        ]);

        $response->assertStatus(400);
    }

    public function test_get_branches_successfully()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/branch");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "address", "image_key", "created_at", "updated_at"]]);
    }

    public function test_get_branches_with_search_successfully()
    {
        $store = Store::first();

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->get("/api/branch?search=" . $branch->name);

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "address", "image_key", "created_at", "updated_at"]]);
    }

    public function test_get_branches_with_search_failed()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/branch?search=gibberish");

        $response->assertStatus(200);

        $response->assertJsonStructure([]);
    }

    public function test_get_branch_successfully()
    {
        $store = Store::first();

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->get("/api/branch/" . $branch->id);

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $branch->id,
            "name" => $branch->name,
            "address" => $branch->address,
            "image_key" => $branch->image_key,
        ]);
    }

    public function test_get_branch_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/branch/999");

        $response->assertStatus(404);
    }

    public function test_get_branch_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/branch/1");

        $response->assertStatus(401);
    }

    public function test_update_branch_successfully()
    {
        $store = Store::first();

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->put("/api/branch/" . $branch->id, [
            "name" => "Branch Name Updated",
            "address" => "Branch Address Updated",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("branches", [
            "name" => "Branch Name Updated",
            "address" => "Branch Address Updated",
            "store_id" => $store->id,
        ]);

        $response->assertJson([
            "name" => "Branch Name Updated",
            "address" => "Branch Address Updated",
        ]);
    }

    public function test_delete_branch_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->delete("/api/branch/999");

        $response->assertStatus(404);
    }

    public function test_delete_branch_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->delete("/api/branch/1");

        $response->assertStatus(401);
    }

    public function test_delete_branch_with_active_employments()
    {
        $store = Store::first();

        $branch = $store
            ->branches()
            ->where("id", 1)
            ->first();

        $response = $this->actingAs($store, "stores")->delete("/api/branch/" . $branch->id);

        $response->assertStatus(400);
    }

    public function test_delete_branch_with_inactive_employments()
    {
        $store = Store::first();

        $branch = $store
            ->branches()
            ->where("id", 2)
            ->first();

        $response = $this->actingAs($store, "stores")->delete("/api/branch/" . $branch->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted("branches", [
            "id" => $branch->id,
        ]);
    }
}
