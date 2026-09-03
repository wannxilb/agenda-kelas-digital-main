<?php

namespace App\Http\Controllers\Wakasek;

use App\Exports\TeacherStatusReportExport;
use App\Http\Controllers\Controller;
use App\Models\TeacherStatus;
use App\Models\User;
use App\Services\TeacherStatusService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class TeacherStatusController extends Controller
{
    public function __construct(private TeacherStatusService $service) {}

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['pending', 'approved', 'all'], true)
            ? $request->query('tab')
            : 'pending';

        $query = TeacherStatus::with(['teacher', 'approver', 'substituteTeacher']);

        if ($tab !== 'all') {
            $query->where('status', $tab);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date')) {
            $date = Carbon::parse($request->date)->toDateString();
            $query->where('date', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->whereNull('date_end')->orWhere('date_end', '>=', $date);
                });
        }

        $range = $request->query('range');
        if (in_array($range, ['today', 'week', 'month'], true)) {
            $start = match ($range) {
                'today' => Carbon::today(),
                'week' => Carbon::today()->startOfWeek(),
                'month' => Carbon::today()->startOfMonth(),
            };
            $query->where('date', '>=', $start->toDateString());
        }

        $statuses = $query->latest()->paginate(15)->withQueryString();

        $pendingCount = TeacherStatus::pending()->count();
        $approvedCount = TeacherStatus::approved()->count();
        $allCount = TeacherStatus::count();

        $teachers = User::role('teacher')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('wakasek.teacher-status.index', compact(
            'statuses',
            'tab',
            'pendingCount',
            'approvedCount',
            'allCount',
            'teachers'
        ));
    }

    public function show(TeacherStatus $teacherStatus)
    {
        $teacherStatus->load(['teacher', 'approver', 'substituteTeacher', 'cancelledBy']);

        $teachers = User::role('teacher')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('wakasek.teacher-status.show', compact('teacherStatus', 'teachers'));
    }

    public function approve(Request $request, TeacherStatus $teacherStatus)
    {
        try {
            $this->service->approve(
                $teacherStatus,
                $request->user(),
                $request->input('substitute_teacher_id') ?: null
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Pengajuan disetujui.');
    }

    public function reject(Request $request, TeacherStatus $teacherStatus)
    {
        try {
            $this->service->reject($teacherStatus, $request->user(), (string) $request->input('rejection_reason', ''));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Pengajuan ditolak.');
    }

    public function cancel(Request $request, TeacherStatus $teacherStatus)
    {
        try {
            $this->service->cancel($teacherStatus, $request->user(), (string) $request->input('cancellation_reason', ''));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Pengajuan dibatalkan.');
    }

    public function attachment(TeacherStatus $teacherStatus)
    {
        abort_if(! $teacherStatus->attachment, 404);

        return Storage::disk('public')->download($teacherStatus->attachment);
    }

    public function report(Request $request)
    {
        [$rows, $month, $year] = $this->reportData($request);

        return view('wakasek.teacher-status.report', compact('rows', 'month', 'year'));
    }

    public function exportCsv(Request $request)
    {
        [$rows, $month, $year] = $this->reportData($request);

        $filename = sprintf('rekap-izin-tugas-luar-%s-%02d.csv', $year, $month);

        return Excel::download(new TeacherStatusReportExport($rows), $filename);
    }

    public function exportPdf(Request $request)
    {
        [$rows, $month, $year] = $this->reportData($request);

        $monthName = Carbon::create()->month($month)->translatedFormat('F');
        $totalIzin = $rows->sum('izin_days');
        $totalSakit = $rows->sum('sakit_days');
        $totalTugas = $rows->sum('tugas_days');
        $totalDays = $rows->sum('total_days');

        $pdf = Pdf::loadView('teacher-status.report-pdf', compact(
            'rows',
            'month',
            'year',
            'monthName',
            'totalIzin',
            'totalSakit',
            'totalTugas',
            'totalDays'
        ));

        $filename = sprintf('rekap-izin-tugas-luar-%s-%02d.pdf', $year, $month);

        return $pdf->download($filename);
    }

    /**
     * Ambil data rekap bulanan (approved) — dipakai halaman report & ekspor.
     * Logika rekap dipegang TeacherStatusService::monthlyReport() agar wakasek
     * & admin memakai satu sumber data yang sama.
     *
     * @return array{0: Collection, 1: int, 2: int} [rows, month, year]
     */
    private function reportData(Request $request): array
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        return [$this->service->monthlyReport($month, $year), $month, $year];
    }
}
