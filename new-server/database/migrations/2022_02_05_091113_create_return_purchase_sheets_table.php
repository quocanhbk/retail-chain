<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnPurchaseSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_purchase_sheets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('code');
            $table->unsignedBigInteger('purchase_sheet_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('discount');
            $table->string('discount_type');
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('paid_amount');
            $table->text('note')->nullable();
            $table->foreign('purchase_sheet_id')->references('id')->on('purchase_sheets')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
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
        Schema::dropIfExists('return_purchase_sheets');
    }
}
