<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPriceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("item_price_histories", function (Blueprint $table) {
            $table->id();
            $table->decimal("price", 13, 0);
            $table->date("start_date");
            $table->date("end_date")->nullable();
            $table
                ->foreignId("item_id")
                ->constrained()
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
        Schema::dropIfExists("item_price_histories");
    }
}
