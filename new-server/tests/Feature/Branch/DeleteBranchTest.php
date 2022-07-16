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

    public function testDeleteBranchUnauthenticated()
    {
        $branch = Store::first()->branches->first();

        $response = $this->delete("/api/branch/{$branch->id}");

        $response->assertStatus(401);
    }

    public function testDeleteBranchNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->delete("/api/branch/999");

        $response->assertStatus(404);
    }

    public function testDeleteBranchUnauthorized()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->delete("/api/branch/1");

        $response->assertStatus(401);
    }

    public function testDeleteBranchWithActiveEmployments()
    {
        $store = Store::find(1);

        $branch = $store
            ->branches()
            ->whereRelation("employments", "to", null)
            ->first();

        $response = $this->actingAs($store, "stores")->delete("/api/branch/" . $branch->id);

        $response->assertStatus(400);
    }

    public function testDeleteBranchWithInactiveEmployments()
    {
        $store = Store::find(1);

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
