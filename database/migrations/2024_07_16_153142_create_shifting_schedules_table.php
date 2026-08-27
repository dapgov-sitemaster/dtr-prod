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
        Schema::create('shifting_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('hris_number');
            $table->foreign('hris_number')->references('hris_number')->on('employees');
            $table->datetime('start');
            $table->datetime('end');
            $table->string('tag');
            $table->string('description')->nullable();
            $table->string('created_by');
            $table->foreign('created_by')->references('hris_number')->on('employees');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifting_schedules');
    }
};
