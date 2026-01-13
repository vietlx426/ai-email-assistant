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
        Schema::create('sprint_email_history', function (Blueprint $table) {
            $table->id();
            $table->string('email_type'); // commitment, update, goals, retrospective, etc.
            $table->text('input_prompt'); // What user typed/requested
            $table->text('generated_content'); // The final generated email
            $table->unsignedBigInteger('template_used_id')->nullable(); // FK to email_templates
            $table->integer('user_feedback')->nullable(); // 1=thumbs up, -1=thumbs down, null=no feedback
            $table->json('sprint_data')->nullable(); // Sprint info used (goals, team, dates, etc.)
            $table->json('generation_metadata')->nullable(); // AI model, tokens used, processing time
            $table->decimal('confidence_score', 3, 2)->nullable(); // AI confidence in generation
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('template_used_id')->references('id')->on('email_templates')->onDelete('set null');

            // Indexes for performance
            $table->index('email_type');
            $table->index('user_feedback');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sprint_email_history');
    }
};