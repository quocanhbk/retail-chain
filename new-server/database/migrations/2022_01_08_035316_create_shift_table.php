<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('branch_id');
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shifts');
    }
}
