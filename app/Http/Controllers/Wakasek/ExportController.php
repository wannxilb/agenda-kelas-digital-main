<?php

namespace App\Http\Controllers\Wakasek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\AcademicYear;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeachingReportExport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExportController extends Controller
{
    use AuthorizesRequests;
    public function exportTeaching()
    {
        $this->authorize('view', User::class);

        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[$date] = date('F Y', strtotime($date));
        }

        return view('wakasek.export.teaching', compact('months'));
    }

    public function exportTeachingExcel(Request $request)
    {
        $this->authorize('view', User::class);

        $month = $request->month ?? date('Y-m');
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $activeYear = AcademicYear::where('is_active', true)->first();

        $query = User::role('teacher')
            ->with('subjects')
            ->withCount('agendas')
            ->with(['agendas' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function($teacher) {
                return (object) [
                    'nip' => $teacher->nip,
                    'name' => $teacher->name,
                    'agendas_count' => $teacher->agendas_count,
                    'latest_agenda_date' => $teacher->agendas->sortByDesc('date')->first()?->date,
                    'subjects' => $teacher->subjects,
                ];
            });

        $data = ['teachers' => $query];

        $filename = 'rekap-jurnal-guru-' . $month . '.xlsx';
        return Excel::download(new TeachingReportExport($data), $filename);
    }
}
