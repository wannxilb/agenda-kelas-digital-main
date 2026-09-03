<?php

namespace App\Exports;

use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SchedulesTemplateExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithMapping, WithStyles
{
    public function collection()
    {
        $institutionId = Auth::user()?->institution_id;
        return Schedule::withoutGlobalScopes()
            ->where('institution_id', $institutionId)
            ->with(['class', 'subject', 'teacher', 'room_model'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }

    public function map($schedule): array
    {
        $dayNames = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
        ];

        return [
            $schedule->class?->name ?? '',
            $schedule->subject?->name ?? '',
            $schedule->teacher?->name ?? '',
            $schedule->teacher?->nip ?? '',
            $dayNames[$schedule->day] ?? $schedule->day,
            $schedule->week_type ?? 'semua',
            Carbon::parse($schedule->start_time)->format('H:i'),
            Carbon::parse($schedule->end_time)->format('H:i'),
            $schedule->room_model?->name ?? ($schedule->room ?? ''),
        ];
    }

    public function headings(): array
    {
        return [
            'Kelas',
            'Mata Pelajaran',
            'Guru Pengampu',
            'NIP Guru',
            'Hari',
            'Tipe Minggu',
            'Jam Mulai',
            'Jam Selesai',
            'Ruangan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '4F46E5']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Jadwal Pelajaran';
    }
}
