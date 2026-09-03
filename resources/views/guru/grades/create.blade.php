@extends('layouts.guru')

@section('title', 'Tambah Nilai Tugas')
@section('header', 'Tambah Nilai Tugas')
@section('content-padding', 'py-6 sm:py-8')

@section('content')
@include('guru.grades.partials.form', [
    'action' => route('guru.grades.store'),
    'method' => 'POST',
    'submitLabel' => 'Simpan Nilai',
    'assignment' => null,
    'gradeMap' => collect(),
])
@endsection
