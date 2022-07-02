<?php

namespace Tests\Feature\Branch;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteBranchTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_branch_unauthenticated()
    {
        $branch = Store::first()->branches->first();

        $response = $this->delete("/api/branch/{$branch->id}");

        $response->assertStatus(401);
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
            ->whereHas("employments", function ($query) {
                $query->where("to", null);
            })
            ->first();

        $response = $this->actingAs($store, "stores")->delete("/api/branch/" . $branch->id);

        $response->assertStatus(400);
    }

    public function test_delete_branch_with_inactive_employments()
    {
        $store = Store::first();

        $branch = $store->branches->first();

        Employment::where("branch_id", $branch->id)->update(["to" => now()]);

        $response = $this->actingAs($store, "stores")->delete("/api/branch/" . $branch->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted("branches", [
            "id" => $branch->id,
        ]);

        $response->assertJsonStructure(["message"]);
    }
}
