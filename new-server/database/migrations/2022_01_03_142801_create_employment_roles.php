<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmploymentRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employment_roles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('employment_id');
            $table->string('role');
        });

        Schema::table('employment_roles', function (Blueprint $table) {
            $table->foreign('employment_id')->references('id')->on('employments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employment_roles');
    }
}
