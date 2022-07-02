<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class UpdateEmployeeTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_update_employee_authenticated()
    {
        $response = $this->put("/api/employee/1");

        $response->assertStatus(401);
    }

    public function test_update_employee_by_admin()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        $response = $this->actingAs($store, "stores")->put("/api/employee/{$employee->id}", [
            "name" => "Updated Name",
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("employees", [
            "id" => $employee->id,
            "name" => "Updated Name",
        ]);
    }

    public function test_update_employee_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->put("/api/employee/1");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function test_update_employee_roles()
    {
        $store = Store::first();

        $roles = $store->roles;

        $employee = $store->employees->first();

        $old_employment = $employee->employment;

        $new_role = $roles->whereNotIn("id", $employee->employment->roles->pluck("role_id"))->first();

        $response = $this->actingAs($store, "stores")->put("/api/employee/{$employee->id}", [
            "role_ids" => [$new_role->id],
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $this->assertDatabaseHas("employments", [
            "employee_id" => $employee->id,
            "from" => $old_employment->from,
            "to" => date("Y/m/d"),
        ]);

        $this->assertDatabaseHas("employments", [
            "employee_id" => $employee->id,
            "from" => date("Y/m/d"),
            "to" => null,
        ]);

        $new_employment = Store::first()->employees->first()->employment;

        $this->assertDatabaseHas("employment_roles", [
            "employment_id" => $new_employment->id,
            "role_id" => $new_role->id,
        ]);
    }

    public function test_update_employee_avatar()
    {
        $store = Store::first();

        $employee = $store->employees->first();

        Storage::fake("local");

        $avatar = UploadedFile::fake()->image("test_image.jpg");

        $response = $this->actingAs($store, "stores")->put("/api/employee/{$employee->id}", [
            "avatar" => $avatar,
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);

        $employee = Store::first()
            ->employees->where("id", $employee->id)
            ->first();

        $this->assertTrue(Str::startsWith($employee->avatar, "images/{$store->id}/employees/"));

        Storage::disk("local")->assertExists($employee->avatar);
    }

    public function test_udpate_employee_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->put("/api/employee/9999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
