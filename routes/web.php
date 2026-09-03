<?php

use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\ClassHistoryController;
use App\Http\Controllers\Admin\ClassPromotionController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\DailyAttendanceCorrectionController;
use App\Http\Controllers\Admin\DailyAttendanceSettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeacherStatusController;
use App\Http\Controllers\AttendanceMediaController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Guru\GradeController;
use App\Http\Controllers\Sekretaris\AgendaController;
use App\Http\Controllers\Sekretaris\ArchiveController;
use App\Http\Controllers\Sekretaris\AttendanceController;
use App\Http\Controllers\Sekretaris\DailyAttendanceController;
use App\Http\Controllers\Sekretaris\PrintController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\InstitutionController;
use App\Http\Controllers\SuperAdmin\SettingController;
use App\Http\Controllers\Wakasek\CurriculumController;
use App\Http\Controllers\Wakasek\EvaluationController;
use App\Http\Controllers\Wakasek\MonitoringController;
use App\Http\Controllers\Wakasek\TeachingController;
use App\Http\Controllers\WaliKelas\ExportController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root ke login
Route::get('/', function () {
    return redirect('/login');
});

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/academic-period/switch', [AcademicPeriodController::class, 'switch'])->name('academic-period.switch');
    Route::post('/academic-period/reset', [AcademicPeriodController::class, 'reset'])->name('academic-period.reset');
});

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Super Admin Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('institutions/trash', [InstitutionController::class, 'trash'])->name('institutions.trash');
    Route::post('institutions/{id}/restore', [InstitutionController::class, 'restore'])->name('institutions.restore');
    Route::delete('institutions/{id}/force-delete', [InstitutionController::class, 'forceDelete'])->name('institutions.forceDelete');
    Route::post('institutions/{institution}/features', [InstitutionController::class, 'updateFeatures'])->name('institutions.features.update');
    Route::resource('institutions', InstitutionController::class);

    Route::get('admins/trash', [AdminController::class, 'trash'])->name('admins.trash');
    Route::post('admins/{id}/restore', [AdminController::class, 'restore'])->name('admins.restore');
    Route::delete('admins/{id}/force-delete', [AdminController::class, 'forceDelete'])->name('admins.forceDelete');
    Route::resource('admins', AdminController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/maintenance', [SettingController::class, 'toggleMaintenance'])->name('settings.maintenance');
    Route::post('/settings/test-email', [SettingController::class, 'testEmail'])->name('settings.testEmail');
    Route::post('/settings/backup/database', [SettingController::class, 'backupDatabase'])->name('settings.backup.database');
    Route::post('/settings/backup/files', [SettingController::class, 'backupFiles'])->name('settings.backup.files');
    Route::post('/settings/backup/restore', [SettingController::class, 'restoreDatabase'])->name('settings.backup.restore');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/search', [App\Http\Controllers\SuperAdmin\SearchController::class, 'index'])->name('search');

});

