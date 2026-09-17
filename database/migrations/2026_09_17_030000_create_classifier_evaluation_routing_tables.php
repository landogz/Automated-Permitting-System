<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classification_rules', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('classification', 40);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->json('conditions')->nullable();
            $table->unsignedInteger('sla_hours')->default(72);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'priority']);
            $table->index('classification');
        });

        Schema::create('routing_templates', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('classification', 40);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['classification', 'is_active']);
        });

        Schema::create('routing_template_steps', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('routing_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('step_order')->default(1);
            $table->string('label');
            $table->unsignedInteger('sla_hours')->default(24);
            $table->timestamps();

            $table->unique(['routing_template_id', 'step_order'], 'routing_template_step_order_unique');
            $table->index('department_id');
        });

        Schema::create('routing_slips', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routing_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slip_no', 40)->unique();
            $table->string('classification', 40);
            $table->string('status', 40)->default('open');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['permit_application_id', 'status']);
            $table->index('classification');
        });

        Schema::create('routing_slip_steps', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('routing_slip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('step_order')->default(1);
            $table->string('label');
            $table->string('status', 40)->default('pending');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['routing_slip_id', 'step_order'], 'routing_slip_step_order_unique');
            $table->index(['department_id', 'status']);
        });

        Schema::create('evaluations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routing_slip_step_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 40)->default('draft');
            $table->string('result', 40)->nullable();
            $table->json('findings')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['permit_application_id', 'status']);
            $table->index(['evaluator_id', 'created_at']);
        });

        Schema::create('evaluation_time_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'ended_at']);
            $table->index(['permit_application_id', 'started_at']);
        });

        Schema::table('permit_applications', function (Blueprint $table): void {
            $table->string('classified_by_rule')->nullable()->after('classification');
            $table->foreignId('classified_by')->nullable()->after('classified_by_rule')->constrained('users')->nullOnDelete();
            $table->timestamp('classified_at')->nullable()->after('classified_by');
        });
    }

    public function down(): void
    {
        Schema::table('permit_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('classified_by');
            $table->dropColumn(['classified_by_rule', 'classified_at']);
        });

        Schema::dropIfExists('evaluation_time_logs');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('routing_slip_steps');
        Schema::dropIfExists('routing_slips');
        Schema::dropIfExists('routing_template_steps');
        Schema::dropIfExists('routing_templates');
        Schema::dropIfExists('classification_rules');
    }
};
