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
        Schema::create('email_training_data', function (Blueprint $table) {
            $table->id();
            $table->string('email_type'); // sprint_commitment, sprint_update, etc.
            $table->string('subject_line')->nullable();
            $table->text('content'); // Original email content
            $table->string('filename')->nullable(); // Original file name if uploaded
            $table->string('content_hash'); // Hash for duplicate detection
            $table->json('extracted_patterns')->nullable(); // AI-analyzed patterns
            $table->boolean('is_processed')->default(false); // Has been analyzed by AI
            $table->boolean('is_approved')->default(true); // User can reject bad examples
            $table->json('metadata')->nullable(); // Additional info (date sent, recipient, etc.)
            $table->timestamps();

            // Indexes
            $table->index('email_type');
            $table->index('is_processed');
            $table->index('is_approved');
            $table->unique('content_hash'); // Prevent duplicate uploads
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_training_data');
    }
};