// Admin Routes (Kepala Sekolah)
Route::middleware(['auth', 'role:admin', 'system.maintenance'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/api/stats', [DashboardController::class, 'statsApi'])->middleware('throttle:polls')->name('dashboard.api.stats');

    // Manajemen Kelas
    Route::resource('classes', ClassController::class);

    // Manajemen Jurusan (Master)
    Route::resource('majors', MajorController::class)->except(['show']);

    // Manajemen Tahun Ajaran
    Route::resource('academic-years', AcademicYearController::class)->except(['create', 'show', 'edit']);
    Route::post('academic-years/{academic_year}/set-active', [AcademicYearController::class, 'setActive'])->name('academic-years.set-active');
    Route::get('daily-attendance/settings', fn () => redirect('/admin/settings'))->name('daily-attendance.settings');
    Route::get('/daily-attendance/corrections', [DailyAttendanceCorrectionController::class, 'index'])->name('daily-attendance.corrections.index');
    Route::post('/daily-attendance/corrections/{correction}/review', [DailyAttendanceCorrectionController::class, 'review'])->name('daily-attendance.corrections.review');

    // Kenaikan Kelas
    Route::get('class-promotions', [ClassPromotionController::class, 'index'])->name('class-promotions.index');
    Route::get('class-promotions/preview', [ClassPromotionController::class, 'preview'])->middleware('throttle:api')->name('class-promotions.preview');
    Route::post('class-promotions/promote', [ClassPromotionController::class, 'promote'])->name('class-promotions.promote');

    // Manajemen Siswa — with import feature guard
    Route::middleware('feature:import_data')->group(function () {
        Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
    });
    Route::get('students/export/template', [StudentController::class, 'exportTemplate'])->name('students.export.template');
    Route::get('students/export/data', [StudentController::class, 'exportData'])->name('students.export.data');
    Route::get('students/bulk-graduation', [StudentController::class, 'bulkGraduation'])->name('students.bulk-graduation');
    Route::post('students/process-bulk-graduation', [StudentController::class, 'processBulkGraduation'])->name('students.process-bulk-graduation');
    Route::delete('students/bulk-delete', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
    Route::get('students/{student}/history', [StudentController::class, 'history'])->name('students.history');
    Route::resource('students', StudentController::class);

    // Manajemen Guru — with import feature guard
    Route::middleware('feature:import_data')->group(function () {
        Route::post('teachers/import', [TeacherController::class, 'import'])->name('teachers.import');
    });
    Route::get('teachers/export/template', [TeacherController::class, 'exportTemplate'])->name('teachers.export.template');
    Route::resource('teachers', TeacherController::class);

    // Manajemen Mata Pelajaran
    Route::middleware('feature:import_data')->group(function () {
        Route::post('subjects/import', [SubjectController::class, 'import'])->name('subjects.import');
    });
    Route::get('subjects/export/template', [SubjectController::class, 'exportTemplate'])->name('subjects.export.template');
    Route::resource('subjects', SubjectController::class);
    Route::post('subjects/{subject}/add-teacher', [SubjectController::class, 'addTeacher'])->name('subjects.add-teacher');
    Route::delete('subjects/{subject}/remove-teacher/{teacherId}', [SubjectController::class, 'removeTeacher'])->name('subjects.remove-teacher');

    // Manajemen Jadwal
    Route::middleware('feature:schedule')->group(function () {
        Route::middleware('feature:import_data')->group(function () {
            Route::post('schedules/import', [ScheduleController::class, 'import'])->name('schedules.import');
        });
        Route::get('schedules/export/template', [ScheduleController::class, 'exportTemplate'])->name('schedules.export.template');
        Route::get('schedules/get-available-rooms', [ScheduleController::class, 'getAvailableRooms'])->middleware('throttle:api')->name('schedules.get-available-rooms');
        Route::post('schedules/mode', [ScheduleController::class, 'setScheduleMode'])->name('schedules.mode');
        Route::resource('schedules', ScheduleController::class);
    });

    // Manajemen Ruangan
    Route::middleware('feature:rooms')->group(function () {
        Route::resource('rooms', RoomController::class)->except(['show']);
    });

    // Monitoring
    Route::middleware('feature:rooms')->group(function () {
        Route::get('monitoring/rooms', [App\Http\Controllers\Admin\MonitoringController::class, 'rooms'])->name('monitoring.rooms');
    });
    Route::get('academic-records', [ClassHistoryController::class, 'index'])->name('academic-records.index');
    Route::get('academic-records/{classId}/show/{yearName}', [ClassHistoryController::class, 'show'])->name('academic-records.show')->where('yearName', '.*');

    // Monitoring
    Route::get('monitoring/classes', [DashboardController::class, 'monitoringClasses'])->middleware('throttle:polls')->name('monitoring.classes');
    Route::get('monitoring/classes/api', [DashboardController::class, 'monitoringClassesApi'])->middleware('throttle:polls')->name('monitoring.classes.api');
    Route::get('monitoring/teachers', [App\Http\Controllers\Admin\DashboardController::class, 'monitoringTeachers'])->middleware('throttle:polls')->name('monitoring.teachers');
    Route::get('monitoring/teachers/api', [App\Http\Controllers\Admin\DashboardController::class, 'monitoringTeachersApi'])->middleware('throttle:polls')->name('monitoring.teachers.api');
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');

    // Rekap Izin / Tugas Luar Guru (desain §8)
    Route::get('teacher-status/report/csv', [TeacherStatusController::class, 'exportCsv'])->name('teacher-status.report.csv');
    Route::get('teacher-status/report/pdf', [TeacherStatusController::class, 'exportPdf'])->name('teacher-status.report.pdf');
    Route::get('teacher-status/report', [TeacherStatusController::class, 'report'])->name('teacher-status.report');

    Route::get('reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('reports/attendance/student/{id}', [ReportController::class, 'studentAttendance'])->middleware('throttle:api')->name('reports.attendance.student');
    Route::get('reports/export/pdf', [ReportController::class, 'exportPDF'])->name('reports.export.pdf');
    Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Profile & Settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/daily-attendance/settings/edit', [DailyAttendanceSettingController::class, 'edit'])->name('daily-attendance.settings.edit');
    Route::put('/daily-attendance/settings', [DailyAttendanceSettingController::class, 'update'])->name('daily-attendance.settings.update');
});

// Sekretaris Routes
Route::middleware(['auth', 'role:sekretaris', 'system.maintenance'])->prefix('sekretaris')->name('sekretaris.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Sekretaris\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [App\Http\Controllers\Sekretaris\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\Sekretaris\ProfileController::class, 'update'])->name('profile.update');

    // Agenda Kelas
    Route::middleware('feature:agenda_harian')->group(function () {
        Route::get('/agenda/get-schedule-info', [AgendaController::class, 'getScheduleInfo'])->middleware('throttle:api')->name('agenda.get-schedule-info');
        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
        Route::get('/agenda/archive', [ArchiveController::class, 'index'])->name('agenda.archive');
        Route::get('/agenda/{id}/preview', [AgendaController::class, 'preview'])->name('agenda.preview');

        // Rute yang dibatasi jam operasional
        Route::middleware(['operational.hours'])->group(function () {
            Route::get('/agenda/create', [AgendaController::class, 'create'])->name('agenda.create');
            Route::post('/agenda', [AgendaController::class, 'store'])->middleware('agenda.location')->name('agenda.store');
            Route::get('/agenda/{agenda}/edit', [AgendaController::class, 'edit'])->name('agenda.edit');
            Route::put('/agenda/{agenda}', [AgendaController::class, 'update'])->middleware('agenda.location')->name('agenda.update');
            Route::delete('/agenda/{agenda}', [AgendaController::class, 'destroy'])->name('agenda.destroy');
        });

        // Cetak Laporan
        Route::get('/print/agenda', [PrintController::class, 'printAgenda'])->name('print.agenda');
    });

    // Presensi Siswa
    Route::middleware('feature:attendance')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/daily-attendance', [DailyAttendanceController::class, 'index'])->name('daily-attendance.index');

        Route::middleware(['operational.hours'])->group(function () {
            Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
        });
        Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
        Route::get('/attendance/report/student/{id}', [AttendanceController::class, 'studentAttendance'])->middleware('throttle:api')->name('attendance.report.student');

        // Cetak Laporan Presensi
        Route::get('/print/attendance', [PrintController::class, 'printAttendance'])->name('print.attendance');
    });
});

