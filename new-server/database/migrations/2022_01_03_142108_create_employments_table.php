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
            $table
                ->foreignId("employee_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("branch_id")
                ->constrained()
                ->onDelete("cascade");
            $table->date("from");
            $table->date("to")->nullable();
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
