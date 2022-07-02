<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("permission_roles", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table
                ->foreignId("permission_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("role_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("store_id")
                ->constrained()
                ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("permission_roles");
    }
}
