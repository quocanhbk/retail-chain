<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnPurchaseSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("return_purchase_sheets", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("code");
            $table
                ->foreignId("purchase_sheet_id")
                ->nullable()
                ->constrained()
                ->onDelete("set null");
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
            $table->string("discount_type");
            $table->unsignedBigInteger("total");
            $table->unsignedBigInteger("paid_amount");
            $table->text("note")->default("");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("return_purchase_sheets");
    }
}
