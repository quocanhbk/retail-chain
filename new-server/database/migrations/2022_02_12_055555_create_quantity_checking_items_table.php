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
        Schema::create("quantity_checking_items", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table
                ->foreignId("quantity_checking_sheet_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("item_id")
                ->constrained()
                ->onDelete("cascade");
            $table->unsignedBigInteger("expected_quantity");
            $table->unsignedBigInteger("actual_quantity");
            $table->bigInteger("total");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("quantity_checking_items");
    }
}
