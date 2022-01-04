<?php

namespace Tests\Feature\Store;

use Tests\TestCase;

class CreateStoreTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_register_successfully_with_valid_data()
    {
        $data = [
            'name' => 'Store Name',
            'email' => 'quocanhbk17@gmail.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ];
        $response = $this->post('/api/store/register', $data);
        $response->assertStatus(200);
    }
}
