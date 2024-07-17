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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('hris_number');
            $table->foreign('hris_number')->references('hris_number')->on('employees');
            $table->datetime('time_start')->nullable();
            $table->datetime('time_end')->nullable();
            $table->time('official_time')->nullable();
            $table->string('office');
            $table->string('appointment_status');
            $table->string('time_entry_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
