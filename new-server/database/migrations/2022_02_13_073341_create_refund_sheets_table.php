<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("refund_sheets", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("code");
            $table
                ->foreignId("branch_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("employee_id")
                ->nullable()
                ->constrained()
                ->onDelete("set null");
            $table
                ->foreignId("invoice_id")
                ->nullable()
                ->constrained()
                ->onDelete("set null");
            $table->unsignedBigInteger("total");
            $table->string("reason")->default("");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("refund_sheets");
    }
}
