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
            $table
                ->foreignId("shift_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("employee_id")
                ->constrained()
                ->onDelete("cascade");
            $table->date("date");
            $table->string("note")->default("");
            $table
                ->boolean("is_absent")
                ->nullable()
                ->default(null);
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
