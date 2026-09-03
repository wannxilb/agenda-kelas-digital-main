<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function index()
    {
        $allYears = AcademicYear::orderBy('name', 'desc')->orderBy('semester', 'asc')->get();

        // Group by year name so Ganjil & Genap appear in one row
        $grouped = $allYears->groupBy('name')->map(function ($items, $name) {
            return [
                'name'   => $name,
                'ganjil' => $items->firstWhere('semester', 'Ganjil'),
                'genap'  => $items->firstWhere('semester', 'Genap'),
            ];
        })->values();

        return view('admin.academic_years.index', ['groupedYears' => $grouped]);
    }

    public function store(Request $request)
    {
        if ($request->has('name')) {
            $request->merge(['name' => trim($request->name)]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'semester' => 'required|in:Ganjil,Genap',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'Tanggal Selesai harus sama atau setelah Tanggal Mulai.',
        ]);

        // Cek kombinasi unik name + semester secara manual agar pesan error lebih spesifik
        $exists = AcademicYear::where('name', $request->name)
            ->where('semester', $request->semester)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['name' => 'Kombinasi Tahun Ajaran dan Semester ini sudah ada.'])
                ->withInput();
        }

        AcademicYear::create([
            'name' => $request->name,
            'semester' => $request->semester,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'institution_id' => $request->user()?->institution_id,
        ]);

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Ajaran berhasil ditambahkan.');
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        if ($request->has('name')) {
            $request->merge(['name' => trim($request->name)]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'semester' => 'required|in:Ganjil,Genap',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'Tanggal Selesai harus sama atau setelah Tanggal Mulai.',
        ]);

        $exists = AcademicYear::where('name', $request->name)
            ->where('semester', $request->semester)
            ->where('id', '!=', $academicYear->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['name' => 'Kombinasi Tahun Ajaran dan Semester ini sudah ada.'])
                ->withInput();
        }

        $academicYear->update([
            'name' => $request->name,
            'semester' => $request->semester,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->is_active) {
            return redirect()->route('admin.academic-years.index')->with('error', 'Tidak dapat menghapus Tahun Ajaran yang sedang aktif.');
        }

        $academicYear->delete();
        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Ajaran berhasil dihapus.');
    }

    public function setActive(AcademicYear $academicYear)
    {
        $institutionId = auth()->user()?->institution_id;

        DB::transaction(function () use ($academicYear, $institutionId) {
            AcademicYear::where('institution_id', $institutionId)->update(['is_active' => false]);
            $academicYear->update(['is_active' => true]);

            // Sinkronkan dengan tabel settings untuk konsistensi UI
            \App\Models\Setting::set('academic_year', $academicYear->name);
            \App\Models\Setting::set('semester', $academicYear->semester);
        });

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Ajaran ' . $academicYear->name . ' (' . $academicYear->semester . ') berhasil diaktifkan.');
    }
}
