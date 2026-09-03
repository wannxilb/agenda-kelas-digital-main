@extends('layouts.guru')

@section('title', 'Edit Nilai Tugas')
@section('header', 'Edit Nilai Tugas')
@section('content-padding', 'py-6 sm:py-8')

@section('content')
@include('guru.grades.partials.form', [
    'action' => route('guru.grades.update', $grade),
    'method' => 'PUT',
    'submitLabel' => 'Simpan Perubahan',
    'assignment' => $grade,
])
@endsection