// Wali Kelas Routes
Route::middleware(['auth', 'role:wali_kelas', 'system.maintenance'])->prefix('wali-kelas')->name('wali-kelas.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\WaliKelas\DashboardController::class, 'index'])->name('dashboard');

    // Agenda Kelas (Read Only)
    Route::middleware('feature:agenda_harian')->group(function () {
        Route::get('/agenda/archive', [App\Http\Controllers\WaliKelas\ArchiveController::class, 'index'])->name('agenda.archive');
        Route::get('/agenda', [App\Http\Controllers\WaliKelas\AgendaController::class, 'index'])->name('agenda.index');
        Route::get('/agenda/{agenda}', [App\Http\Controllers\WaliKelas\AgendaController::class, 'show'])->name('agenda.show');
    });

    // Monitoring Presensi
    Route::middleware('feature:attendance')->group(function () {
        Route::get('/attendance', [App\Http\Controllers\WaliKelas\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/daily-attendance', [App\Http\Controllers\WaliKelas\DailyAttendanceController::class, 'index'])->name('daily-attendance.index');
        Route::post('/daily-attendance/{attendance}/verify', [App\Http\Controllers\WaliKelas\DailyAttendanceController::class, 'verify'])->name('daily-attendance.verify');
        Route::post('/daily-attendance/dispen/{earlyLeaveRequest}/confirm', [App\Http\Controllers\WaliKelas\DailyAttendanceController::class, 'confirmDispen'])->name('daily-attendance.dispen.confirm');
        Route::post('/daily-attendance/assist', [App\Http\Controllers\WaliKelas\DailyAttendanceController::class, 'assist'])->name('daily-attendance.assist');
        Route::post('/daily-attendance/assist-checkout', [App\Http\Controllers\WaliKelas\DailyAttendanceController::class, 'assistCheckout'])->name('daily-attendance.assist-checkout');
        Route::post('/daily-attendance/whatsapp/{log}/retry', [App\Http\Controllers\WaliKelas\DailyAttendanceController::class, 'retryWhatsapp'])->name('daily-attendance.whatsapp.retry');
        Route::post('/daily-attendance/corrections/{correction}/review', [App\Http\Controllers\WaliKelas\DailyAttendanceController::class, 'reviewCorrection'])->name('daily-attendance.corrections.review');
        Route::post('/daily-attendance/early-leave/{earlyLeaveRequest}/review', [App\Http\Controllers\WaliKelas\DailyAttendanceController::class, 'reviewEarlyLeave'])->name('daily-attendance.early-leave.review');
        Route::get('/daily-attendance/early-leave', [App\Http\Controllers\WaliKelas\DailyAttendanceController::class, 'earlyLeaveIndex'])->name('daily-attendance.early-leave.index');
        Route::post('/daily-attendance/early-leave/batch', [App\Http\Controllers\WaliKelas\DailyAttendanceController::class, 'batchReviewEarlyLeave'])->name('daily-attendance.early-leave.batch');
        Route::get('/attendance/report', [App\Http\Controllers\WaliKelas\AttendanceController::class, 'report'])->name('attendance.report');
        Route::get('/attendance/report/student/{id}', [App\Http\Controllers\WaliKelas\AttendanceController::class, 'studentAttendance'])->middleware('throttle:api')->name('attendance.report.student');
    });

    // Export Laporan
    Route::middleware('feature:export_pdf')->group(function () {
        Route::get('/export/attendance', [ExportController::class, 'exportAttendance'])->name('export.attendance');
        Route::get('/export/attendance/pdf', [ExportController::class, 'exportAttendancePDF'])->name('export.attendance.pdf');
    });

    // Nilai Siswa
    Route::middleware('feature:nilai_tugas')->group(function () {
        Route::get('/grades', [App\Http\Controllers\WaliKelas\GradeController::class, 'index'])->name('grades.index');
    });

    // Profile
    Route::get('/profile', [App\Http\Controllers\Guru\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\Guru\ProfileController::class, 'update'])->name('profile.update');

});

// Wakasek Routes
Route::middleware(['auth', 'role:wakasek|super_admin', 'system.maintenance'])->prefix('wakasek')->name('wakasek.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Wakasek\DashboardController::class, 'index'])->name('dashboard');

    // Monitoring Kurikulum
    Route::get('/curriculum', [CurriculumController::class, 'index'])->name('curriculum.index');
    Route::get('/curriculum/progress', [CurriculumController::class, 'progress'])->name('curriculum.progress');
    Route::get('/monitoring/agenda', [MonitoringController::class, 'index'])->name('monitoring.agenda');

    // Izin / Tugas Luar Guru (approval wakasek)
    // PENTING: route report, report/csv, report/pdf & attachment didefinisikan
    // SEBELUM {teacherStatus}.
    Route::get('/teacher-status/report/csv', [App\Http\Controllers\Wakasek\TeacherStatusController::class, 'exportCsv'])->name('teacher-status.report.csv');
    Route::get('/teacher-status/report/pdf', [App\Http\Controllers\Wakasek\TeacherStatusController::class, 'exportPdf'])->name('teacher-status.report.pdf');
    Route::get('/teacher-status/report', [App\Http\Controllers\Wakasek\TeacherStatusController::class, 'report'])->name('teacher-status.report');
    Route::get('/teacher-status', [App\Http\Controllers\Wakasek\TeacherStatusController::class, 'index'])->name('teacher-status.index');
    Route::get('/teacher-status/{teacherStatus}/attachment', [App\Http\Controllers\Wakasek\TeacherStatusController::class, 'attachment'])->name('teacher-status.attachment');
    Route::get('/teacher-status/{teacherStatus}', [App\Http\Controllers\Wakasek\TeacherStatusController::class, 'show'])->name('teacher-status.show');
    Route::post('/teacher-status/{teacherStatus}/approve', [App\Http\Controllers\Wakasek\TeacherStatusController::class, 'approve'])->name('teacher-status.approve');
    Route::post('/teacher-status/{teacherStatus}/reject', [App\Http\Controllers\Wakasek\TeacherStatusController::class, 'reject'])->name('teacher-status.reject');
    Route::post('/teacher-status/{teacherStatus}/cancel', [App\Http\Controllers\Wakasek\TeacherStatusController::class, 'cancel'])->name('teacher-status.cancel');

    // Monitoring Pembelajaran
    Route::get('/teaching', [TeachingController::class, 'index'])->name('teaching.index');
    Route::get('/teaching/{teacher}', [TeachingController::class, 'show'])->name('teaching.show');

    // Evaluasi
    Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation.index');
    Route::get('/evaluation/report', [EvaluationController::class, 'report'])->name('evaluation.report');

    // Laporan Presensi
    Route::middleware('feature:attendance')->group(function () {
        Route::get('/daily-attendance', [App\Http\Controllers\Wakasek\DailyAttendanceController::class, 'index'])->name('daily-attendance.index');
        Route::get('reports/attendance', [App\Http\Controllers\Wakasek\ReportController::class, 'attendance'])->name('reports.attendance');
        Route::get('reports/attendance/student/{id}', [App\Http\Controllers\Wakasek\ReportController::class, 'studentAttendance'])->middleware('throttle:api')->name('reports.attendance.student');
    });
    Route::middleware('feature:export_pdf')->group(function () {
        Route::get('reports/export/pdf', [App\Http\Controllers\Wakasek\ReportController::class, 'exportPDF'])->name('reports.export.pdf');
    });
    Route::middleware('feature:export_excel')->group(function () {
        Route::get('reports/export/excel', [App\Http\Controllers\Wakasek\ReportController::class, 'exportExcel'])->name('reports.export.excel');
    });

    // Export Data
    Route::get('/export/teaching', [App\Http\Controllers\Wakasek\ExportController::class, 'exportTeaching'])->name('export.teaching');
    Route::get('/export/teaching/excel', [App\Http\Controllers\Wakasek\ExportController::class, 'exportTeachingExcel'])->name('export.teaching.excel');

    // Profile
    Route::get('/profile', [App\Http\Controllers\Wakasek\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\Wakasek\ProfileController::class, 'update'])->name('profile.update');
});

