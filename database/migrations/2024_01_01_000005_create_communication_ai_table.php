<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Communication (notices / routines) and AI + audit logging migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------------
        // Notices & Events
        // ---------------------------------------------------------------------
        Schema::create('notices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->enum('type', ['announcement', 'event', 'notification'])->default('announcement');
            $table->enum('audience', ['all', 'students', 'teachers', 'parents', 'admins'])->default('all');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ---------------------------------------------------------------------
        // Routines (class & exam schedules)
        // ---------------------------------------------------------------------
        Schema::create('routines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['class', 'exam'])->default('class');
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week')->comment('1=Mon ... 7=Sun');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ---------------------------------------------------------------------
        // AI Assistant
        // ---------------------------------------------------------------------
        Schema::create('ai_conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->json('messages')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('intent')->nullable();
            $table->string('tool')->nullable();
            $table->text('query');
            $table->longText('response')->nullable();
            $table->unsignedInteger('tokens_used')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['institution_id', 'created_at']);
        });

        Schema::create('ai_feedback', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_audit_log_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // ---------------------------------------------------------------------
        // Generic audit log
        // ---------------------------------------------------------------------
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('ai_feedback');
        Schema::dropIfExists('ai_audit_logs');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('routines');
        Schema::dropIfExists('notices');
    }
};
