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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique()->nullable(); // Variation SKU
            $table->string('name')->nullable(); // Variation name (e.g., "Red - Small")
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable(); // Variation price (overrides product price)
            $table->decimal('sale_price', 10, 2)->nullable(); // Sale price
            $table->integer('stock_quantity')->default(0);
            $table->boolean('manage_stock')->default(true);
            $table->boolean('stock_status')->default(true); // in_stock, out_of_stock
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('image_url')->nullable(); // Variation specific image
            $table->json('gallery_images')->nullable(); // Multiple images for variation
            $table->json('attribute_data')->nullable(); // Store attribute combinations as JSON
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
            $table->index(['sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};