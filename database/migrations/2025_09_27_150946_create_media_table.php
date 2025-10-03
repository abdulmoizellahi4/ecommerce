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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_url');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->string('alt_text')->nullable();
            $table->text('description')->nullable();
            $table->string('uploaded_by')->nullable(); // user_id or admin_id
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['mime_type', 'is_active']);
            $table->index(['uploaded_by', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
