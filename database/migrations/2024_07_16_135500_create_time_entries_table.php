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
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->string('hris_number');
            $table->foreign('hris_number')->references('hris_number')->on('employees');
            $table->datetime('time_start');
            $table->datetime('time_end')->nullable();
            $table->foreignId('department_id')->constrained('departments');
            $table->time('official_time');
            $table->foreignId('timekeeper_id')->constrained('users');
            $table->enum('tag', ['ros', 'wfh', 'mvpool']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
