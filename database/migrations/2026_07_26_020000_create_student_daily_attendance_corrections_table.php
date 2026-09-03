<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_daily_attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_daily_attendance_id')->constrained('student_daily_attendances')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->string('target_event');
            $table->string('requested_status');
            $table->text('reason');
            $table->string('evidence_path')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_note')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'status']);
            $table->index(['student_daily_attendance_id', 'target_event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_daily_attendance_corrections');
    }
};
