<?php

namespace Tests\Feature\Branch;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetBranchTest extends TestCase
{
    public function test_get_branch_unauthenticated()
    {
        $branch = Store::first()->branches->first();

        $response = $this->get("/api/branch/{$branch->id}");

        $response->assertStatus(401);
    }

    public function test_get_branch_unauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/branch/1");

        $response->assertStatus(401);
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
}
