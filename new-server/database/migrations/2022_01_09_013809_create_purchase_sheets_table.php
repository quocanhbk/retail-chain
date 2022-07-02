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
            $table->unsignedBigInteger("discount");
            $table->unsignedBigInteger("paid_amount");
            $table->string("discount_type");
            $table->unsignedBigInteger("total");
            $table->string("note")->nullable();
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
