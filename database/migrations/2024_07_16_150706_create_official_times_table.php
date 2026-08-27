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
        Schema::create('official_times', function (Blueprint $table) {
            $table->id();
            $table->string('hris_number');
            $table->foreign('hris_number')->references('hris_number')->on('employees');
            $table->time('time_in')->nullable();
            $table->enum('status', ['pending', 'approved', 'disapproved'])->default('pending');
            $table->string('created_by');
            $table->foreign('created_by')->references('hris_number')->on('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('official_times');
    }
};