// Siswa Routes
Route::middleware(['auth', 'role:siswa', 'system.maintenance'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Siswa\DashboardController::class, 'index'])->name('dashboard');

    // Agenda Kelas
    Route::middleware('feature:agenda_harian')->group(function () {
        Route::get('/agenda', [App\Http\Controllers\Siswa\AgendaController::class, 'index'])->name('agenda.index');
        Route::get('/agenda/{id}/json', [App\Http\Controllers\Siswa\AgendaController::class, 'showJson'])->name('agenda.json');
    });

    // Jadwal Pelajaran
    Route::middleware('feature:schedule')->group(function () {
        Route::get('/schedule', [App\Http\Controllers\Siswa\ScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/schedule/by-date', [App\Http\Controllers\Siswa\ScheduleController::class, 'byDate'])->name('schedule.by-date');
        Route::get('/schedule/change-week', [App\Http\Controllers\Siswa\ScheduleController::class, 'changeWeek'])->name('schedule.change-week');
        Route::get('/schedule/today-date', [App\Http\Controllers\Siswa\ScheduleController::class, 'todayDate'])->name('schedule.today-date');
    });

    // Presensi Pribadi
    Route::middleware('feature:attendance')->group(function () {
        Route::get('/attendance', [App\Http\Controllers\Siswa\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/daily-attendance', [App\Http\Controllers\Siswa\DailyAttendanceController::class, 'index'])->name('daily-attendance.index');
        Route::post('/daily-attendance/check-in', [App\Http\Controllers\Siswa\DailyAttendanceController::class, 'checkIn'])->name('daily-attendance.check-in');
        Route::post('/daily-attendance/check-out', [App\Http\Controllers\Siswa\DailyAttendanceController::class, 'checkOut'])->name('daily-attendance.check-out');
        Route::post('/daily-attendance/{attendance}/corrections', [App\Http\Controllers\Siswa\DailyAttendanceController::class, 'requestCorrection'])->name('daily-attendance.corrections.store');
        Route::post('/daily-attendance/early-leave', [App\Http\Controllers\Siswa\DailyAttendanceController::class, 'requestEarlyLeave'])->name('daily-attendance.early-leave.store');
        Route::get('/daily-attendance/early-leave/history', [App\Http\Controllers\Siswa\DailyAttendanceController::class, 'earlyLeaveHistory'])->name('daily-attendance.early-leave.history');
        Route::post('/daily-attendance/early-leave/{earlyLeaveRequest}/update', [App\Http\Controllers\Siswa\DailyAttendanceController::class, 'updateEarlyLeave'])->name('daily-attendance.early-leave.update');
        Route::post('/daily-attendance/early-leave/{earlyLeaveRequest}/cancel', [App\Http\Controllers\Siswa\DailyAttendanceController::class, 'cancelEarlyLeave'])->name('daily-attendance.early-leave.cancel');
    });

    // Nilai Tugas Saya
    Route::middleware('feature:nilai_tugas')->group(function () {
        Route::get('/grades', [App\Http\Controllers\Siswa\GradeController::class, 'index'])->name('grades.index');
        Route::get('/grades/{grade}', [App\Http\Controllers\Siswa\GradeController::class, 'show'])->name('grades.show');
    });

    // Profile
    Route::get('/profile', [App\Http\Controllers\Siswa\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\Siswa\ProfileController::class, 'update'])->name('profile.update');
});

// Guru Routes
Route::middleware(['auth', 'role:teacher', 'system.maintenance'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Guru\DashboardController::class, 'index'])->name('dashboard');

    // Presensi
    Route::middleware('feature:attendance')->group(function () {
        Route::get('/attendance', [App\Http\Controllers\Guru\AttendanceController::class, 'index'])->name('attendance.index');

        // Rute yang dibatasi jam operasional
        Route::middleware(['operational.hours'])->group(function () {
            Route::post('/attendance', [App\Http\Controllers\Guru\AttendanceController::class, 'store'])->name('attendance.store');
        });
    });

    // Teacher Status (Izin / Tugas Luar)
    // PENTING: route dengan segmen statis (create, attachment) didefinisikan
    // SEBELUM route {teacherStatus} supaya route model binding tidak
    // memperlakukan string tersebut sebagai ID record.
    Route::get('/teacher-status', [App\Http\Controllers\Guru\TeacherStatusController::class, 'index'])->name('teacher-status.index');
    Route::get('/teacher-status/create', [App\Http\Controllers\Guru\TeacherStatusController::class, 'create'])->name('teacher-status.create');
    Route::post('/teacher-status', [App\Http\Controllers\Guru\TeacherStatusController::class, 'store'])->name('teacher-status.store');
    Route::get('/teacher-status/{teacherStatus}/edit', [App\Http\Controllers\Guru\TeacherStatusController::class, 'edit'])->name('teacher-status.edit');
    Route::get('/teacher-status/{teacherStatus}', [App\Http\Controllers\Guru\TeacherStatusController::class, 'show'])->name('teacher-status.show');
    Route::get('/teacher-status/{teacherStatus}/attachment', [App\Http\Controllers\Guru\TeacherStatusController::class, 'attachment'])->name('teacher-status.attachment');
    Route::put('/teacher-status/{teacherStatus}', [App\Http\Controllers\Guru\TeacherStatusController::class, 'update'])->name('teacher-status.update');
    Route::post('/teacher-status/{teacherStatus}/withdraw', [App\Http\Controllers\Guru\TeacherStatusController::class, 'withdraw'])->name('teacher-status.withdraw');

    // Nilai Tugas
    Route::middleware('feature:nilai_tugas')->group(function () {
        Route::get('/grades/summary/{class}/{subject}', [GradeController::class, 'summary'])->name('grades.summary');
        Route::get('/grades/summary/{class}/{subject}/export', [GradeController::class, 'exportSummary'])->name('grades.summary.export');
        Route::get('/grades/{grade}/export', [GradeController::class, 'export'])->name('grades.export');
        Route::resource('grades', GradeController::class);
    });

    // Agenda / Jurnal Mengajar
    Route::middleware('feature:agenda_harian')->group(function () {
        Route::get('/agenda/archive', [App\Http\Controllers\Guru\ArchiveController::class, 'index'])->name('agenda.archive');
        Route::get('/agenda/get-schedule-info', [App\Http\Controllers\Guru\AgendaController::class, 'getScheduleInfo'])->middleware('throttle:api')->name('agenda.get-schedule-info');
        Route::get('/agenda', [App\Http\Controllers\Guru\AgendaController::class, 'index'])->name('agenda.index');
        Route::get('/agenda/{id}/preview', [App\Http\Controllers\Guru\AgendaController::class, 'preview'])->name('agenda.preview');

        // Rute yang dibatasi jam operasional
        Route::middleware(['operational.hours'])->group(function () {
            Route::get('/agenda/create', [App\Http\Controllers\Guru\AgendaController::class, 'create'])->name('agenda.create');
            Route::post('/agenda', [App\Http\Controllers\Guru\AgendaController::class, 'store'])->middleware('agenda.location')->name('agenda.store');
            Route::get('/agenda/{agenda}/edit', [App\Http\Controllers\Guru\AgendaController::class, 'edit'])->name('agenda.edit');
            Route::put('/agenda/{agenda}', [App\Http\Controllers\Guru\AgendaController::class, 'update'])->middleware('agenda.location')->name('agenda.update');
        });
        Route::delete('/agenda/{agenda}', [App\Http\Controllers\Guru\AgendaController::class, 'destroy'])->name('agenda.destroy');

        Route::get('/agenda/{agenda}', [App\Http\Controllers\Guru\AgendaController::class, 'show'])->name('agenda.show');

        // Jurnal Guru (Alias for convenience if needed, but agenda is the primary term)
        Route::get('/journal', [App\Http\Controllers\Guru\AgendaController::class, 'index'])->name('journal.index');
    });

    // Laporan
    Route::middleware('feature:attendance')->group(function () {
        Route::get('/report', [App\Http\Controllers\Guru\ReportController::class, 'index'])->name('report.index');
        Route::get('/report/export', [App\Http\Controllers\Guru\ReportController::class, 'export'])->name('report.export');
    });
    Route::middleware('feature:agenda_harian')->group(function () {
        Route::get('/report/agenda/export/excel', [App\Http\Controllers\Guru\ReportController::class, 'exportAgendaExcel'])->name('report.agenda.export.excel');
        Route::get('/report/agenda/export/pdf', [App\Http\Controllers\Guru\ReportController::class, 'exportAgendaPdf'])->name('report.agenda.export.pdf');
    });

    // Profile
    Route::get('/profile', [App\Http\Controllers\Guru\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\Guru\ProfileController::class, 'update'])->name('profile.update');

});

