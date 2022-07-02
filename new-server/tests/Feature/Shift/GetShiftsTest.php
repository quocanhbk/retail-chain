<?php

namespace Tests\Feature\Shift;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetShiftsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_shifts_unauthenticated()
    {
        $response = $this->get("/api/shift");

        $response->assertStatus(401);
    }

    public function test_get_shifts_as_employee()
    {
        $store = Store::first();

        $employee = Employee::where("store_id", $store->id)->first();

        $response = $this->actingAs($employee)->get("/api/shift");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "start_time", "end_time", "branch_id"]]);
    }

    public function test_get_shifts_as_admin()
    {
        $store = Store::first();

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->get("/api/shift?branch_id={$branch->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "start_time", "end_time", "branch_id"]]);

        $response->assertJsonFragment(["branch_id" => $branch->id]);
    }

    public function test_get_shifts_as_admin_with_invalid_branch_id()
    {
        $store = Store::first();

        $branch = Branch::where("store_id", "!=", $store->id)->first();

        $response = $this->actingAs($store, "stores")->get("/api/shift?branch_id={$branch->id}");

        $response->assertStatus(200);

        $response->assertJsonCount(0);
    }
}
