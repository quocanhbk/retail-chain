<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("purchase_sheets", function (Blueprint $table) {
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
                ->foreignId("supplier_id")
                ->nullable()
                ->constrained()
                ->onDelete("set null");
            $table->unsignedBigInteger("discount")->nullable();
            $table->string("discount_type")->nullable();
            $table
                ->unsignedBigInteger("paid_amount")
                ->nullable()
                ->default(0);
            $table->unsignedBigInteger("total");
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
        Schema::dropIfExists("purchase_sheets");
    }
}
