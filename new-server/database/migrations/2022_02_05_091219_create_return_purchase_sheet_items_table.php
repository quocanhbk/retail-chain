<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnPurchaseSheetItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("return_purchase_sheet_items", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger("return_purchase_sheet_id");
            $table->unsignedBigInteger("item_id");
            $table->integer("quantity");
            $table->unsignedBigInteger("return_price");
            $table->string("return_price_type");
            $table->unsignedBigInteger("total");
            $table
                ->foreign("return_purchase_sheet_id")
                ->references("id")
                ->on("return_purchase_sheets")
                ->onDelete("cascade");
            $table
                ->foreign("item_id")
                ->references("id")
                ->on("items")
                ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("return_purchase_sheet_items");
    }
}
