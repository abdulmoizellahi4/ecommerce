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
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained()->onDelete('cascade');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_variation')->default(false); // Used for creating variations
            $table->boolean('is_visible')->default(true); // Show on product page
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id']);
            $table->index(['product_id', 'is_variation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};