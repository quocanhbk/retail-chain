<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuantityCheckingSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("quantity_checking_sheets", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("code");
            $table
                ->foreignId("employee_id")
                ->nullable()
                ->constrained()
                ->onDelete("set null");
            $table
                ->foreignId("branch_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->string("note")
                ->nullable()
                ->default("");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("quantity_checking_sheets");
    }
}
