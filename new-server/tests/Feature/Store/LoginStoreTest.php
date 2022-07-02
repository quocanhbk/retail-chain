<?php

namespace Tests\Feature\Store;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_input()
    {
        $data = [
            "email" => "hexagon@gmail.com",
            "password" => "hexagon",
        ];

        $response = $this->post("/api/store/login", $data);

        $response->assertStatus(200);
    }

    public function test_login_with_wrong_credentials()
    {
        $data = [
            "email" => "hexagon@gmail.com",
            "password" => "123456",
        ];

        $response = $this->post("/api/store/login", $data);

        $response->assertStatus(401);
    }

    public function test_login_failed_with_invalid_input()
    {
        $data = [
            "email" => "email",
            "password" => "password",
        ];

        $response = $this->post("/api/store/login", $data);

        $response->assertStatus(400);
    }
}
