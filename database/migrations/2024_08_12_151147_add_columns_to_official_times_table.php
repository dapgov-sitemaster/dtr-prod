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
        Schema::table('official_times', function (Blueprint $table) {
            $table->enum('schedule_type', ['full_flexitime', 'fixed_officialtime'])->nullable()->after('time_in');
            $table->date('effectivity_date')->nullable()->after('schedule_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('official_times', function (Blueprint $table) {
            $table->dropColumn('schedule_type');
            $table->dropColumn('effectivity_date');
        });
    }
};
