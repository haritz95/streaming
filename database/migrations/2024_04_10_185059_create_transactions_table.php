<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->biginteger('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->biginteger('platform_id')->unsigned();
            $table->foreign('platform_id')->references('id')->on('platforms');
            $table->integer('quantity');
            $table->double('cost_price');
            $table->double('sell_price');
            $table->double('profit');
            $table->double('total');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
