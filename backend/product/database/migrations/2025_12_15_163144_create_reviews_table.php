<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('reviewer_name');
            $table->tinyInteger('rating')->unsigned(); // 0-255 range
            $table->text('comment')->nullable();
            $table->timestamps();
            
            // Composite index for better query performance
            $table->index(['product_id', 'created_at']);
            $table->index(['product_id', 'rating']);
            
            // Add constraint via raw SQL (optional)
            // DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_rating_range CHECK (rating >= 1 AND rating <= 5)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};