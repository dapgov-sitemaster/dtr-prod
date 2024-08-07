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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('hris_number')->nullable();
            $table->foreign('hris_number')->references('hris_number')->on('employees');
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
            $table->string('tag');
            $table->string('description');
            $table->enum('status', ['pending', 'approved', 'disapproved'])->default('pending');
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
        Schema::dropIfExists('events');
    }
};
