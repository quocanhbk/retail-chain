<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("items", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string("barcode");
            $table->string("code");
            $table->string("name");
            $table->string("image")->nullable();
            $table->string("image_key")->nullable();
            $table
                ->foreignId("store_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("item_category_id")
                ->nullable()
                ->constrained()
                ->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("items");
    }
}
