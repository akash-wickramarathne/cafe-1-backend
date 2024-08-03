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
        Schema::create('tables', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key

            $table->string('name'); // e.g., "Table 1", "Table 2", etc.
            $table->foreignId('size_id')->constrained('table_sizes'); // Foreign key to table_sizes
            $table->foreignId('status_id')->constrained('table_statuses'); // Foreign key to table_statuses
            $table->integer('seats')->default(4); // Number of seats at the table (optional)

            $table->timestamps(); // Created at and updated at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
