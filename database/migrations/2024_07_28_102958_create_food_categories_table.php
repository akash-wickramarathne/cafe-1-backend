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
        Schema::create('food_categories', function (Blueprint $table) {
            $table->bigIncrements('food_category_id');
            $table->string('food_type_name', 250);
            $table->string('food_type_description', 500);
            $table->unsignedBigInteger('create_admin_id'); // Ensure it matches the foreign key column in `food_items`
            $table->foreign('create_admin_id')->references('admin_id')->on('admins');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_categories');
    }
};
