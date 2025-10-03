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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Color", "Size", "Material"
            $table->string('slug')->unique(); // e.g., "color", "size", "material"
            $table->text('description')->nullable();
            $table->string('type')->default('select'); // select, color, image, text
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(true); // Can be used in product filters
            $table->boolean('is_variation')->default(true); // Can be used for variations
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};