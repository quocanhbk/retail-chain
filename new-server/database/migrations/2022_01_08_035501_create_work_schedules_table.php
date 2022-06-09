<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("work_schedules", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger("shift_id");
            $table->unsignedBigInteger("employee_id");
            $table->date("date");
            $table->string("note")->default("");
            $table
                ->boolean("is_absent")
                ->nullable()
                ->default(null);
            $table
                ->foreign("shift_id")
                ->references("id")
                ->on("shifts");
            $table
                ->foreign("employee_id")
                ->references("id")
                ->on("employees");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("work_schedules");
    }
}
