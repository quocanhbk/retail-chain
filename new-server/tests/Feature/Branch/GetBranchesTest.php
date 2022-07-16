<?php

namespace Tests\Feature\Branch;

use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class GetBranchesTest extends TestCase
{
    public function testGetBranchesUnauthenticated()
    {
        $response = $this->get("/api/branch");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetBranchesAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/branch");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetBranchesAsAdmin()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/branch");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "address", "image_key", "created_at", "updated_at"]]);
    }

    public function testGetBranchesWithSearch()
    {
        $store = Store::find(1);

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->get("/api/branch?search=" . $branch->name);

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "address", "image_key", "created_at", "updated_at"]]);

        $response->assertJsonFragment(["name" => $branch->name]);
    }

    public function testGetBranchesWithSearchEmpty()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/branch?search=gibberish");

        $response->assertStatus(200);

        $response->assertJsonStructure([]);
    }
}
