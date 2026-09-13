<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyAttendanceSetting;
use App\Services\WhatsappNotificationHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DailyAttendanceSettingController extends Controller
{
    public function edit()
    {
        $setting = DailyAttendanceSetting::forInstitution(Auth::user()->institution_id);

        $setting->check_in_message_template = $setting->check_in_message_template
            ?: 'Ananda {student} telah masuk sekolah pukul {time}. Status: {status}.';
        $setting->check_out_message_template = $setting->check_out_message_template
            ?: 'Ananda {student} telah pulang sekolah pukul {time}. Status: {status}.';
        $setting->absent_message_template = $setting->absent_message_template
            ?: 'Ananda {student} ({class}) tidak tercatat hadir di sekolah hari ini, {date}. Jika anak berhalangan, mohon sampaikan keterangan ke wali kelas.';

        $health = app(WhatsappNotificationHealth::class)->overview(Auth::user()->institution_id);

        return view('admin.daily-attendance.settings', compact('setting', 'health'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'check_in_start' => ['required', 'date_format:H:i', 'before:check_in_deadline'],
            'check_in_deadline' => ['required', 'date_format:H:i'],
            'check_in_verification_deadline' => ['required', 'date_format:H:i', 'after:check_in_deadline'],
            'check_out_start' => ['required', 'date_format:H:i'],
            'check_out_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:60'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'require_check_in_photo' => ['nullable', 'boolean'],
            'require_check_out_photo' => ['nullable', 'boolean'],
            'whatsapp_enabled' => ['nullable', 'boolean'],
            'whatsapp_mode' => ['nullable', Rule::in(['lengkap', 'hemat', 'hanya_absen'])],
            'whatsapp_provider' => ['nullable', Rule::in(['fonnte'])],
            'whatsapp_api_url' => ['nullable', 'url', 'max:255'],
            'whatsapp_token' => ['nullable', 'string', 'max:500'],
            'whatsapp_country_code' => ['nullable', 'string', 'max:5'],
            'check_in_message_template' => ['nullable', 'string', 'max:1000'],
            'check_out_message_template' => ['nullable', 'string', 'max:1000'],
            'absent_message_template' => ['nullable', 'string', 'max:1000'],
        ]);

        $setting = DailyAttendanceSetting::forInstitution(Auth::user()->institution_id);
        $setting->update([
            'check_in_start' => $validated['check_in_start'],
            'check_in_deadline' => $validated['check_in_deadline'],
            'check_in_verification_deadline' => $validated['check_in_verification_deadline'],
            'check_out_start' => $validated['check_out_start'],
            'check_out_tolerance_minutes' => $validated['check_out_tolerance_minutes'],
            'late_tolerance_minutes' => $validated['late_tolerance_minutes'],
            'require_check_in_photo' => $request->boolean('require_check_in_photo'),
            'require_check_out_photo' => $request->boolean('require_check_out_photo'),
            'whatsapp_enabled' => $request->boolean('whatsapp_enabled'),
            'whatsapp_mode' => $validated['whatsapp_mode'] ?? 'lengkap',
            'whatsapp_provider' => $validated['whatsapp_provider'] ?? 'fonnte',
            'whatsapp_api_url' => $validated['whatsapp_api_url'] ?? 'https://api.fonnte.com/send',
            'whatsapp_token' => $request->filled('whatsapp_token') ? $validated['whatsapp_token'] : $setting->whatsapp_token,
            'whatsapp_country_code' => $validated['whatsapp_country_code'] ?? '62',
            'check_in_message_template' => $validated['check_in_message_template'] ?? null,
            'check_out_message_template' => $validated['check_out_message_template'] ?? null,
            'absent_message_template' => $validated['absent_message_template'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Pengaturan absensi harian berhasil disimpan.');
    }
}