Route::middleware(['auth', 'system.maintenance'])->group(function () {
    Route::get('/daily-attendance/{attendance}/media/{type}', [AttendanceMediaController::class, 'attendance'])->name('attendance.media');
    Route::get('/daily-attendance/corrections/{correction}/evidence', [AttendanceMediaController::class, 'correction'])->name('attendance.corrections.evidence');
    Route::get('/daily-attendance/early-leave/{earlyLeaveRequest}/evidence', [AttendanceMediaController::class, 'earlyLeave'])->name('attendance.early-leave.evidence');
});

// Route untuk user yang sudah login tapi role tidak terdeteksi
Route::middleware(['auth'])->get('/dashboard', function () {
    /** @var User */
    $user = Auth::user();

    if ($user->hasRole('super_admin')) {
        return redirect()->route('super-admin.dashboard');
    } elseif ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('wakasek')) {
        return redirect()->route('wakasek.dashboard');
    } elseif ($user->hasRole('wali_kelas')) {
        return redirect()->route('wali-kelas.dashboard');
    } elseif ($user->hasRole('teacher')) {
        return redirect()->route('guru.dashboard');
    } elseif ($user->hasRole('sekretaris')) {
        return redirect()->route('sekretaris.dashboard');
    } elseif ($user->hasRole('siswa')) {
        return redirect()->route('siswa.dashboard');
    }

    return redirect('/login');
})->name('dashboard');
