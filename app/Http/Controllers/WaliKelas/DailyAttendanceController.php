<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\DailyAttendanceSetting;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentDailyAttendanceCorrection;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\User;
use App\Models\WhatsappNotificationLog;
use App\Services\AttendanceCorrectionApplier;
use App\Services\AuditLogger;
use App\Services\DailyAttendancePresensiSync;
use App\Services\StudentLeaveFinalStatusSync;
use App\Support\DailyAttendanceWhatsappNotifier;
use App\Traits\ResolvesWaliKelasContext;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DailyAttendanceController extends Controller
{
    use ResolvesWaliKelasContext;

    public function index(Request $request)
    {
        $context = $this->waliKelasContext($request);
        $class = $context['selectedClass'];

        if (! $class) {
            return view('daily-attendance.monitor', array_merge($context, [
                'layout' => 'layouts.walikelas',
                'title' => 'Absensi Harian Siswa',
                'date' => $request->query('date', now()->toDateString()),
                'records' => collect(),
                'class' => null,
                'showClass' => false,
                'emptyText' => 'Anda belum ditugaskan sebagai wali kelas.',
            ]));
        }

        $date = $request->query('date', now()->toDateString());
        $records = StudentDailyAttendance::withoutGlobalScope('academic_year')
            ->where('class_id', $class->id)
            ->whereDate('date', $date)
            ->with(['student', 'class', 'whatsappLogs', 'corrections.reviewer', 'earlyLeaveRequests.reviewer'])
            ->orderByRaw('check_in_at IS NULL, check_in_at ASC')
            ->get();
        $recordsByStudent = $records->keyBy('student_id');
        $earlyLeaveRequestsByStudent = StudentEarlyLeaveRequest::where('class_id', $class->id)
            ->where('date', '<=', $date)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$date])
            ->with('reviewer')
            ->get()
            ->groupBy('student_id');

        $absentWaLogsByStudent = WhatsappNotificationLog::whereIn('student_id', $class->students->pluck('id'))
            ->where('event_type', 'absent')
            ->whereDate('created_at', $date)
            ->latest()
            ->get()
            ->groupBy('student_id');

        $records = $class->students->map(function ($student) use ($recordsByStudent, $earlyLeaveRequestsByStudent, $class, $date) {
            $record = $recordsByStudent->get($student->id);
            if ($record) {
                $record->setRelation('earlyLeaveRequests', $earlyLeaveRequestsByStudent->get($student->id, collect())->values());

                return $record;
            }

            $record = new StudentDailyAttendance([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'date' => $date,
            ]);
            $record->setRelation('student', $student);
            $record->setRelation('class', $class);
            $record->setRelation('whatsappLogs', collect());
            $record->setRelation('corrections', collect());
            $record->setRelation('earlyLeaveRequests', $earlyLeaveRequestsByStudent->get($student->id, collect())->values());

            return $record;
        });

        return view('daily-attendance.monitor', array_merge($context, [
            'layout' => 'layouts.walikelas',
            'title' => 'Absensi Harian Siswa',
            'date' => $date,
            'records' => $records,
            'class' => $class,
            'showClass' => false,
            'canVerify' => true,
            'absentWaLogsByStudent' => $absentWaLogsByStudent,
            'emptyText' => 'Belum ada absensi harian untuk kelas binaan pada tanggal ini.',
        ]));
    }

    public function verify(Request $request, StudentDailyAttendance $attendance)
    {
        $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'verification_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureOwnActiveClass($attendance->class_id);

        $isCheckInVerification = $attendance->check_in_status === 'needs_verification' || $attendance->check_in_status === 'alpha';
        $isCheckOutVerification = ! $isCheckInVerification && ($attendance->check_out_status === 'needs_verification' || $attendance->check_out_status === 'alpha');

        if (! $isCheckInVerification && ! $isCheckOutVerification) {
            return redirect()->back()->with('error', 'Absensi ini tidak dapat diubah.');
        }

        if ($attendance->check_in_status === 'alpha' && $request->decision === 'reject') {
            return redirect()->back()->with('error', 'Siswa sudah berstatus alpha. Penolakan tidak diperlukan.');
        }

        if ($request->decision === 'reject' && ! $request->filled('verification_note')) {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        if ($isCheckInVerification) {
            if ($attendance->check_in_status === 'alpha') {
                if ($request->decision === 'reject') {
                    return redirect()->back()->with('error', 'Siswa sudah berstatus alpha. Penolakan tidak diperlukan.');
                }

                $verificationMethod = $attendance->check_in_suspicious ? 'device_review' : 'late_review';

                $attendance->update([
                    'check_in_status' => 'teacher_verified',
                    'verification_method' => $verificationMethod,
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                    'verification_note' => $request->input('verification_note'),
                    'overridden_by' => Auth::id(),
                    'overridden_at' => now(),
                ]);

                if ($attendance->check_in_suspicious) {
                    AuditLogger::log(
                        'suspicious_device_override',
                        'student_daily_attendances',
                        $attendance->id,
                        $attendance->institution_id,
                        ['check_in_suspicious_reason' => $attendance->check_in_suspicious_reason],
                        ['overridden_by' => Auth::id(), 'verification_note' => $request->input('verification_note')]
                    );
                }
            } else {
                $verificationMethod = $attendance->check_in_suspicious ? 'device_review' : 'late_review';

                $attendance->update([
                    'check_in_status' => $request->decision === 'approve' ? 'teacher_verified' : 'teacher_rejected',
                    'verification_method' => $verificationMethod,
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                    'verification_note' => $request->input('verification_note'),
                ]);

                if ($attendance->check_in_suspicious && $request->decision === 'approve') {
                    AuditLogger::log(
                        'suspicious_device_override',
                        'student_daily_attendances',
                        $attendance->id,
                        $attendance->institution_id,
                        ['check_in_suspicious_reason' => $attendance->check_in_suspicious_reason],
                        ['overridden_by' => Auth::id(), 'verification_note' => $request->input('verification_note')]
                    );
                }
            }
        } else {
            $attendance->update([
                'check_out_status' => $request->decision === 'approve' ? 'teacher_verified' : 'teacher_rejected',
                'check_out_verification_method' => $attendance->check_out_suspicious ? 'device_review' : 'checkout_review',
                'check_out_verified_by' => Auth::id(),
                'check_out_verified_at' => now(),
                'check_out_verification_note' => $request->input('verification_note'),
            ]);
        }

        app(DailyAttendancePresensiSync::class)->sync($attendance->refresh());

        if ($request->decision === 'approve') {
            $student = $attendance->student()->firstOrFail();
            $setting = DailyAttendanceSetting::forInstitution($student->institution_id);
            app(DailyAttendanceWhatsappNotifier::class)->sendIfEligible($student, $attendance, $setting, $isCheckInVerification ? 'check_in' : 'check_out');
        }

        return redirect()->back()->with('success', $request->decision === 'approve'
            ? 'Absensi '.($isCheckInVerification ? 'masuk' : 'pulang').' disetujui.'
            : 'Absensi '.($isCheckInVerification ? 'masuk' : 'pulang').' ditolak.');
    }

    public function confirmDispen(Request $request, StudentEarlyLeaveRequest $earlyLeaveRequest)
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = $request->input('date');

        if ($date > now()->toDateString()) {
            return redirect()->back()->with('error', 'Tanggal yang dikonfirmasi masih di masa depan.');
        }

        if ($earlyLeaveRequest->status !== 'approved' || ! $earlyLeaveRequest->isDispenCategory()) {
            return redirect()->back()->with('error', 'Pengajuan ini bukan dispensasi yang disetujui.');
        }

        if (! $earlyLeaveRequest->coversDate($date)) {
            return redirect()->back()->with('error', 'Pengajuan tidak mencakup tanggal tersebut.');
        }

        $this->ensureOwnActiveClassForStudent($earlyLeaveRequest->student_id);

        $attendance = StudentDailyAttendance::withoutGlobalScope('academic_year')->firstOrNew([
            'student_id' => $earlyLeaveRequest->student_id,
            'date' => $date,
        ]);

        if ($attendance->check_in_at) {
            return redirect()->back()->with('error', 'Siswa sudah memiliki absen masuk pada tanggal tersebut.');
        }

        $verifiedAt = now();
        $attendance->fill([
            'class_id' => $earlyLeaveRequest->class_id,
            'institution_id' => $earlyLeaveRequest->institution_id ?: $earlyLeaveRequest->student?->institution_id,
            'check_in_at' => $verifiedAt,
            'check_in_status' => 'additional_activity',
            'verification_method' => 'approved_dispen_confirmation',
            'verified_by' => Auth::id(),
            'verified_at' => $verifiedAt,
            'verification_note' => 'Dikonfirmasi wali kelas (dispen tanpa bukti absen siswa)',
        ])->save();

        $final = Attendance::withoutGlobalScope('academic_year')->firstOrNew([
            'student_id' => $earlyLeaveRequest->student_id,
            'date' => $date,
        ]);

        $final->fill([
            'class_id' => $earlyLeaveRequest->class_id,
            'status' => 'present',
            'note' => trim('Sinkron dari pengajuan '.$earlyLeaveRequest->categoryLabel().': '.$earlyLeaveRequest->reason.' (Dikonfirmasi wali kelas)'),
            'check_in_time' => $verifiedAt->format('H:i:s'),
            'institution_id' => $earlyLeaveRequest->institution_id ?: $earlyLeaveRequest->student?->institution_id,
            'source' => 'manual',
        ])->save();

        AuditLogger::log('dispen_confirmed', 'attendances', $final->id, $final->institution_id, null, [
            'student_early_leave_request_id' => $earlyLeaveRequest->id,
            'date' => $date,
        ]);

        return redirect()->back()->with('success', 'Kehadiran siswa pada tanggal '.$date.' dikonfirmasi hadir.');
    }

    public function assist(Request $request)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('institution_id', Auth::user()->institution_id)),
            ],
            'date' => ['required', 'date_format:Y-m-d', 'date_equals:'.now()->toDateString()],
            'verification_note' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ], [
            'date.date_equals' => 'Bantuan absensi hanya dapat dicatat untuk hari ini.',
        ]);

        $class = $this->ensureOwnActiveClassForStudent((int) $validated['student_id']);
        $student = $class->students()->whereKey($validated['student_id'])->firstOrFail();

        if (! $this->isOperationalAttendanceDay($student->institution_id, $validated['date'])) {
            return redirect()->back()->with('error', $this->nonOperationalAttendanceMessage());
        }

        if ($this->approvedTidakHadirForDate($student, $validated['date'])) {
            return redirect()->back()->with('error', 'Siswa memiliki pengajuan ketidakhadiran yang disetujui untuk hari ini, sehingga wali kelas tidak dapat membantu absensi masuk.');
        }

        $setting = DailyAttendanceSetting::forInstitution($student->institution_id);
        $this->ensureAssistedCheckInIsOpen($setting, $validated['date']);

        $attendance = StudentDailyAttendance::withoutGlobalScope('academic_year')->firstOrNew([
            'student_id' => $student->id,
            'date' => $validated['date'],
        ]);

        if ($attendance->check_in_at) {
            return redirect()->back()->with('error', 'Siswa tersebut sudah memiliki absensi masuk.');
        }

        $location = $this->validateTeacherLocation($validated, $student);
        $attendance->fill([
            'class_id' => $class->id,
            'institution_id' => $student->institution_id,
            'check_in_at' => now(),
            'check_in_status' => 'teacher_verified',
            'verification_method' => 'teacher_assisted',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_note' => $validated['verification_note'],
            'check_in_latitude' => $location['latitude'],
            'check_in_longitude' => $location['longitude'],
            'check_in_accuracy' => $location['accuracy'],
            'check_in_distance_meters' => $location['distance_meters'],
        ])->save();

        app(DailyAttendancePresensiSync::class)->sync($attendance);
        app(DailyAttendanceWhatsappNotifier::class)->sendIfEligible($student, $attendance, $setting, 'check_in');

        return redirect()->back()->with('success', 'Absensi bantuan berhasil dicatat.');
    }

    public function assistCheckout(Request $request)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('institution_id', Auth::user()->institution_id)),
            ],
            'date' => ['required', 'date_format:Y-m-d', 'date_equals:'.now()->toDateString()],
            'verification_note' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ], [
            'date.date_equals' => 'Bantuan absensi pulang hanya dapat dicatat untuk hari ini.',
        ]);

        $class = $this->ensureOwnActiveClassForStudent((int) $validated['student_id']);
        $student = $class->students()->whereKey($validated['student_id'])->firstOrFail();

        if (! $this->isOperationalAttendanceDay($student->institution_id, $validated['date'])) {
            return redirect()->back()->with('error', $this->nonOperationalAttendanceMessage());
        }

        if ($this->approvedTidakHadirForDate($student, $validated['date'])) {
            return redirect()->back()->with('error', 'Siswa memiliki pengajuan ketidakhadiran yang disetujui untuk hari ini, sehingga wali kelas tidak dapat membantu absensi pulang.');
        }

        $setting = DailyAttendanceSetting::forInstitution($student->institution_id);
        $this->ensureAssistedCheckOutIsOpen($setting, $validated['date']);

        $attendance = StudentDailyAttendance::withoutGlobalScope('academic_year')->firstOrNew([
            'student_id' => $student->id,
            'date' => $validated['date'],
        ]);

        if (! $attendance->check_in_at) {
            return redirect()->back()->with('error', 'Siswa tersebut belum melakukan absensi masuk.');
        }

        if ($attendance->check_out_at) {
            return redirect()->back()->with('error', 'Siswa tersebut sudah memiliki absensi pulang.');
        }

        $location = $this->validateTeacherLocation($validated, $student);
        $attendance->fill([
            'class_id' => $class->id,
            'institution_id' => $student->institution_id,
            'check_out_at' => now(),
            'check_out_status' => 'teacher_assisted',
            'check_out_verification_method' => 'teacher_assisted',
            'check_out_verified_by' => Auth::id(),
            'check_out_verified_at' => now(),
            'check_out_verification_note' => $validated['verification_note'],
            'check_out_latitude' => $location['latitude'],
            'check_out_longitude' => $location['longitude'],
            'check_out_accuracy' => $location['accuracy'],
            'check_out_distance_meters' => $location['distance_meters'],
        ])->save();

        app(DailyAttendancePresensiSync::class)->sync($attendance);
        app(DailyAttendanceWhatsappNotifier::class)->sendIfEligible($student, $attendance, $setting, 'check_out');

        return redirect()->back()->with('success', 'Absensi pulang bantuan berhasil dicatat.');
    }

    public function retryWhatsapp(WhatsappNotificationLog $log)
    {
        $attendance = $log->studentDailyAttendance()->first();
        if ($attendance) {
            $this->ensureOwnActiveClass($attendance->class_id);
        } else {
            $student = $log->student()->first();
            if ($student) {
                $this->ensureOwnActiveClass($student->class_id);
            }
        }

        if ($log->status !== 'failed') {
            return redirect()->back()->with('error', 'Notifikasi ini tidak sedang berstatus gagal.');
        }

        app(DailyAttendanceWhatsappNotifier::class)->retry($log);

        return redirect()->back()->with('success', 'Pengiriman WhatsApp diulang. Periksa statusnya setelah beberapa saat.');
    }

    public function reviewCorrection(Request $request, StudentDailyAttendanceCorrection $correction)
    {
        $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'reviewer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $attendance = $correction->attendance()->firstOrFail();
        $this->ensureOwnActiveClass($attendance->class_id);

        if ($correction->status !== 'pending') {
            return redirect()->back()->with('error', 'Koreksi ini sudah diproses.');
        }

        if ($request->decision === 'reject' && ! $request->filled('reviewer_note')) {
            return redirect()->back()->with('error', 'Alasan penolakan koreksi wajib diisi.');
        }

        $oldValues = [
            'target_event' => $correction->target_event,
            'original_status' => $correction->target_event === 'check_in' ? $attendance->check_in_status : $attendance->check_out_status,
            'original_time' => $correction->target_event === 'check_in' ? $attendance->check_in_at?->toIso8601String() : $attendance->check_out_at?->toIso8601String(),
        ];
        $decision = $request->decision === 'approve' ? 'approved' : 'rejected';

        if ($decision === 'approved') {
            app(AttendanceCorrectionApplier::class)->apply($correction);
        }

        $newValues = [
            'decision' => $request->decision,
            'requested_status' => $correction->requested_status,
            'reason' => $correction->reason,
            'reviewer_note' => $request->input('reviewer_note'),
            'original_attendance_preserved' => $decision !== 'approved',
        ];

        $correction->update([
            'status' => $decision,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'reviewer_note' => $request->input('reviewer_note'),
        ]);

        AuditLogger::log(
            'daily_attendance_correction_'.$request->decision,
            'student_daily_attendance_corrections',
            $correction->id,
            $attendance->institution_id,
            $oldValues,
            $newValues
        );

        return redirect()->back()->with('success', 'Koreksi absensi berhasil '.($request->decision === 'approve' ? 'disetujui.' : 'ditolak.'));
    }

    public function reviewEarlyLeave(Request $request, StudentEarlyLeaveRequest $earlyLeaveRequest)
    {
        $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'reviewer_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->ensureOwnActiveClass($earlyLeaveRequest->class_id);
        if ($earlyLeaveRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan izin / dispensasi sudah diproses.');
        }
        if ($request->decision === 'reject' && ! $request->filled('reviewer_note')) {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        $this->applyEarlyLeaveDecision($earlyLeaveRequest, $request->decision, $request->input('reviewer_note'));

        return redirect()->back()->with('success', 'Pengajuan izin / dispensasi berhasil diproses.');
    }

    public function earlyLeaveIndex(Request $request)
    {
        $context = $this->waliKelasContext($request);
        $class = $context['selectedClass'];

        if (! $class) {
            return view('walikelas.daily-attendance.early-leave-index', array_merge($context, [
                'requests' => new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 25),
                'classes' => collect(),
                'pendingCount' => 0,
            ]));
        }

        $classIds = collect([$class->id]);

        $query = StudentEarlyLeaveRequest::with(['student', 'class', 'reviewer'])
            ->whereIn('class_id', $classIds)
            ->latest('date');

        $query->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')));
        $query->when($request->filled('group'), function ($query) use ($request) {
            $query->whereIn('category', StudentEarlyLeaveRequest::categoriesForGroup($request->input('group')));
        });
        $query->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->input('class_id')));
        $query->when($request->filled('from'), fn ($query) => $query->whereDate('date', '>=', $request->input('from')));
        $query->when($request->filled('to'), fn ($query) => $query->whereDate('date', '<=', $request->input('to')));

        $requests = $query->paginate(25)->withQueryString();
        $pendingCount = StudentEarlyLeaveRequest::whereIn('class_id', $classIds)
            ->where('status', 'pending')
            ->count();

        $classes = collect([$class]);

        return view('walikelas.daily-attendance.early-leave-index', compact('requests', 'classes', 'pendingCount'));
    }

    public function batchReviewEarlyLeave(Request $request)
    {
        $request->validate([
            'request_ids' => ['required'],
            'decision' => ['required', 'in:approve,reject'],
            'reviewer_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'request_ids.required' => 'Pilih minimal satu pengajuan terlebih dahulu.',
        ]);

        if ($request->decision === 'reject' && ! $request->filled('reviewer_note')) {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        $ids = is_array($request->input('request_ids'))
            ? $request->input('request_ids')
            : array_values(array_filter(array_map('trim', explode(',', (string) $request->input('request_ids')))));

        $processed = 0;
        $skipped = 0;

        foreach ($ids as $id) {
            $earlyLeaveRequest = StudentEarlyLeaveRequest::find($id);
            if (! $earlyLeaveRequest || $earlyLeaveRequest->status !== 'pending') {
                $skipped++;

                continue;
            }

            try {
                $this->ensureOwnActiveClass($earlyLeaveRequest->class_id);
            } catch (\Throwable $e) {
                $skipped++;

                continue;
            }

            $this->applyEarlyLeaveDecision($earlyLeaveRequest, $request->decision, $request->input('reviewer_note'));
            $processed++;
        }

        $verb = $request->decision === 'approve' ? 'disetujui' : 'ditolak';
        $message = $processed.' pengajuan berhasil '.$verb.'.';
        if ($skipped > 0) {
            $message .= ' '.$skipped.' pengajuan dilewati (sudah diproses / bukan kelas Anda).';
        }

        return redirect()->back()->with('success', $message);
    }

    private function applyEarlyLeaveDecision(StudentEarlyLeaveRequest $earlyLeaveRequest, string $decision, ?string $reviewerNote): void
    {
        $earlyLeaveRequest->update([
            'status' => $decision === 'approve' ? 'approved' : 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'reviewer_note' => $reviewerNote,
        ]);

        if ($decision === 'approve') {
            app(StudentLeaveFinalStatusSync::class)->syncApproved($earlyLeaveRequest);
            $this->recordApprovedEarlyLeaveCheckout($earlyLeaveRequest);
        }

        app(DailyAttendanceWhatsappNotifier::class)->sendEarlyLeaveDecision(
            $earlyLeaveRequest,
            $decision === 'approve' ? 'approved' : 'rejected'
        );

        AuditLogger::log('early_leave_'.$decision, 'student_early_leave_requests', $earlyLeaveRequest->id, $earlyLeaveRequest->institution_id, null, [
            'category' => $earlyLeaveRequest->category,
            'reason' => $earlyLeaveRequest->reason,
            'decision' => $decision,
            'reviewer_note' => $reviewerNote,
        ]);
    }

    private function recordApprovedEarlyLeaveCheckout(StudentEarlyLeaveRequest $earlyLeaveRequest): void
    {
        if (! $earlyLeaveRequest->isActivityCategory()) {
            return;
        }

        $today = now()->toDateString();
        if (! $earlyLeaveRequest->coversDate($today)) {
            return;
        }

        $student = $earlyLeaveRequest->student()->firstOrFail();
        $attendance = StudentDailyAttendance::withoutGlobalScope('academic_year')->firstOrNew([
            'student_id' => $earlyLeaveRequest->student_id,
            'date' => $today,
        ]);

        if ($attendance->check_out_at) {
            return;
        }

        $setting = DailyAttendanceSetting::forInstitution($earlyLeaveRequest->institution_id ?? $student->institution_id);
        $scheduledCheckout = Carbon::parse($today.' '.$this->checkoutStartTime($setting, $today));
        $checkoutAt = now()->lessThan($scheduledCheckout) ? $scheduledCheckout : now();

        $attendance->fill([
            'class_id' => $earlyLeaveRequest->class_id,
            'institution_id' => $earlyLeaveRequest->institution_id ?? $student->institution_id,
            'check_out_at' => $checkoutAt,
            'check_out_status' => 'additional_activity',
            'check_out_verification_method' => 'early_leave_approval',
            'check_out_verified_by' => Auth::id(),
            'check_out_verified_at' => $checkoutAt,
            'check_out_verification_note' => $earlyLeaveRequest->reviewer_note ?: $earlyLeaveRequest->reason,
        ])->save();

        app(DailyAttendancePresensiSync::class)->sync($attendance);
        app(DailyAttendanceWhatsappNotifier::class)->sendIfEligible($student, $attendance, $setting, 'check_out');
    }

    private function ensureOwnActiveClass(int $classId): Classes
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->classes()->whereKey($classId)->firstOrFail();
    }

    private function ensureOwnActiveClassForStudent(int $studentId): Classes
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->classes()->whereHas('students', fn ($query) => $query->whereKey($studentId))->firstOrFail();
    }

    private function approvedTidakHadirForDate(User $student, string $date): bool
    {
        return StudentEarlyLeaveRequest::where('student_id', $student->id)
            ->where('status', 'approved')
            ->where('date', '<=', $date)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$date])
            ->get()
            ->contains(fn (StudentEarlyLeaveRequest $request) => $request->isTidakHadirCategory());
    }

    private function validateTeacherLocation(array $data, User $student): array
    {
        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];
        $accuracy = isset($data['accuracy']) ? (float) $data['accuracy'] : null;
        $enabled = $this->schoolLocationSetting('enabled', '0', $student->institution_id) === '1';

        if (! $enabled) {
            return compact('latitude', 'longitude', 'accuracy') + ['distance_meters' => null];
        }

        $centerLatitude = $this->schoolLocationSetting('latitude', null, $student->institution_id);
        $centerLongitude = $this->schoolLocationSetting('longitude', null, $student->institution_id);
        $radius = (float) $this->schoolLocationSetting('radius_meters', 100, $student->institution_id);

        if (! is_numeric($centerLatitude) || ! is_numeric($centerLongitude) || $radius <= 0) {
            $this->redirectBackWithError('Absensi bantuan hanya dapat dilakukan di area sekolah.');
        }

        $distance = $this->distanceInMeters((float) $centerLatitude, (float) $centerLongitude, $latitude, $longitude);

        if ($distance > $radius) {
            $this->redirectBackWithError('Absensi bantuan hanya dapat dilakukan di area sekolah.');
        }

        return compact('latitude', 'longitude', 'accuracy') + ['distance_meters' => round($distance, 2)];
    }

    private function ensureAssistedCheckInIsOpen(DailyAttendanceSetting $setting, string $date): void
    {
        $opening = Carbon::parse($date.' '.$setting->check_in_start);

        if (now()->lessThan($opening)) {
            $this->redirectBackWithError('Absensi masuk baru dibuka pukul '.$opening->format('H:i').'.');
        }
    }

    private function ensureAssistedCheckOutIsOpen(DailyAttendanceSetting $setting, string $date): void
    {
        $opening = Carbon::parse($date.' '.$this->checkoutStartTime($setting, $date))
            ->subMinutes((int) $setting->check_out_tolerance_minutes);

        if (now()->lessThan($opening)) {
            $this->redirectBackWithError('Absensi pulang baru dibuka pukul '.$opening->format('H:i').'.');
        }
    }

    private function checkoutStartTime(DailyAttendanceSetting $setting, string $date): string
    {
        if ($setting->check_out_override_date && $setting->check_out_override_time
            && Carbon::parse($setting->check_out_override_date)->toDateString() === $date) {
            return $setting->check_out_override_time;
        }

        return $setting->check_out_start;
    }

    private function redirectBackWithError(string $message): never
    {
        throw new HttpResponseException(redirect()->back()->withInput()->with('error', $message));
    }

    private function schoolLocationSetting(string $key, mixed $default = null, ?int $institutionId = null): mixed
    {
        return Setting::get(
            'school_location_'.$key,
            Setting::get('agenda_location_'.$key, $default, $institutionId),
            $institutionId
        );
    }

    private function isOperationalAttendanceDay(?int $institutionId, string $date): bool
    {
        $overrideUntil = Setting::get('operational_override_until', null, $institutionId);
        if ($overrideUntil && Carbon::parse($overrideUntil)->isFuture()) {
            return true;
        }

        $operationalDays = array_filter(explode(',', Setting::get('operational_days', '1,2,3,4,5', $institutionId)));

        return in_array((string) Carbon::parse($date)->dayOfWeekIso, $operationalDays, true);
    }

    private function nonOperationalAttendanceMessage(): string
    {
        return 'Absensi harian hanya dapat dilakukan pada hari operasional sekolah.';
    }

    private function distanceInMeters(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
    {
        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($latitude2 - $latitude1);
        $longitudeDelta = deg2rad($longitude2 - $longitude1);
        $a = sin($latitudeDelta / 2) ** 2 + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($longitudeDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
