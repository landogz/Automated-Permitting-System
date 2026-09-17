<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('approval_status', 20)->default('approved')->after('is_active');
            $table->text('approval_notes')->nullable()->after('approval_status');
            $table->timestamp('registered_at')->nullable()->after('approval_notes');
            $table->timestamp('approved_at')->nullable()->after('registered_at');
            $table->timestamp('declined_at')->nullable()->after('approved_at');
            $table->foreignId('reviewed_by')->nullable()->after('declined_at')->constrained('users')->nullOnDelete();

            $table->index(['approval_status', 'registered_at']);
        });

        DB::table('users')->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropIndex(['approval_status', 'registered_at']);
            $table->dropColumn([
                'approval_status',
                'approval_notes',
                'registered_at',
                'approved_at',
                'declined_at',
            ]);
        });
    }
};
