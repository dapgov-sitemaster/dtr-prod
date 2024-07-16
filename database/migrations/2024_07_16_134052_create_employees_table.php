<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('hris_number');
            $table->foreign('hris_number')->references('hris_number')->on('users');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name');
            $table->foreignId('department_id')->constrained('departments');
            $table->enum('appointment_status', ['pbp', 'npp']);
            $table->boolean('employment_status')->default(true);
            $table->string('signature_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
