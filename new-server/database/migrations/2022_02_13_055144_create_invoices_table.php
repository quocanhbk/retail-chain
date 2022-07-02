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
            $table
                ->foreignId("branch_id")
                ->constrained()
                ->onDelete("cascade");
            $table
                ->foreignId("employee_id")
                ->nullable()
                ->constrained()
                ->onDelete("set null");
            $table
                ->foreignId("customer_id")
                ->nullable()
                ->constrained()
                ->onDelete("set null");
            $table->string("code");
            $table->unsignedBigInteger("total");
            $table->unsignedBigInteger("point_used")->default(0);
            $table->unsignedBigInteger("point_added")->default(0);
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
