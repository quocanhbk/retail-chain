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
            $table
                ->foreignId("return_purchase_sheet_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("item_id")
                ->constrained()
                ->onDelete("cascade");
            $table->integer("quantity");
            $table->unsignedBigInteger("current_price")->default(0);
            $table->unsignedBigInteger("return_price");
            $table->string("return_price_type");
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
        Schema::dropIfExists("return_purchase_sheet_items");
    }
}
