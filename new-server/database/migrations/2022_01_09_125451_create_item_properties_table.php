<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("item_properties", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer("quantity");
            $table->unsignedBigInteger("sell_price")->default(0);
            $table->unsignedBigInteger("base_price")->default(0);
            $table->unsignedBigInteger("last_purchase_price")->nullable();
            $table
                ->foreignId("item_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("branch_id")
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
        Schema::dropIfExists("item_properties");
    }
}
