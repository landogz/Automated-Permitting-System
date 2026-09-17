<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_rules', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('agency', 20);
            $table->string('basis', 40)->default('fixed');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('rate', 10, 4)->nullable();
            $table->json('conditions')->nullable();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency', 'is_active']);
            $table->index(['is_active', 'priority']);
        });

        Schema::create('inspections', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->string('inspection_no', 40)->unique();
            $table->string('type', 40)->default('joint');
            $table->string('status', 40)->default('scheduled');
            $table->string('result', 40)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('scheduled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->json('compliance_sheet')->nullable();
            $table->json('electrical_form')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['permit_application_id', 'status']);
            $table->index(['inspector_id', 'scheduled_at']);
            $table->index('status');
        });

        Schema::create('orders_of_payment', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->string('oop_no', 40)->unique();
            $table->string('status', 40)->default('draft');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('cto_stub_reference')->nullable();
            $table->text('override_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['permit_application_id', 'status']);
            $table->index('status');
        });

        Schema::create('order_of_payment_lines', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_of_payment_id')->constrained('orders_of_payment')->cascadeOnDelete();
            $table->foreignId('fee_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('agency', 20);
            $table->string('description');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('external_stub_reference')->nullable();
            $table->unsignedSmallInteger('line_order')->default(1);
            $table->timestamps();

            $table->index(['order_of_payment_id', 'line_order']);
            $table->index('agency');
        });

        Schema::create('compliance_notices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('permit_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('notice_no', 40)->unique();
            $table->string('type', 40);
            $table->string('status', 40)->default('draft');
            $table->string('title');
            $table->text('body');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['permit_application_id', 'type']);
            $table->index(['status', 'type']);
        });

        Schema::create('compliance_appeals', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('compliance_notice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filed_by')->constrained('users')->cascadeOnDelete();
            $table->string('status', 40)->default('pending');
            $table->text('grounds');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['compliance_notice_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_appeals');
        Schema::dropIfExists('compliance_notices');
        Schema::dropIfExists('order_of_payment_lines');
        Schema::dropIfExists('orders_of_payment');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('fee_rules');
    }
};
