<?php

namespace Tests\Feature\Shift;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class DeleteShiftTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function test_delete_shift_unauthenticated()
    {
        $shift = Shift::first();

        $response = $this->delete("/api/shift/{$shift->id}");

        $response->assertStatus(401);

        $this->assertDatabaseHas("shifts", [
            "id" => $shift->id,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_shift_with_invalid_permission()
    {
        $employee = $this->getEmployeeWithoutPermission(Store::first()->id, "delete-shift");

        $shift = $employee->employment->branch->shifts->first();

        $response = $this->actingAs($employee)->delete("/api/shift/{$shift->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas("shifts", [
            "id" => $shift->id,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_shift_with_valid_permission()
    {
        $employee = $this->getEmployeeWithPermission(Store::first()->id, "delete-shift");

        $shift = $employee->employment->branch->shifts->first();

        $response = $this->actingAs($employee)->delete("/api/shift/{$shift->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing("shifts", [
            "id" => $shift->id,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_shift_as_admin()
    {
        $store = Store::first();

        $shift = $store->branches->first()->shifts->first();

        $response = $this->actingAs($store, "stores")->delete("/api/shift/{$shift->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing("shifts", [
            "id" => $shift->id,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function test_delete_shift_not_found()
    {
        $employee = $this->getEmployeeWithPermission(Store::first()->id, "delete-shift");

        $response = $this->actingAs($employee)->delete("/api/shift/999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
