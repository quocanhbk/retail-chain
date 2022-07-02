<?php

namespace Tests\Feature\Branch;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetBranchesTest extends TestCase
{
    public function test_get_branches_unauthenticated()
    {
        $response = $this->get("/api/branch");

        $response->assertStatus(401);
    }

    public function test_get_branches_successfully()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/branch");

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "address", "image_key", "created_at", "updated_at"]]);
    }

    public function test_get_branches_with_search_successfully()
    {
        $store = Store::first();

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->get("/api/branch?search=" . $branch->name);

        $response->assertStatus(200);

        $response->assertJsonStructure([["id", "name", "address", "image_key", "created_at", "updated_at"]]);
    }

    public function test_get_branches_with_search_empty()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/branch?search=gibberish");

        $response->assertStatus(200);

        $response->assertJsonStructure([]);
    }
}
