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
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id(); // This will create an auto-incrementing ID
            $table->unsignedBigInteger('order_id'); // Foreign key to orders table
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade'); // Optional: Add onDelete behavior
            $table->unsignedBigInteger('order_status_id'); // Foreign key to order_status table
            $table->foreign('order_status_id')->references('order_status_id')->on('order_statuses')->onDelete('cascade'); // Optional: Add onDelete behavior
            $table->timestamp('change_at')->useCurrent(); // Create a timestamp for when the status changed
            $table->timestamps(); // This creates created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
