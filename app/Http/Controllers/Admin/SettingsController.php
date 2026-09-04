<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $institutionId = Auth::user()?->institution_id;
        $settings = Setting::allForInstitution();
        $academicYears = AcademicYear::select('name')->distinct()->orderBy('name', 'desc')->get();
        $institution = Institution::find($institutionId);

        return view('admin.settings', compact('settings', 'academicYears', 'institution'));
    }

    public function update(Request $request)
    {
        $institutionId = Auth::user()?->institution_id;

        $request->validate([
            'school_name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'school_address' => 'nullable|string|max:500',
            'operational_start_time' => 'required|date_format:H:i',
            'operational_end_time' => 'required|date_format:H:i',
            'operational_days' => 'required|array',
            'operational_days.*' => 'string',
            'operational_override_until' => 'nullable|date',
            'school_location_enabled' => 'nullable|boolean',
            'school_location_latitude' => 'nullable|required_if:school_location_enabled,1|numeric|between:-90,90',
            'school_location_longitude' => 'nullable|required_if:school_location_enabled,1|numeric|between:-180,180',
            'school_location_radius_meters' => 'nullable|required_if:school_location_enabled,1|integer|min:10|max:10000',
            'schedule_time_slots' => ['nullable', 'string', 'max:2000', function ($attribute, $value, $fail) {
                if (! is_string($value) || trim($value) === '') {
                    return;
                }
                foreach (preg_split('/\r?\n/', $value) as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $time = trim(explode('|', $line, 2)[0]);
                    if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
                        $fail("Format jam '{$time}' tidak valid. Gunakan format HH:MM, satu jam per baris.");
                    }
                }
            }],
            'recurring_activities' => ['nullable', 'string', 'max:10000', function ($attribute, $value, $fail) {
                if (! is_string($value) || trim($value) === '') {
                    return;
                }
                $decoded = json_decode($value, true);
                if (! is_array($decoded)) {
                    $fail('Format kegiatan rutin tidak valid.');
                    return;
                }
                $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                foreach ($decoded as $i => $item) {
                    if (empty($item['name']) || empty($item['start_time']) || empty($item['duration']) || empty($item['days'])) {
                        $fail("Kegiatan rutin ke-" . ($i + 1) . " tidak lengkap.");
                        return;
                    }
                    if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $item['start_time'])) {
                        $fail("Format jam kegiatan '{$item['name']}' tidak valid.");
                        return;
                    }
                    if (! is_numeric($item['duration']) || $item['duration'] < 5 || $item['duration'] > 240) {
                        $fail("Durasi kegiatan '{$item['name']}' harus antara 5-240 menit.");
                        return;
                    }
                    if (! is_array($item['days']) || count(array_diff($item['days'], $validDays)) > 0) {
                        $fail("Hari kegiatan '{$item['name']}' tidak valid.");
                        return;
                    }
                }
            }],
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,ico,svg|max:1024',
        ]);

        $data = $request->only([
            'school_name', 'academic_year', 'semester', 'school_address',
            'operational_start_time', 'operational_end_time', 'operational_days',
            'operational_override_until', 'school_location_enabled',
            'school_location_latitude', 'school_location_longitude',
            'school_location_radius_meters', 'schedule_time_slots',
            'recurring_activities',
        ]);
        $data['operational_days'] = implode(',', $request->operational_days);
        $data['school_location_enabled'] = $request->boolean('school_location_enabled') ? '1' : '0';
        if (array_key_exists('schedule_time_slots', $data)) {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $data['schedule_time_slots']))));
            $data['schedule_time_slots'] = implode("\n", $lines);
        }
        if (array_key_exists('recurring_activities', $data)) {
            $raw = trim((string) $data['recurring_activities']);
            $decoded = json_decode($raw, true);
            $data['recurring_activities'] = is_array($decoded) ? json_encode($decoded) : '[]';
        }

        foreach ($data as $key => $value) {
            if (in_array($key, ['logo', 'favicon'])) {
                continue;
            }
            Setting::set($key, $value);
        }

        $institution = Institution::find($institutionId);
        if ($institution) {
            if ($request->hasFile('logo')) {
                if ($institution->logo) {
                    Storage::disk('public')->delete($institution->logo);
                }
                $institution->logo = $request->file('logo')->store('institutions', 'public');
            }
            if ($request->hasFile('favicon')) {
                if ($institution->favicon) {
                    Storage::disk('public')->delete($institution->favicon);
                }
                $institution->favicon = $request->file('favicon')->store('institutions', 'public');
            }
            $institution->save();
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}
