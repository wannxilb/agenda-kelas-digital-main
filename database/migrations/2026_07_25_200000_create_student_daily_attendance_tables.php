<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('parent_phone')->nullable()->after('phone');
        });

        Schema::create('daily_attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('cascade');
            $table->time('check_in_deadline')->default('06:30:00');
            $table->time('check_out_start')->default('14:30:00');
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(0);
            $table->boolean('require_check_in_photo')->default(true);
            $table->boolean('require_check_out_photo')->default(true);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->text('check_in_message_template')->nullable();
            $table->text('check_out_message_template')->nullable();
            $table->timestamps();

            $table->unique('institution_id');
        });

        Schema::create('student_daily_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->date('date');
            $table->timestamp('check_in_at')->nullable();
            $table->string('check_in_photo')->nullable();
            $table->string('check_in_status')->nullable();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->timestamp('check_out_at')->nullable();
            $table->string('check_out_photo')->nullable();
            $table->string('check_out_status')->nullable();
            $table->unsignedSmallInteger('early_leave_minutes')->default(0);
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['student_id', 'date'], 'student_daily_attendance_student_date_unique');
            $table->index(['class_id', 'date'], 'student_daily_attendance_class_date_index');
        });

        Schema::create('whatsapp_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_daily_attendance_id')->nullable()->constrained('student_daily_attendances')->onDelete('set null');
            $table->foreignId('student_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('recipient_phone')->nullable();
            $table->string('event_type');
            $table->text('message');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_logs');
        Schema::dropIfExists('student_daily_attendances');
        Schema::dropIfExists('daily_attendance_settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('parent_phone');
        });
    }
};
