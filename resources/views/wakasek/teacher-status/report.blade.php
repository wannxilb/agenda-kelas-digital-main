{{-- resources/views/wakasek/teacher-status/report.blade.php --}}
@extends('layouts.wakasek')

@section('title', 'Rekap Izin / Sakit / Tugas Luar')
@section('header', 'Rekap Izin / Sakit / Tugas Luar')

@section('content')
    @include('teacher-status._report-content', [
        'rows' => $rows,
        'month' => $month,
        'year' => $year,
        'reportUrl' => route('wakasek.teacher-status.report'),
        'csvUrl' => route('wakasek.teacher-status.report.csv', ['month' => $month, 'year' => $year]),
        'pdfUrl' => route('wakasek.teacher-status.report.pdf', ['month' => $month, 'year' => $year]),
    ])
@endsection
