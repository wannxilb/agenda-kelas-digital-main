<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\DailyAttendanceSetting;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentDailyAttendanceCorrection;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\DailyAttendancePresensiSync;
use App\Services\PrivateAttendanceMedia;
use App\Support\DailyAttendanceWhatsappNotifier;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DailyAttendanceController extends Controller
{
    public function index()
    {
        /** @var User $student */
        $student = Auth::user();
        $student->load('class');
        $setting = DailyAttendanceSetting::forInstitution($student->institution_id);
        $today = now()->toDateString();
        $attendance = StudentDailyAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        $history = StudentDailyAttendance::where('student_id', $student->id)
            ->latest('date')
            ->take(10)
            ->get();

        $locationEnabled = $this->schoolLocationSetting('enabled', '0', $student->institution_id) === '1';

        $earlyLeaveRequest = StudentEarlyLeaveRequest::where('student_id', $student->id)
            ->where('date', '<=', $today)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$today])
            ->latest()->first();
        $approvedTidakHadirRequest = $this->approvedTidakHadirRequestForDate($student, $today);
        $approvedTidakHadir = (bool) $approvedTidakHadirRequest;
        $scheduledCheckout = $this->scheduledCheckoutTime($student, $setting);
        $isOperationalDay = $this->isOperationalAttendanceDay($student->institution_id);

        return view('siswa.daily-attendance.index', compact('student', 'setting', 'attendance', 'history', 'locationEnabled', 'earlyLeaveRequest', 'approvedTidakHadir', 'approvedTidakHadirRequest', 'scheduledCheckout', 'isOperationalDay'));
    }

    public function checkIn(Request $request)
    {
        $student = Auth::user();
        $setting = DailyAttendanceSetting::forInstitution($student->institution_id);

        if (! $this->isOperationalAttendanceDay($student->institution_id)) {
            return redirect()->back()->with('error', $this->nonOperationalAttendanceMessage());
        }

        $request->validate([
            'photo_data' => [$setting->require_check_in_photo ? 'required' : 'nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'device_fingerprint' => ['nullable', 'string', 'max:128'],
        ], [
            'photo_data.required' => 'Foto absen masuk wajib diambil terlebih dahulu.',
            'latitude.numeric' => 'Data lokasi absen masuk tidak valid. Coba kirim ulang.',
            'longitude.numeric' => 'Data lokasi absen masuk tidak valid. Coba kirim ulang.',
            'accuracy.max' => 'Akurasi lokasi absen masuk tidak valid. Coba kirim ulang.',
        ]);

        $today = now()->toDateString();
        $hasRemoteDispen = StudentEarlyLeaveRequest::hasRemoteApprovedForDate($student->id, $today);
        $approvedTidakHadir = $this->approvedTidakHadirForDate($student, $today);

        if ($approvedTidakHadir) {
            return redirect()->back()->with('error', 'Anda memiliki pengajuan ketidakhadiran yang disetujui untuk hari ini, sehingga tidak dapat melakukan absensi masuk.');
        }

        $location = $this->validateAttendanceLocation($request, $student, $hasRemoteDispen);
        $deviceAudit = $this->attendanceDeviceAudit($request, $student, 'check_in');

        $attendance = StudentDailyAttendance::firstOrNew([
            'student_id' => $student->id,
            'date' => $today,
        ]);

        if ($attendance->exists && $attendance->check_in_at) {
            return redirect()->back()->with('error', 'Anda sudah absen masuk hari ini.');
        }

        $now = now();
        $opening = Carbon::parse($today.' '.$setting->check_in_start);
        $deadline = Carbon::parse($today.' '.$setting->check_in_deadline)
            ->addMinutes((int) $setting->late_tolerance_minutes);
        $verificationDeadline = Carbon::parse($today.' '.$setting->check_in_verification_deadline);
        $checkInCloseAt = $this->checkInCloseAt($student, $setting, $today);

        if ($now->lessThan($opening)) {
            return redirect()->back()->with('error', 'Absensi masuk baru dibuka pukul '.$opening->format('H:i').'.');
        }

        if ($now->greaterThan($checkInCloseAt)) {
            return redirect()->back()->with('error', 'Batas absensi masuk sudah ditutup pukul '.$checkInCloseAt->format('H:i').'.');
        }

        $checkInStatus = 'on_time';
        $lateMinutes = 0;
        if ($now->greaterThan($deadline)) {
            $lateMinutes = (int) ceil($deadline->diffInMinutes($now));
            $checkInStatus = ($hasRemoteDispen || $now->lessThanOrEqualTo($verificationDeadline)) ? 'late' : 'alpha';
        }
        if ($deviceAudit['suspicious'] && ! $hasRemoteDispen) {
            $checkInStatus = 'alpha';
        }
        $photoPath = $this->storeCameraPhoto($request->input('photo_data'), 'check-in', $today);

        $attendance->fill([
            'class_id' => $student->class_id,
            'check_in_at' => $now,
            'check_in_photo' => $photoPath,
            'check_in_latitude' => $location['latitude'],
            'check_in_longitude' => $location['longitude'],
            'check_in_accuracy' => $location['accuracy'],
            'check_in_distance_meters' => $location['distance_meters'],
            'check_in_ip_address' => $deviceAudit['ip_address'],
            'check_in_user_agent' => $deviceAudit['user_agent'],
            'check_in_device_fingerprint' => $deviceAudit['device_fingerprint'],
            'check_in_suspicious' => $deviceAudit['suspicious'],
            'check_in_suspicious_reason' => $deviceAudit['suspicious_reason'],
            'check_in_status' => $checkInStatus,
            'verification_method' => $hasRemoteDispen ? 'approved_dispen' : ($deviceAudit['suspicious'] ? 'device_review' : null),
            'late_minutes' => $lateMinutes,
            'institution_id' => $student->institution_id,
        ])->save();

        app(DailyAttendancePresensiSync::class)->sync($attendance);
        app(DailyAttendanceWhatsappNotifier::class)->sendIfEligible($student, $attendance, $setting, 'check_in');

        return redirect()->back()->with(
            'success',
            $checkInStatus === 'alpha'
                ? 'Absensi masuk tercatat dengan status alpha. Wali kelas dapat mengubahnya jika ada alasan khusus.'
                : ($hasRemoteDispen
                    ? 'Absensi masuk berhasil disimpan (dispensasi disetujui).'
                    : ($deviceAudit['suspicious']
                        ? 'Absensi masuk tercatat dengan status alpha karena perangkat mencurigakan. Wali kelas dapat mengubahnya jika ada alasan khusus.'
                        : 'Absensi masuk berhasil disimpan.'))
        );
    }

    public function checkOut(Request $request)
    {
        $student = Auth::user();
        $setting = DailyAttendanceSetting::forInstitution($student->institution_id);

        if (! $this->isOperationalAttendanceDay($student->institution_id)) {
            return redirect()->back()->with('error', $this->nonOperationalAttendanceMessage());
        }

        $request->validate([
            'photo_data' => [$setting->require_check_out_photo ? 'required' : 'nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'device_fingerprint' => ['nullable', 'string', 'max:128'],
        ], [
            'photo_data.required' => 'Foto absen pulang wajib diambil terlebih dahulu.',
            'latitude.numeric' => 'Data lokasi absen pulang tidak valid. Coba kirim ulang.',
            'longitude.numeric' => 'Data lokasi absen pulang tidak valid. Coba kirim ulang.',
            'accuracy.max' => 'Akurasi lokasi absen pulang tidak valid. Coba kirim ulang.',
        ]);

        $today = now()->toDateString();
        $approvedTidakHadir = $this->approvedTidakHadirForDate($student, $today);

        if ($approvedTidakHadir) {
            return redirect()->back()->with('error', 'Anda memiliki pengajuan ketidakhadiran yang disetujui untuk hari ini, sehingga tidak dapat melakukan absensi pulang.');
        }

        $hasRemoteDispen = StudentEarlyLeaveRequest::hasRemoteApprovedForDate($student->id, $today);

        $location = $this->validateAttendanceLocation($request, $student, $hasRemoteDispen);
        $deviceAudit = $this->attendanceDeviceAudit($request, $student, 'check_out');

        $attendance = StudentDailyAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in_at) {
            return redirect()->back()->with('error', 'Anda belum melakukan absensi masuk hari ini.');
        }

        if ($attendance->check_out_at) {
            return redirect()->back()->with('error', 'Anda sudah absen pulang hari ini.');
        }

        $now = now();
        $start = Carbon::parse($today.' '.$this->checkoutStartTime($student, $setting));
        $checkOutCloseAt = $this->checkoutCloseAt($student, $setting);
        $toleranceCutoff = $start->copy()->subMinutes((int) $setting->check_out_tolerance_minutes);
        $earlyMinutes = $now->lessThan($start) ? (int) ceil($now->diffInMinutes($start)) : 0;

        $approvedRequest = StudentEarlyLeaveRequest::where('student_id', $student->id)
            ->where('status', 'approved')
            ->where('date', '<=', $today)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$today])
            ->latest()
            ->first();

        if ($now->lessThan($toleranceCutoff) && ! $approvedRequest) {
            return redirect()->back()->with('error', 'Absensi pulang terlalu awal. Ajukan izin dan tunggu persetujuan wali kelas.');
        }

        if (! $approvedRequest && $now->greaterThan($checkOutCloseAt)) {
            return redirect()->back()->with('error', 'Batas absensi pulang sudah ditutup pukul '.$checkOutCloseAt->format('H:i').'.');
        }

        $photoPath = $this->storeCameraPhoto($request->input('photo_data'), 'check-out', $today);
        $isEarly = $now->lessThan($start);
        $checkoutStatus = $approvedRequest?->isActivityCategory()
            ? 'additional_activity'
            : ($approvedRequest && $isEarly ? 'early_with_permission' : 'checked_out');
        if ($deviceAudit['suspicious']) {
            $checkoutStatus = 'alpha';
        }

        $attendance->update([
            'class_id' => $student->class_id,
            'check_out_at' => $now,
            'check_out_photo' => $photoPath,
            'check_out_latitude' => $location['latitude'],
            'check_out_longitude' => $location['longitude'],
            'check_out_accuracy' => $location['accuracy'],
            'check_out_distance_meters' => $location['distance_meters'],
            'check_out_ip_address' => $deviceAudit['ip_address'],
            'check_out_user_agent' => $deviceAudit['user_agent'],
            'check_out_device_fingerprint' => $deviceAudit['device_fingerprint'],
            'check_out_suspicious' => $deviceAudit['suspicious'],
            'check_out_suspicious_reason' => $deviceAudit['suspicious_reason'],
            'check_out_status' => $checkoutStatus,
            'check_out_verification_method' => $deviceAudit['suspicious'] ? 'device_review' : null,
            'early_leave_minutes' => $earlyMinutes,
            'institution_id' => $student->institution_id,
        ]);

        app(DailyAttendancePresensiSync::class)->sync($attendance->fresh());
        app(DailyAttendanceWhatsappNotifier::class)->sendIfEligible($student, $attendance, $setting, 'check_out');

        return redirect()->back()->with(
            'success',
            $checkoutStatus === 'alpha'
                ? 'Absensi pulang tercatat dengan status alpha karena perangkat mencurigakan. Wali kelas dapat mengubahnya jika ada alasan khusus.'
                : 'Absensi pulang berhasil disimpan.'
        );
    }

    public function requestEarlyLeave(Request $request, PrivateAttendanceMedia $media)
    {
        $student = Auth::user();

        if (! $this->isOperationalAttendanceDay($student->institution_id)) {
            return redirect()->back()->with('error', $this->nonOperationalAttendanceMessage());
        }

        $validated = $request->validate([
            'category' => ['required', 'in:'.implode(',', array_keys(StudentEarlyLeaveRequest::allowedCategories()))],
            'reason' => ['required', 'string', 'max:1000'],
            'date' => ['required', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date'],
            'activity_name' => ['required_if:category,pulang_kegiatan,kegiatan_tambahan', 'nullable', 'string', 'max:255'],
            'activity_start_time' => ['nullable', 'date_format:H:i'],
            'activity_end_time' => ['nullable', 'date_format:H:i'],
            'evidence' => [
                Rule::requiredIf(fn () => StudentEarlyLeaveRequest::requiresEvidenceFor($request->input('category'))),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:4096',
            ],
        ], [
            'activity_name.required_if' => 'Nama kegiatan / acara wajib diisi untuk kategori ini.',
            'evidence.required' => 'Bukti pendukung wajib diunggah untuk jenis pengajuan ini.',
        ]);

        $category = $validated['category'];
        $date = $validated['date'];

        if ($error = $this->earlyLeaveValidationError($category, $date)) {
            return redirect()->back()->withInput()->withErrors([
                'date' => $error,
            ]);
        }

        if ($error = $this->activityTimeValidationError($validated['activity_start_time'] ?? null, $validated['activity_end_time'] ?? null)) {
            return redirect()->back()->withInput()->withErrors([
                'activity_end_time' => $error,
            ]);
        }

        $today = now()->toDateString();
        $categoryModel = new StudentEarlyLeaveRequest(['category' => $category]);
        $categoryDefinition = StudentEarlyLeaveRequest::categoryDefinition($category);
        $needsCheckIn = $date === $today && (bool) ($categoryDefinition['needs_check_in'] ?? true);
        $attendance = StudentDailyAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if ($needsCheckIn && ! $attendance?->check_in_at) {
            return redirect()->back()->with('error', 'Anda harus melakukan absensi masuk terlebih dahulu sebelum mengajukan izin / dispensasi untuk hari ini.');
        }

        $existing = StudentEarlyLeaveRequest::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get()
            ->contains(fn (StudentEarlyLeaveRequest $request) => $request->coversDate($date));
        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan izin / dispensasi (menunggu atau disetujui) yang mencakup tanggal tersebut.');
        }

        StudentEarlyLeaveRequest::create([
            'student_id' => $student->id,
            'class_id' => $student->class_id,
            'institution_id' => $student->institution_id,
            'date' => $date,
            'date_end' => $categoryModel->isSingleDayCategory() ? null : ($validated['date_end'] ?? null),
            'category' => $category,
            'reason' => $validated['reason'],
            'activity_name' => $validated['activity_name'] ?? null,
            'activity_start_time' => $validated['activity_start_time'] ?? null,
            'activity_end_time' => $validated['activity_end_time'] ?? null,
            'evidence_path' => $request->hasFile('evidence') ? $media->store($request->file('evidence')->get(), 'early-leave-evidence/'.$student->id, 'enc') : null,
            'evidence_mime' => $request->file('evidence')?->getMimeType(),
        ]);

        return redirect()->back()->with('success', 'Pengajuan izin / dispensasi dikirim ke wali kelas.');
    }

    public function cancelEarlyLeave(Request $request, StudentEarlyLeaveRequest $earlyLeaveRequest)
    {
        abort_unless((int) $earlyLeaveRequest->student_id === (int) Auth::id(), 403);

        if ($earlyLeaveRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan izin / dispensasi sudah diproses dan tidak dapat dibatalkan.');
        }

        $earlyLeaveRequest->update(['status' => 'cancelled']);

        AuditLogger::log('early_leave_cancel', 'student_early_leave_requests', $earlyLeaveRequest->id, $earlyLeaveRequest->institution_id, null, [
            'category' => $earlyLeaveRequest->category,
            'reason' => $earlyLeaveRequest->reason,
        ]);

        return redirect()->back()->with('success', 'Pengajuan izin / dispensasi berhasil dibatalkan.');
    }

    public function earlyLeaveHistory()
    {
        $student = Auth::user();

        $requests = StudentEarlyLeaveRequest::where('student_id', $student->id)
            ->latest('created_at')
            ->get();

        return view('siswa.daily-attendance.early-leave-history', compact('student', 'requests'));
    }

    public function updateEarlyLeave(Request $request, StudentEarlyLeaveRequest $earlyLeaveRequest, PrivateAttendanceMedia $media)
    {
        abort_unless((int) $earlyLeaveRequest->student_id === (int) Auth::id(), 403);

        if ($earlyLeaveRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan izin / dispensasi sudah diproses dan tidak dapat diubah.');
        }

        $student = Auth::user();

        $validated = $request->validate([
            'category' => ['required', 'in:'.implode(',', array_keys(StudentEarlyLeaveRequest::allowedCategories()))],
            'reason' => ['required', 'string', 'max:1000'],
            'date' => ['required', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date'],
            'activity_name' => ['required_if:category,pulang_kegiatan,kegiatan_tambahan', 'nullable', 'string', 'max:255'],
            'activity_start_time' => ['nullable', 'date_format:H:i'],
            'activity_end_time' => ['nullable', 'date_format:H:i'],
            'evidence' => [
                Rule::requiredIf(fn () => StudentEarlyLeaveRequest::requiresEvidenceFor($request->input('category')) && ! $earlyLeaveRequest->evidence_path),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:4096',
            ],
        ], [
            'activity_name.required_if' => 'Nama kegiatan / acara wajib diisi untuk kategori ini.',
            'evidence.required' => 'Bukti pendukung wajib diunggah untuk jenis pengajuan ini.',
        ]);

        $category = $validated['category'];
        $date = $validated['date'];

        if ($error = $this->earlyLeaveValidationError($category, $date)) {
            return redirect()->back()->withInput()->withErrors([
                'date' => $error,
            ]);
        }

        if ($error = $this->activityTimeValidationError($validated['activity_start_time'] ?? null, $validated['activity_end_time'] ?? null)) {
            return redirect()->back()->withInput()->withErrors([
                'activity_end_time' => $error,
            ]);
        }

        $today = now()->toDateString();
        $categoryModel = new StudentEarlyLeaveRequest(['category' => $category]);
        $categoryDefinition = StudentEarlyLeaveRequest::categoryDefinition($category);
        $needsCheckIn = $date === $today && (bool) ($categoryDefinition['needs_check_in'] ?? true);
        $attendance = StudentDailyAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if ($needsCheckIn && ! $attendance?->check_in_at) {
            return redirect()->back()->with('error', 'Anda harus melakukan absensi masuk terlebih dahulu sebelum mengajukan izin / dispensasi untuk hari ini.');
        }

        $existing = StudentEarlyLeaveRequest::where('student_id', $student->id)
            ->whereKeyNot($earlyLeaveRequest->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get()
            ->contains(fn (StudentEarlyLeaveRequest $existingRequest) => $existingRequest->coversDate($date));
        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan izin / dispensasi (menunggu atau disetujui) yang mencakup tanggal tersebut.');
        }

        $oldEvidencePath = $earlyLeaveRequest->evidence_path;
        $newEvidencePath = null;
        $newEvidenceMime = null;
        if ($request->hasFile('evidence')) {
            $newEvidencePath = $media->store($request->file('evidence')->get(), 'early-leave-evidence/'.$student->id, 'enc');
            $newEvidenceMime = $request->file('evidence')->getMimeType();
        }

        $earlyLeaveRequest->update([
            'date' => $date,
            'date_end' => $categoryModel->isSingleDayCategory() ? null : ($validated['date_end'] ?? null),
            'category' => $category,
            'reason' => $validated['reason'],
            'activity_name' => $validated['activity_name'] ?? null,
            'activity_start_time' => $validated['activity_start_time'] ?? null,
            'activity_end_time' => $validated['activity_end_time'] ?? null,
            'evidence_path' => $newEvidencePath ?? $earlyLeaveRequest->evidence_path,
            'evidence_mime' => $newEvidenceMime ?? $earlyLeaveRequest->evidence_mime,
        ]);

        if ($newEvidencePath && $oldEvidencePath && $oldEvidencePath !== $newEvidencePath) {
            $media->delete($oldEvidencePath);
        }

        AuditLogger::log('early_leave_update', 'student_early_leave_requests', $earlyLeaveRequest->id, $earlyLeaveRequest->institution_id, null, [
            'category' => $category,
            'reason' => $validated['reason'],
        ]);

        return redirect()->back()->with('success', 'Pengajuan izin / dispensasi berhasil diperbarui.');
    }

    private function earlyLeaveValidationError(string $category, string $date): ?string
    {
        $today = now()->toDateString();
        $categoryModel = new StudentEarlyLeaveRequest(['category' => $category]);

        if ($categoryModel->isSingleDayCategory() && $date !== $today) {
            return 'Kategori ini hanya dapat diajukan untuk hari ini.';
        }

        if ($category === 'sakit' && $date < now()->subDays(7)->toDateString()) {
            return 'Pengajuan sakit maksimal untuk 7 hari terakhir.';
        }

        $maxFutureDate = now()->addDays(StudentEarlyLeaveRequest::MAX_FUTURE_REQUEST_DAYS);
        if ($date > $maxFutureDate->toDateString()) {
            return 'Pengajuan izin / dispensasi maksimal untuk tanggal '.$maxFutureDate->translatedFormat('d M Y').' ('.StudentEarlyLeaveRequest::MAX_FUTURE_REQUEST_DAYS.' hari ke depan).';
        }

        return null;
    }

    private function activityTimeValidationError(?string $startTime, ?string $endTime): ?string
    {
        if (! $startTime || ! $endTime) {
            return null;
        }

        if (strtotime($endTime) <= strtotime($startTime)) {
            return 'Jam selesai kegiatan harus setelah jam mulai.';
        }

        return null;
    }

    public function requestCorrection(Request $request, StudentDailyAttendance $attendance, PrivateAttendanceMedia $media)
    {
        abort_unless((int) $attendance->student_id === (int) Auth::id(), 403);

        $validated = $request->validate([
            'target_event' => ['required', 'in:check_in,check_out'],
            'requested_status' => ['required', 'in:izin_kegiatan,sakit,izin_lainnya'],
            'reason' => ['required', 'string', 'max:1000'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $hasPending = $attendance->corrections()
            ->where('target_event', $validated['target_event'])
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return redirect()->back()->with('error', 'Pengajuan koreksi untuk bagian ini masih menunggu verifikasi.');
        }

        if (($validated['target_event'] === 'check_in' && ! $attendance->check_in_at)
            || ($validated['target_event'] === 'check_out' && ! $attendance->check_out_at)) {
            return redirect()->back()->with('error', 'Bagian absensi yang dikoreksi belum memiliki data asli.');
        }

        $evidencePath = $request->hasFile('evidence')
            ? $media->store($request->file('evidence')->get(), 'correction-evidence/'.Auth::id(), 'enc')
            : null;

        StudentDailyAttendanceCorrection::create([
            'student_daily_attendance_id' => $attendance->id,
            'student_id' => Auth::id(),
            'institution_id' => Auth::user()->institution_id,
            'target_event' => $validated['target_event'],
            'requested_status' => $validated['requested_status'],
            'reason' => $validated['reason'],
            'evidence_path' => $evidencePath,
            'evidence_mime' => $request->file('evidence')?->getMimeType(),
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Pengajuan koreksi berhasil dikirim untuk verifikasi wali kelas atau admin.');
    }

    private function storeCameraPhoto(?string $photoData, string $type, string $date): ?string
    {
        if (! $photoData) {
            return null;
        }

        if (! preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $photoData)) {
            $this->redirectBackWithError('Foto kamera tidak valid.');
        }

        $payload = preg_replace('/^data:image\/(jpeg|jpg|png|webp);base64,/', '', $photoData);
        $binary = base64_decode($payload, true);

        if ($binary === false || strlen($binary) > 4 * 1024 * 1024) {
            $this->redirectBackWithError('Ukuran foto maksimal 4 MB.');
        }

        $path = app(PrivateAttendanceMedia::class)->store($binary, 'student-attendances/'.$type.'/'.$date, 'enc');

        return $path;
    }

    private function validateAttendanceLocation(Request $request, User $student, bool $bypassRadius = false): array
    {
        $locationEnabled = $this->schoolLocationSetting('enabled', '0', $student->institution_id) === '1';
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $accuracy = $request->input('accuracy');

        if (! $locationEnabled) {
            return [
                'latitude' => is_numeric($latitude) ? (float) $latitude : null,
                'longitude' => is_numeric($longitude) ? (float) $longitude : null,
                'accuracy' => is_numeric($accuracy) ? (float) $accuracy : null,
                'distance_meters' => null,
            ];
        }

        $centerLatitude = $this->schoolLocationSetting('latitude', null, $student->institution_id);
        $centerLongitude = $this->schoolLocationSetting('longitude', null, $student->institution_id);
        $radius = (float) $this->schoolLocationSetting('radius_meters', 100, $student->institution_id);

        if (! is_numeric($centerLatitude) || ! is_numeric($centerLongitude) || $radius <= 0) {
            $this->redirectBackWithError('Lokasi sekolah belum dikonfigurasi oleh Admin.');
        }

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            $this->redirectBackWithError('Aktifkan izin lokasi untuk melakukan absensi.');
        }

        $distance = $this->distanceInMeters(
            (float) $centerLatitude,
            (float) $centerLongitude,
            (float) $latitude,
            (float) $longitude
        );

        if ($distance > $radius && ! $bypassRadius) {
            $this->redirectBackWithError('Absensi hanya dapat dilakukan di dalam area sekolah.');
        }

        return [
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
            'accuracy' => is_numeric($accuracy) ? (float) $accuracy : null,
            'distance_meters' => round($distance, 2),
        ];
    }

    private function attendanceDeviceAudit(Request $request, User $student, string $event): array
    {
        $fingerprint = trim((string) $request->input('device_fingerprint'));
        $fingerprint = $fingerprint !== '' ? $fingerprint : null;
        $ipAddress = $request->ip();
        $userAgent = substr((string) $request->userAgent(), 0, 1000);
        $today = now()->toDateString();
        $fingerprintColumn = $event.'_device_fingerprint';
        $reasons = [];

        if ($fingerprint) {
            $previousFingerprint = StudentDailyAttendance::withoutGlobalScope('academic_year')
                ->where('student_id', $student->id)
                ->whereDate('date', '<', $today)
                ->whereNotNull($fingerprintColumn)
                ->latest('date')
                ->value($fingerprintColumn);

            if ($previousFingerprint && $previousFingerprint !== $fingerprint) {
                $reasons[] = 'Perangkat berbeda dari absensi sebelumnya.';
            }

            $sameDeviceOtherStudent = StudentDailyAttendance::withoutGlobalScope('academic_year')
                ->whereDate('date', $today)
                ->where('student_id', '!=', $student->id)
                ->where($fingerprintColumn, $fingerprint)
                ->exists();

            if ($sameDeviceOtherStudent) {
                $reasons[] = 'Perangkat yang sama digunakan oleh siswa lain hari ini.';
            }
        } else {
            $reasons[] = 'Fingerprint perangkat tidak terkirim.';
        }

        return [
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_fingerprint' => $fingerprint,
            'suspicious' => count($reasons) > 0,
            'suspicious_reason' => $reasons ? implode(' ', $reasons) : null,
        ];
    }

    private function distanceInMeters(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
    {
        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($latitude2 - $latitude1);
        $longitudeDelta = deg2rad($longitude2 - $longitude1);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($longitudeDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function isOperationalAttendanceDay(?int $institutionId): bool
    {
        $overrideUntil = Setting::get('operational_override_until', null, $institutionId);
        if ($overrideUntil && Carbon::parse($overrideUntil)->isFuture()) {
            return true;
        }

        $operationalDays = array_filter(explode(',', Setting::get('operational_days', '1,2,3,4,5', $institutionId)));

        return in_array((string) now()->dayOfWeekIso, $operationalDays, true);
    }

    private function scheduledCheckoutTime(User $student, DailyAttendanceSetting $setting): string
    {
        return $this->checkoutStartTime($student, $setting);
    }

    private function checkInCloseAt(User $student, DailyAttendanceSetting $setting, string $date): Carbon
    {
        $close = Carbon::parse($date.' '.Setting::get('operational_end_time', '16:00', $student->institution_id));
        $open = Carbon::parse($date.' '.$setting->check_in_start);

        if ($open->greaterThanOrEqualTo($close)) {
            $close = Carbon::parse($date.' 23:59:59');
        }

        return $close;
    }

    private function checkoutStartTime(User $student, DailyAttendanceSetting $setting): string
    {
        $today = now()->toDateString();

        if ($setting->check_out_override_date && $setting->check_out_override_time
            && Carbon::parse($setting->check_out_override_date)->toDateString() === $today) {
            return $setting->check_out_override_time;
        }

        return $setting->check_out_start;
    }

    private function checkoutCloseAt(User $student, DailyAttendanceSetting $setting): Carbon
    {
        $date = now()->toDateString();
        $close = Carbon::parse($date.' '.Setting::get('operational_end_time', '16:00', $student->institution_id));
        $start = Carbon::parse($date.' '.$this->checkoutStartTime($student, $setting));

        if ($start->greaterThanOrEqualTo($close)) {
            $close = $start->copy()->addMinutes(30);
        }

        return $close;
    }

    private function nonOperationalAttendanceMessage(): string
    {
        return 'Absensi harian hanya dapat dilakukan pada hari operasional sekolah.';
    }

    private function schoolLocationSetting(string $key, mixed $default = null, ?int $institutionId = null): mixed
    {
        return Setting::get(
            'school_location_'.$key,
            Setting::get('agenda_location_'.$key, $default, $institutionId),
            $institutionId
        );
    }

    private function redirectBackWithError(string $message): never
    {
        throw new HttpResponseException(redirect()->back()->withInput()->with('error', $message));
    }

    private function approvedTidakHadirForDate(User $student, string $date): bool
    {
        return (bool) $this->approvedTidakHadirRequestForDate($student, $date);
    }

    private function approvedTidakHadirRequestForDate(User $student, string $date): ?StudentEarlyLeaveRequest
    {
        return StudentEarlyLeaveRequest::where('student_id', $student->id)
            ->where('status', 'approved')
            ->where('date', '<=', $date)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$date])
            ->get()
            ->first(fn (StudentEarlyLeaveRequest $request) => $request->isTidakHadirCategory());
    }
}
