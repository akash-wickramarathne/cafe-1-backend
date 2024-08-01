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
        Schema::create('food_items', function (Blueprint $table) {
            $table->bigIncrements('food_item_id');
            $table->string('food_name', 250);
            $table->string('description', 500);
            $table->double('price');
            $table->json('food_images');
            $table->unsignedBigInteger('create_admin_id'); // Ensure this matches `create_admin_id` in `food_categories`
            $table->foreign('create_admin_id')->references('admin_id')->on('admins');
            $table->unsignedBigInteger('food_category_id'); // Use `unsignedBigInteger` to match `food_category_id` in `food_categories`
            $table->foreign('food_category_id')->references('food_category_id')->on('food_categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};
