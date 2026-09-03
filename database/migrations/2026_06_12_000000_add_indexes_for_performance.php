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
        // Add indexes to users table for better query performance
        Schema::table('users', function (Blueprint $table) {
            // Index for student searches
            $table->index('nis', 'users_nis_index');
            $table->index('nisn', 'users_nisn_index');
            $table->index('name', 'users_name_index');
            
            // Index for filtering
            $table->index('class_id', 'users_class_id_index');
            $table->index('gender', 'users_gender_index');
            $table->index('status', 'users_status_index');
            
            // Composite indexes for common query patterns
            $table->index(['class_id', 'status'], 'users_class_status_index');
            $table->index(['gender', 'status'], 'users_gender_status_index');
        });
        
        // Add indexes to classes table
        Schema::table('classes', function (Blueprint $table) {
            $table->index('academic_year', 'classes_academic_year_index');
            $table->index('grade_level', 'classes_grade_level_index');
            $table->index('homeroom_teacher_id', 'classes_homeroom_teacher_id_index');
            $table->index(['academic_year', 'grade_level'], 'classes_year_grade_index');
        });
        
        // Add indexes to attendances table for performance
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('student_id', 'attendances_student_id_index');
            $table->index('date', 'attendances_date_index');
            $table->index(['student_id', 'date'], 'attendances_student_date_index');
        });
        
        // Add indexes to agendas table
        Schema::table('agendas', function (Blueprint $table) {
            $table->index('teacher_id', 'agendas_teacher_id_index');
            $table->index('date', 'agendas_date_index');
            $table->index(['teacher_id', 'date'], 'agendas_teacher_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_nis_index');
            $table->dropIndex('users_nisn_index');
            $table->dropIndex('users_name_index');
            $table->dropIndex('users_class_id_index');
            $table->dropIndex('users_gender_index');
            $table->dropIndex('users_status_index');
            $table->dropIndex('users_class_status_index');
            $table->dropIndex('users_gender_status_index');
        });
        
        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex('classes_academic_year_index');
            $table->dropIndex('classes_grade_level_index');
            $table->dropIndex('classes_homeroom_teacher_id_index');
            $table->dropIndex('classes_year_grade_index');
        });
        
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_student_id_index');
            $table->dropIndex('attendances_date_index');
            $table->dropIndex('attendances_student_date_index');
        });
        
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropIndex('agendas_teacher_id_index');
            $table->dropIndex('agendas_date_index');
            $table->dropIndex('agendas_teacher_date_index');
        });
    }
};
