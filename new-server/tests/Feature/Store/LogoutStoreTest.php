<?php

namespace Tests\Feature\Store;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_as_store()
    {
        $store = Store::first();

        $response = $this->actingAs($store, "stores")->post("/api/store/logout");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);
    }

    public function test_logout_as_guest()
    {
        $response = $this->post("/api/store/logout");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }
}
