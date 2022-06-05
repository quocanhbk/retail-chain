<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmploymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("employments", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger("employee_id");
            $table->unsignedBigInteger("branch_id");
            $table->date("from");
            $table->date("to")->nullable();
        });

        Schema::table("employments", function (Blueprint $table) {
            $table
                ->foreign("employee_id")
                ->references("id")
                ->on("employees");
            $table
                ->foreign("branch_id")
                ->references("id")
                ->on("branches");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("employments");
    }
}
