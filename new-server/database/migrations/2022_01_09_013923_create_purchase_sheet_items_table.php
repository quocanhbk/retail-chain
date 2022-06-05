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
            $table->unsignedBigInteger("purchase_sheet_id");
            $table->unsignedBigInteger("item_id")->unsigned();
            $table->integer("quantity");
            $table->unsignedBigInteger("price");
            $table->unsignedBigInteger("discount");
            $table->string("discount_type");
            $table->unsignedBigInteger("total");
            $table
                ->foreign("purchase_sheet_id")
                ->references("id")
                ->on("purchase_sheets")
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
        Schema::dropIfExists("purchase_sheet_items");
    }
}
