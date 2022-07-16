<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CreateEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateEmployeeAuthenticated()
    {
        $response = $this->post("/api/employee");

        $response->assertStatus(401);
    }

    public function testCreateEmployeeAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->post("/api/employee");

        $response->assertStatus(401);
    }

    public function testCreateEmployeeWithInvalidInput()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/employee", [
            "name" => "My Employee",
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateEmployeeAsAdmin()
    {
        $store = Store::find(1);

        $roles = $store->roles;

        $response = $this->actingAs($store, "stores")->post("/api/employee", [
            "name" => "My Employee",
            "email" => "employee@gmail.com",
            "branch_id" => $store->branches->first()->id,
            "role_ids" => [$roles->first()->id],
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(["id", "name", "email"]);

        $response->assertJson([
            "name" => "My Employee",
            "email" => "employee@gmail.com",
        ]);

        $this->assertDatabaseHas("employees", [
            "name" => "My Employee",
            "email" => "employee@gmail.com",
        ]);

        $this->assertDatabaseHas("employments", [
            "employee_id" => $response->json("id"),
            "branch_id" => $store->branches->first()->id,
            "from" => date("Y/m/d"),
            "to" => null,
        ]);

        $this->assertDatabaseHas("employment_roles", [
            "employment_id" => Employment::where(["employee_id" => $response->json("id"), "to" => null])->first()->id,
            "role_id" => $roles->first()->id,
        ]);
    }

    public function testCreateEmployeeWithDuplicateEmail()
    {
        $store = Store::find(1);

        $roles = $store->roles;

        $response = $this->actingAs($store, "stores")->post("/api/employee", [
            "name" => "My Employee",
            "email" => $store->employees()->first()->email,
            "branch_id" => $store->branches->first()->id,
            "role_ids" => [$roles->first()->id],
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }

    public function testCreateEmployeeWithAvatar()
    {
        $store = Store::find(1);

        $roles = $store->roles;

        Storage::fake("local");

        $image = UploadedFile::fake()->image("test_image.jpg");

        $response = $this->actingAs($store, "stores")->post("/api/employee", [
            "name" => "My Employee",
            "email" => "employee@gmail.com",
            "avatar" => $image,
            "branch_id" => $store->branches->first()->id,
            "role_ids" => [$roles->first()->id],
        ]);

        $response->assertStatus(200);

        $this->assertTrue(Str::startsWith($response->json("avatar"), "images/{$store->id}/employees/"));

        Storage::disk("local")->assertExists($response->json("avatar"));
    }

    public function testCreateEmployeeWithInvalidBirthday()
    {
        $store = Store::find(1);

        $roles = $store->roles;

        $response = $this->actingAs($store, "stores")->post("/api/employee", [
            "name" => "My Employee",
            "email" => "employee@gmail.com",
            "birthday" => "2022/06/01",
            "branch_id" => $store->branches->first()->id,
            "role_ids" => [$roles->first()->id],
        ]);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
