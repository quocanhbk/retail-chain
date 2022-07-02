<?php

namespace Tests\Feature\Store;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_with_invalid_input()
    {
        $input = [
            "name" => "Store Name",
        ];

        $response = $this->post("/api/store/register", $input);

        $response->assertStatus(400);
    }

    public function test_register_with_valid_input()
    {
        $data = [
            "name" => "Store Name",
            "email" => "quocanhbk17@gmail.com",
            "password" => "123456",
            "password_confirmation" => "123456",
        ];

        $response = $this->post("/api/store/register", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas("stores", [
            "name" => $data["name"],
            "email" => $data["email"],
        ]);

        $this->assertDatabaseHas("item_categories", [
            "store_id" => Store::where("email", $data["email"])->first()->id,
        ]);

        $response->assertJson([
            "name" => $data["name"],
            "email" => $data["email"],
        ]);
    }

    public function test_register_with_existed_email()
    {
        $existed_store = Store::first();

        $data = [
            "name" => "Store Name",
            "email" => $existed_store->email,
            "password" => "123456",
            "password_confirmation" => "123456",
        ];

        $response = $this->post("/api/store/register", $data);

        $response->assertStatus(400);

        $response->assertJsonStructure(["message"]);
    }
}
