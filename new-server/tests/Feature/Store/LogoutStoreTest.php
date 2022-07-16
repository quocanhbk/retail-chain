<?php

namespace Tests\Feature\Store;

use App\Models\Store;
use Tests\TestCase;

class LogoutStoreTest extends TestCase
{
    public function testLogoutAsStore()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->post("/api/store/logout");

        $response->assertStatus(200);

        $response->assertJsonStructure(["message"]);
    }

    public function testLogoutAsGuest()
    {
        $response = $this->post("/api/store/logout");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }
}
