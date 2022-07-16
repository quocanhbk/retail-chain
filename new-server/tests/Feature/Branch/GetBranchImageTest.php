<?php

namespace Tests\Feature\Branch;

use App\Models\Employee;
use App\Models\Store;
use Tests\TestCase;

class GetBranchImageTest extends TestCase
{
    public function testGetBranchImageUnauthenticated()
    {
        $branch = Store::first()->branches->first();

        $response = $this->get("/api/branch/image/{$branch->image_key}");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetBranchImageAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->get("/api/branch/image/{$employee->employment->branch->image_key}");

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testGetBranchImageAsAdmin()
    {
        $store = Store::find(1);

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->get("/api/branch/image/" . $branch->image_key);

        $response->assertStatus(200);

        $response->assertHeader("Content-Type", "image/*");
    }

    public function testGetBranchImageNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->get("/api/branch/image/invalid");

        $response->assertStatus(404);
    }
}
