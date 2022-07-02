<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("employees", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("email");
            $table->string("avatar")->nullable();
            $table->string("avatar_key")->nullable();
            $table->string("phone")->nullable();
            $table->date("birthday")->nullable();
            $table->string("gender")->nullable();
            $table->string("password");
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists("employees");
    }
}
