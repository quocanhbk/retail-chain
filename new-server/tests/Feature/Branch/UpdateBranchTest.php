<?php

namespace Tests\Feature\Branch;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateBranchTest extends TestCase
{
    use RefreshDatabase;

    public function testUpdateBranchUnauthenticated()
    {
        $branch = Branch::first();

        $response = $this->put("/api/branch/{$branch->id}", [
            "name" => "My Branch",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateBranchAsEmployee()
    {
        $employee = Employee::first();

        $response = $this->actingAs($employee)->put("/api/branch/{$employee->employment->branch->id}", [
            "name" => "My Branch",
        ]);

        $response->assertStatus(401);

        $response->assertJsonStructure(["message"]);
    }

    public function testUpdateBranchAsAdmin()
    {
        $store = Store::find(1);

        $branch = $store->branches()->first();

        $response = $this->actingAs($store, "stores")->put("/api/branch/" . $branch->id, [
            "name" => "Branch Name Updated",
            "address" => "Branch Address Updated",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas("branches", [
            "name" => "Branch Name Updated",
            "address" => "Branch Address Updated",
            "store_id" => $store->id,
        ]);

        $response->assertJson([
            "name" => "Branch Name Updated",
            "address" => "Branch Address Updated",
        ]);
    }

    public function testUpdateBranchImageSuccessfully()
    {
        $store = Store::find(1);

        $branch = $store->branches()->first();

        Storage::fake("local");

        $image = UploadedFile::fake()->image("test_image.jpg");

        $response = $this->actingAs($store, "stores")->put("/api/branch/{$branch->id}", [
            "image" => $image,
        ]);

        $response->assertStatus(200);

        Storage::disk("local")->assertMissing($branch->image);

        $branch->refresh();

        Storage::disk("local")->assertExists($branch->image);

        $this->assertTrue(Str::startsWith($branch->image, "images/{$store->id}/branches/"));

        $this->assertDatabaseHas("branches", [
            "image" => $branch->image,
        ]);
    }

    public function testUpdateBranchNotFound()
    {
        $store = Store::find(1);

        $response = $this->actingAs($store, "stores")->put("/api/branch/999", [
            "name" => "Branch Name Updated",
            "address" => "Branch Address Updated",
        ]);

        $response->assertStatus(404);
    }
}
