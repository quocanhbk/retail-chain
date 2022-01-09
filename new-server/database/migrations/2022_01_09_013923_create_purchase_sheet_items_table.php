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
        Schema::create('purchase_sheet_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('purchase_sheet_id')->unsigned();
            $table->bigInteger('item_id')->unsigned();
            $table->integer('quantity');
            $table->string('unit');
            $table->decimal('price', 15, 2);
            $table->decimal('discount', 15, 2);
            $table->string('discount_type');
            $table->decimal('total', 15, 2);
            $table->foreign('purchase_sheet_id')->references('id')->on('purchase_sheets')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_sheet_items');
    }
}
