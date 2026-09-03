<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\TeacherStatus;
use App\Services\TeacherStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeacherStatusController extends Controller
{
    public function __construct(private TeacherStatusService $service) {}

    public function index(Request $request)
    {
        $query = TeacherStatus::with(['approver', 'substituteTeacher'])
            ->where('teacher_id', Auth::id());

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(note) LIKE ?', ['%'.mb_strtolower($s).'%'])
                    ->orWhereRaw('CAST(date AS TEXT) LIKE ?', ['%'.mb_strtolower($s).'%']);
            });
        }

        // Filter tipe (izin/tugas_luar) — pakai parameter `type`
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter workflow status (pending/approved/rejected/cancelled)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        $statuses = $query->latest()->paginate(20);

        $summary = [
            'total' => TeacherStatus::where('teacher_id', Auth::id())->count(),
            'pending' => TeacherStatus::where('teacher_id', Auth::id())->pending()->count(),
            'approved' => TeacherStatus::where('teacher_id', Auth::id())->approved()->count(),
            'rejected' => TeacherStatus::where('teacher_id', Auth::id())->rejected()->count(),
        ];

        return view('guru.teacher-status.index', compact('statuses', 'summary'));
    }

    public function create()
    {
        return view('guru.teacher-status.create');
    }

    public function edit(TeacherStatus $teacherStatus)
    {
        abort_if($teacherStatus->teacher_id !== Auth::id(), 403);

        if (! $teacherStatus->isPending()) {
            return redirect()
                ->route('guru.teacher-status.show', $teacherStatus)
                ->with('error', 'Pengajuan hanya dapat diubah saat status masih menunggu.');
        }

        if (Carbon::parse($teacherStatus->effectiveEndDate())->lt(Carbon::today())) {
            return redirect()
                ->route('guru.teacher-status.show', $teacherStatus)
                ->with('error', 'Pengajuan sudah melewati tanggal dan tidak dapat diubah.');
        }

        return view('guru.teacher-status.edit', compact('teacherStatus'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        try {
            $this->service->create(Auth::user(), $data, $request->file('attachment'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('guru.teacher-status.index')
            ->with('success', 'Pengajuan izin/sakit/tugas luar berhasil dikirim dan menunggu persetujuan wakasek.');
    }

    public function show(TeacherStatus $teacherStatus)
    {
        abort_if($teacherStatus->teacher_id !== Auth::id(), 403);

        return view('guru.teacher-status.show', compact('teacherStatus'));
    }

    public function update(Request $request, TeacherStatus $teacherStatus)
    {
        abort_if($teacherStatus->teacher_id !== Auth::id(), 403);

        $data = $this->validatedData($request, $teacherStatus);

        try {
            $this->service->update($teacherStatus, Auth::user(), $data, $request->file('attachment'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('guru.teacher-status.index')
            ->with('success', 'Pengajuan berhasil diperbarui.');
    }

    public function withdraw(TeacherStatus $teacherStatus)
    {
        abort_if($teacherStatus->teacher_id !== Auth::id(), 403);

        try {
            $this->service->withdraw($teacherStatus, Auth::user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Pengajuan berhasil ditarik.');
    }

    public function attachment(TeacherStatus $teacherStatus)
    {
        abort_if($teacherStatus->teacher_id !== Auth::id(), 403);
        abort_if(! $teacherStatus->attachment, 404);

        return Storage::disk('public')->download($teacherStatus->attachment);
    }

    private function validatedData(Request $request, ?TeacherStatus $teacherStatus = null): array
    {
        return $request->validate([
            'type' => ['required', 'in:izin,sakit,tugas_luar'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'date_end' => ['nullable', 'date', 'after_or_equal:date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'note' => ['nullable', 'string', 'max:500'],
            'attachment' => [
                // Surat wajib untuk semua tipe; saat edit hanya wajib bila belum ada.
                Rule::requiredIf(fn () => $teacherStatus === null || $teacherStatus->attachment === null),
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048',
            ],
        ], [
            'date.after_or_equal' => 'Tanggal izin/sakit/tugas luar tidak boleh di masa lalu.',
            'date_end.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'attachment.required' => 'Lampiran surat wajib dilampirkan untuk semua jenis pengajuan (izin, sakit, tugas luar).',
        ]);
    }
}
