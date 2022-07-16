<?php

namespace Tests\Feature\Shift;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class GetShiftsTest extends TestCase
{
    public function testGetShiftsUnauthenticated()
    {
        $response = $this->get("/api/shift");

        $response->assertStatus(401);
    }

    public function testGetShiftsAsEmployee()
    {
        $store = Store::find(1);

        $employee = Employee::where("store_id", $store->id)->first();

        $response = $this->actingAs($employee)->get("/api/shift");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "start_time", "end_time", "branch_id"]]);
    }

    public function testGetShiftsAsAdmin()
    {
        $store = Store::find(1);

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->get("/api/shift?branch_id={$branch->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "start_time", "end_time", "branch_id"]]);

        $response->assertJsonFragment(["branch_id" => $branch->id]);
    }

    public function testGetShiftsAsAdminWithInvalidBranchId()
    {
        $store = Store::find(1);

        $branch = Branch::where("store_id", "!=", $store->id)->first();

        $response = $this->actingAs($store, "stores")->get("/api/shift?branch_id={$branch->id}");

        $response->assertStatus(200);

        $response->assertJsonCount(0);
    }
}
