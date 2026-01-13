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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('pattern_type'); // sprint_commitment, sprint_update, etc.
            $table->string('template_name')->nullable(); // User-friendly name
            $table->text('template_content'); // Raw text template with variables
            $table->json('variables'); // Array of variables like {{sprint_goal}}, {{team_name}}
            $table->json('style_attributes'); // Tone, formality, structure patterns
            $table->integer('usage_count')->default(0); // How often used
            $table->decimal('success_rate', 3, 2)->default(0.00); // Based on user feedback
            $table->unsignedBigInteger('source_email_id')->nullable(); // Original training email
            $table->boolean('is_active')->default(true); // Can be disabled
            $table->timestamps();

            // Indexes for performance
            $table->index('pattern_type');
            $table->index('success_rate');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};