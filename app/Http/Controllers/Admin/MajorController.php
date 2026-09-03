<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MajorController extends Controller
{
    public function index()
    {
        $majors = Major::with('gradeTemplates')->orderBy('name')->get();
        return view('admin.majors.index', compact('majors'));
    }

    public function create()
    {
        return view('admin.majors.create');
    }

    public function store(Request $request)
    {
        $institutionId = auth()->user()->institution_id;

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('majors', 'name')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'code' => 'nullable|string|max:20',
            'grades' => 'required|array',
            'grades.*.code' => 'required|string|max:20',
            'grades.*.rombel_count' => 'required|integer|min:1|max:20',
        ]);

        $major = Major::create([
            'name' => $request->name,
            'code' => $request->code,
            'is_active' => $request->boolean('is_active'),
            'institution_id' => $institutionId,
        ]);

        $this->syncGradeTemplates($major, $request->grades);

        return redirect()->route('admin.majors.index')->with('success', 'Jurusan berhasil ditambahkan!');
    }

    public function edit(Major $major)
    {
        $major->load('gradeTemplates');
        return view('admin.majors.edit', compact('major'));
    }

    public function update(Request $request, Major $major)
    {
        $institutionId = auth()->user()->institution_id;

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('majors', 'name')->ignore($major->id)->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'code' => 'nullable|string|max:20',
            'grades' => 'required|array',
            'grades.*.code' => 'required|string|max:20',
            'grades.*.rombel_count' => 'required|integer|min:1|max:20',
        ]);

        $major->update([
            'name' => $request->name,
            'code' => $request->code,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncGradeTemplates($major, $request->grades);

        return redirect()->route('admin.majors.index')->with('success', 'Jurusan berhasil diperbarui!');
    }

    public function destroy(Major $major)
    {
        $major->delete();
        return redirect()->route('admin.majors.index')->with('success', 'Jurusan berhasil dihapus!');
    }

    private function syncGradeTemplates(Major $major, array $grades)
    {
        $templates = [];
        foreach ($grades as $gradeLevel => $data) {
            $templates[] = [
                'grade_level' => $gradeLevel,
                'code' => $data['code'],
                'rombel_count' => $data['rombel_count'],
            ];
        }

        $major->gradeTemplates()->delete();
        foreach ($templates as $template) {
            $major->gradeTemplates()->create($template);
        }
    }
}
