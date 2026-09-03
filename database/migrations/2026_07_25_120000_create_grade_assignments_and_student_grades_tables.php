<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->date('assigned_date');
            $table->date('due_date')->nullable();
            $table->decimal('max_score', 5, 2)->default(100);
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('cascade');
            $table->timestamps();

            $table->index(['teacher_id', 'class_id', 'subject_id'], 'grade_assignments_teacher_class_subject_index');
        });

        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_assignment_id')->constrained('grade_assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->decimal('score', 5, 2)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['grade_assignment_id', 'student_id'], 'student_grades_assignment_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_grades');
        Schema::dropIfExists('grade_assignments');
    }
};
