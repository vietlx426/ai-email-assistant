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
        Schema::create('email_pattern_vectors', function (Blueprint $table) {
            $table->id();
            $table->string('pattern_type'); // sprint_commitment, sprint_update, sprint_goals, retrospective
            $table->string('content_hash')->unique(); // MD5 hash to prevent duplicates
            $table->json('embedding'); // Vector embedding as JSON array
            $table->json('metadata')->nullable(); // Additional pattern info (subject_line, tone, length, etc.)
            $table->decimal('confidence_score', 3, 2)->default(0.00); // 0.00-1.00
            $table->integer('dimension')->default(1536); // Embedding dimension size
            $table->unsignedBigInteger('source_email_id')->nullable(); // Link to training email
            $table->timestamps();

            // Indexes for performance
            $table->index('pattern_type');
            $table->index('confidence_score');
            $table->index('content_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_pattern_vectors');
    }
};