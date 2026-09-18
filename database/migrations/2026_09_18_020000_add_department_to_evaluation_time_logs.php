<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_time_logs', function (Blueprint $table): void {
            $table->foreignId('department_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('routing_slip_step_id')->nullable()->after('department_id')->constrained()->nullOnDelete();

            $table->index(['department_id', 'started_at']);
            $table->index(['routing_slip_step_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_time_logs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('routing_slip_step_id');
            $table->dropConstrainedForeignId('department_id');
        });
    }
};
