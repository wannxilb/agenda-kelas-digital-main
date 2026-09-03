<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed default system settings if they don't exist yet.
     */
    public function up(): void
    {
        $defaults = [
            // General
            ['key' => 'app_name',           'value' => 'Agenda Kelas Digital',          'group' => 'general'],
            ['key' => 'app_version',        'value' => '1.0.0',                        'group' => 'general'],
            ['key' => 'app_developer',      'value' => '',                              'group' => 'general'],
            ['key' => 'app_copyright',      'value' => '© 2026 Agenda Kelas Digital',  'group' => 'general'],
            ['key' => 'app_timezone',       'value' => 'Asia/Jakarta',                  'group' => 'general'],
            ['key' => 'app_language',       'value' => 'id',                            'group' => 'general'],
            ['key' => 'app_date_format',    'value' => 'd/m/Y',                        'group' => 'general'],
            ['key' => 'app_time_format',    'value' => 'H:i',                          'group' => 'general'],

            // Authentication
            ['key' => 'auth_allow_login',           'value' => '1', 'group' => 'authentication'],
            ['key' => 'auth_login_email',           'value' => '1', 'group' => 'authentication'],
            ['key' => 'auth_login_username',        'value' => '0', 'group' => 'authentication'],
            ['key' => 'auth_force_password_change', 'value' => '0', 'group' => 'authentication'],
            ['key' => 'auth_auto_logout',           'value' => '1', 'group' => 'authentication'],
            ['key' => 'auth_session_timeout',       'value' => '30','group' => 'authentication'],

            // Institution
            ['key' => 'inst_max_admins',          'value' => '2',      'group' => 'institution'],
            ['key' => 'inst_max_teachers',        'value' => '500',    'group' => 'institution'],
            ['key' => 'inst_max_students',        'value' => '5000',   'group' => 'institution'],
            ['key' => 'inst_max_classes',         'value' => '100',    'group' => 'institution'],
            ['key' => 'inst_default_status',      'value' => 'active', 'group' => 'institution'],
            ['key' => 'inst_default_expiry_days', 'value' => '365',   'group' => 'institution'],

            // Features
            ['key' => 'feature_agenda_harian', 'value' => '1', 'group' => 'features'],
            ['key' => 'feature_attendance',    'value' => '1', 'group' => 'features'],
            ['key' => 'feature_schedule',      'value' => '1', 'group' => 'features'],
            ['key' => 'feature_rooms',         'value' => '1', 'group' => 'features'],
            ['key' => 'feature_export_pdf',    'value' => '1', 'group' => 'features'],
            ['key' => 'feature_export_excel',  'value' => '0', 'group' => 'features'],
            ['key' => 'feature_import_data',   'value' => '1', 'group' => 'features'],
            ['key' => 'schedule_time_slots',   'value' => "06:30\n07:15\n08:00\n08:45\n09:30|Istirahat\n09:45\n10:30\n11:15\n12:00|Istirahat & ISHOMA\n12:45\n13:30\n14:00\n14:15\n15:00\n15:30\n15:45", 'group' => 'features'],

            // Security
            ['key' => 'sec_min_password',         'value' => '8',  'group' => 'security'],
            ['key' => 'sec_max_login_attempts',   'value' => '5',  'group' => 'security'],
            ['key' => 'sec_lockout_duration',     'value' => '15', 'group' => 'security'],
            ['key' => 'sec_password_expiry_days', 'value' => '0',  'group' => 'security'],


            // Email
            ['key' => 'mail_host',         'value' => '',                      'group' => 'email'],
            ['key' => 'mail_port',         'value' => '587',                   'group' => 'email'],
            ['key' => 'mail_username',     'value' => '',                      'group' => 'email'],
            ['key' => 'mail_password',     'value' => '',                      'group' => 'email'],
            ['key' => 'mail_encryption',   'value' => 'tls',                   'group' => 'email'],
            ['key' => 'mail_from_name',    'value' => 'Agenda Kelas Digital',  'group' => 'email'],
            ['key' => 'mail_from_address', 'value' => '',                      'group' => 'email'],

            // Maintenance
            ['key' => 'maintenance_enabled', 'value' => '0',                                                        'group' => 'maintenance'],
            ['key' => 'maintenance_message', 'value' => 'Sistem sedang dalam pemeliharaan. Mohon coba lagi nanti.', 'group' => 'maintenance'],

            // Audit
            ['key' => 'audit_retention_days', 'value' => '365', 'group' => 'audit'],
            ['key' => 'audit_log_login',      'value' => '1',   'group' => 'audit'],
            ['key' => 'audit_log_crud',       'value' => '1',   'group' => 'audit'],
            ['key' => 'audit_log_error',      'value' => '1',   'group' => 'audit'],
        ];

        foreach ($defaults as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't delete settings on rollback to preserve user data
    }
};
