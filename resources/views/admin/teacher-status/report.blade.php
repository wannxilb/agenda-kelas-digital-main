{{-- resources/views/admin/teacher-status/report.blade.php --}}
@extends('layouts.admin')

@section('title', 'Rekap Izin / Sakit / Tugas Luar')
@section('header', 'Rekap Izin / Sakit / Tugas Luar')

@section('content')
    @include('teacher-status._report-content', [
        'rows' => $rows,
        'month' => $month,
        'year' => $year,
        'reportUrl' => route('admin.teacher-status.report'),
        'csvUrl' => route('admin.teacher-status.report.csv', ['month' => $month, 'year' => $year]),
        'pdfUrl' => route('admin.teacher-status.report.pdf', ['month' => $month, 'year' => $year]),
    ])
@endsection
