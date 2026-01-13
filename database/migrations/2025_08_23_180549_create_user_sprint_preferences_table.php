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
        Schema::create('user_sprint_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('user_identifier')->default('default'); // For multi-user support later
            $table->json('default_sprint_data')->nullable(); // Default team, manager, etc.
            $table->json('writing_preferences')->nullable(); // Tone, formality, length preferences
            $table->decimal('confidence_threshold', 3, 2)->default(0.70); // Min confidence for auto-generation
            $table->boolean('auto_learn')->default(true); // Learn from user feedback
            $table->json('email_signatures')->nullable(); // Different signatures for different contexts
            $table->timestamps();

            // Index
            $table->index('user_identifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sprint_preferences');
    }
};