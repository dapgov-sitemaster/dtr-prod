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
        Schema::table('reports', function (Blueprint $table) {
            $table->datetime('break_start')->nullable()->after('time_start');
            $table->datetime('break_end')->nullable()->after('break_start');
            $table->enum('schedule_type', ['full_flexitime', 'fixed_officialtime'])->nullable()->after('time_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('break_start');
            $table->dropColumn('break_end');
            $table->dropColumn('schedule_type');
        });
    }
};
