<?php

namespace Tests\Feature\Store;

use Tests\TestCase;

class LoginStoreTest extends TestCase
{
    public function testLoginWithValidInput()
    {
        $data = [
            "email" => "hexagon@gmail.com",
            "password" => "hexagon",
        ];

        $response = $this->post("/api/store/login", $data);

        $response->assertStatus(200);
    }

    public function testLoginWithWrongCredentials()
    {
        $data = [
            "email" => "hexagon@gmail.com",
            "password" => "123456",
        ];

        $response = $this->post("/api/store/login", $data);

        $response->assertStatus(401);
    }

    public function testLoginFailedWithInvalidInput()
    {
        $data = [
            "email" => "email",
            "password" => "password",
        ];

        $response = $this->post("/api/store/login", $data);

        $response->assertStatus(400);
    }
}
