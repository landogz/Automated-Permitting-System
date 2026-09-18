<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table): void {
            $table->json('team_inspectors')->nullable()->after('inspector_id');
            $table->json('schedule_sheet')->nullable()->after('notes');
            $table->json('inspector_notes')->nullable()->after('schedule_sheet');
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table): void {
            $table->dropColumn(['team_inspectors', 'schedule_sheet', 'inspector_notes']);
        });
    }
};
