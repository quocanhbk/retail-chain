<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("refund_items", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table
                ->foreignId("refund_sheet_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("item_id")
                ->constrained()
                ->onDelete("cascade");
            $table->unsignedBigInteger("quantity");
            $table->boolean("resellable");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("refund_items");
    }
}
