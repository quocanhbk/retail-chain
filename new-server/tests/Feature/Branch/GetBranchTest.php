<?php

namespace Tests\Feature\Branch;

use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class GetBranchTest extends TestCase
{
    public function testGetBranchUnauthenticated()
    {
        $branch = Store::first()->branches->first();

        $response = $this->get("/api/branch/{$branch->id}");

        $response->assertStatus(401);
    }

    public function testGetBranchAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/branch/{$employee->employment->branch->id}");

        $response->assertStatus(401);
    }

    public function testGetBranchAsAdmin()
    {
        $store = Store::find(1);

        $branch = $store->branches->first();

        $response = $this->actingAs($store, "stores")->get("/api/branch/" . $branch->id);

        $response->assertStatus(200);

        $response->assertJson([
            "id" => $branch->id,
            "name" => $branch->name,
            "address" => $branch->address,
            "image_key" => $branch->image_key,
        ]);
    }

    public function testGetBranchNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/branch/999");

        $response->assertStatus(404);
    }
}
