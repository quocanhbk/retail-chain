<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseSheetItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("purchase_sheet_items", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table
                ->foreignId("purchase_sheet_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("item_id")
                ->constrained()
                ->onDelete("cascade");
            $table->integer("quantity");
            $table->unsignedBigInteger("price");
            $table->unsignedBigInteger("discount")->nullable();
            $table->string("discount_type")->nullable();
            $table->unsignedBigInteger("total");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("purchase_sheet_items");
    }
}
