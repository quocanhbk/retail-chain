<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("invoices", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("employee_id");
            $table->unsignedBigInteger("customer_id")->nullable();
            $table->string("code");
            $table->unsignedBigInteger("total");
            $table->unsignedBigInteger("point_used")->default(0);
            $table->unsignedBigInteger("point_added")->default(0);
            $table
                ->foreign("branch_id")
                ->references("id")
                ->on("branches");
            $table
                ->foreign("employee_id")
                ->references("id")
                ->on("employees");
            $table
                ->foreign("customer_id")
                ->references("id")
                ->on("customers");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("invoices");
    }
}
