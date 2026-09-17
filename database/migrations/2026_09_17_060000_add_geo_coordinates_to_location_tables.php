<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permit_applications', function (Blueprint $table): void {
            $table->decimal('latitude', 10, 7)->nullable()->after('project_location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        Schema::table('inspections', function (Blueprint $table): void {
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('permit_applications', function (Blueprint $table): void {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('inspections', function (Blueprint $table): void {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
