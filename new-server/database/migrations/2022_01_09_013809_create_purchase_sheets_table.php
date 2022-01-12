<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_sheets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('code');
            $table->bigInteger('employee_id')->unsigned();
            $table->bigInteger('branch_id')->unsigned();
            $table->bigInteger('supplier_id')->unsigned();
            $table->decimal('discount', 15, 2);
            $table->string('discount_type');
            $table->decimal('total', 15, 2);
            $table->string('status');
            $table->string('note')->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_sheets');
    }
}
