<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('entry_no', 40)->unique();
            $table->string('book_type', 40);
            $table->foreignId('permit_application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_contact')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['book_type', 'recorded_at']);
            $table->index('permit_application_id');
        });

        Schema::create('archive_records', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('archive_no', 40)->unique();
            $table->foreignId('permit_application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('storage_location')->nullable();
            $table->string('media_type', 40)->default('digital');
            $table->string('checksum')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['media_type', 'archived_at']);
            $table->index('permit_application_id');
        });

        Schema::create('notification_templates', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('channel', 20)->default('in_app');
            $table->string('subject')->nullable();
            $table->text('body_template');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['channel', 'is_active']);
        });

        Schema::create('user_notifications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('template_code', 50)->nullable();
            $table->string('channel', 20)->default('in_app');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->string('status', 20)->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('archive_records');
        Schema::dropIfExists('logbook_entries');
    }
};
