<?php

namespace Tests\Feature\Shift;

use App\Models\Shift;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\QueryEmployeeTrait;
use Tests\TestCase;

class DeleteShiftTest extends TestCase
{
    use RefreshDatabase;
    use QueryEmployeeTrait;

    public function testDeleteShiftUnauthenticated()
    {
        $shift = Shift::first();

        $response = $this->delete("/api/shift/{$shift->id}");

        $response->assertStatus(401);

        $this->assertDatabaseHas("shifts", [
            "id" => $shift->id,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteShiftWithInvalidPermission()
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

    public function testDeleteShiftWithValidPermission()
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

    public function testDeleteShiftAsAdmin()
    {
        $store = Store::find(1);

        $shift = $store->branches->first()->shifts->first();

        $response = $this->actingAs($store, "stores")->delete("/api/shift/{$shift->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing("shifts", [
            "id" => $shift->id,
        ]);

        $response->assertJsonStructure(["message"]);
    }

    public function testDeleteShiftNotFound()
    {
        $employee = $this->getEmployeeWithPermission(Store::first()->id, "delete-shift");

        $response = $this->actingAs($employee)->delete("/api/shift/999");

        $response->assertStatus(404);

        $response->assertJsonStructure(["message"]);
    }
}
