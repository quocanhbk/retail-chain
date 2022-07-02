<?php

namespace Tests\Feature\Branch;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetBranchImageTest extends TestCase
{

    public function test_get_branch_image_unauthenticated()
    {
        $branch = Store::first()->branches->first();

        $response = $this->get("/api/branch/image/{$branch->image_key}");

        $response->assertStatus(401);
    }

    public function test_get_branch_image_successfully()
    {
        $store = Store::first();

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->get("/api/branch/image/" . $branch->image_key);

        $response->assertStatus(200);

        $response->assertHeader("Content-Type", "image/*");
    }

    public function test_get_branch_image_not_found()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->get("/api/branch/image/invalid");

        $response->assertStatus(404);
    }
}
