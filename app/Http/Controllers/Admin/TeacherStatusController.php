<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TeacherStatusReportExport;
use App\Http\Controllers\Controller;
use App\Services\TeacherStatusService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Rekap bulanan izin/tugas luar guru untuk role admin (desain §8).
 * Semua logika rekap dipakai bersama dengan wakasek via TeacherStatusService.
 */
class TeacherStatusController extends Controller
{
    public function __construct(private TeacherStatusService $service) {}

    public function report(Request $request)
    {
        [$rows, $month, $year] = $this->reportData($request);

        return view('admin.teacher-status.report', compact('rows', 'month', 'year'));
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
     * @return array{0: Collection, 1: int, 2: int} [rows, month, year]
     */
    private function reportData(Request $request): array
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        return [$this->service->monthlyReport($month, $year), $month, $year];
    }
}
