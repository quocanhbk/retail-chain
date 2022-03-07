<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuantityCheckingItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quantity_checking_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('quantity_checking_sheet_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('expected');
            $table->unsignedInteger('actual');
            $table->unsignedInteger('total');
            $table->foreign('quantity_checking_sheet_id')->references('id')->on('quantity_checking_sheets')->onDelete('cascade');
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
        Schema::dropIfExists('quantity_checking_items');
    }
}
