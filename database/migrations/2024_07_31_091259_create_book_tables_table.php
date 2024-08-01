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
        Schema::create('book_tables', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->unsignedBigInteger('table_status_id');
            $table->foreign('table_status_id')->references('order_status_id')->on('order_statuses');
            $table->unsignedBigInteger('waiter_id')->nullable();
            $table->decimal('payment', 8, 2);
            $table->foreign('waiter_id')->references('waiter_id')->on('waiters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_tables');
    }
};
