<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\AcademicYear;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();

        $academicYears = AcademicYear::query()
            ->orderBy('name', 'desc')
            ->get();

        $academicYearId = $this->resolveAcademicYearId($request);
        $search = $request->query('search');
        $status = $request->query('status');
        $date = $request->query('date');

        if (!in_array($status, ['published', 'draft'], true)) {
            $status = null;
        }

        $query = Agenda::withoutGlobalScope('academic_year')
            ->where('teacher_id', $teacher->id)
            ->with(['class', 'subject', 'teacher']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($date) {
            $query->where('date', $date);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%" . mb_strtolower($search) . "%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%" . mb_strtolower($search) . "%"]);
            });
        }

        $agendas = $query->orderBy('date', 'desc')->latest()->paginate(10);

        $groupedAgendas = [];
        foreach ($agendas as $agenda) {
            $dateKey = $agenda->date instanceof Carbon
                ? $agenda->date->toDateString()
                : (string) $agenda->date;

            if (!isset($groupedAgendas[$dateKey])) {
                $groupedAgendas[$dateKey] = [];
            }

            $groupedAgendas[$dateKey][] = $agenda;
        }

        $todayStr = Carbon::today()->toDateString();
        $yesterdayStr = Carbon::yesterday()->toDateString();

        $agendaDates = Agenda::withoutGlobalScope('academic_year')
            ->where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->where('date', '>=', Carbon::now()->subMonths(3)->startOfMonth()->toDateString())
            ->where('date', '<=', Carbon::now()->endOfMonth()->toDateString())
            ->pluck('date')
            ->map(function($d) {
                return $d instanceof Carbon ? $d->toDateString() : (string) $d;
            })
            ->unique()
            ->values();

        return view('guru.agenda.index', compact('agendas', 'groupedAgendas', 'academicYears', 'academicYearId', 'status', 'agendaDates', 'todayStr', 'yesterdayStr'));
    }

    private function resolveAcademicYearId(Request $request): ?int
    {
        $teacher = Auth::user();
        $institutionId = $teacher?->institution_id;
        $academicYearId = $request->query('academic_year_id') ?: $request->session()->get('academic_year_id');
        $legacyAcademicYearName = $request->query('academic_year_name');

        if (!$academicYearId && $legacyAcademicYearName) {
            $academicYearId = AcademicYear::query()
                ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
                ->where('name', $legacyAcademicYearName)
                ->value('id');
        }

        if ($academicYearId && ctype_digit((string) $academicYearId)) {
            $exists = AcademicYear::query()
                ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
                ->whereKey((int) $academicYearId)
                ->exists();

            if ($exists) {
                return (int) $academicYearId;
            }
        }

        return AcademicYear::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->where('is_active', true)
            ->value('id');
    }

    public function create(Request $request)
    {
        $teacher = Auth::user();
        $today = Carbon::today()->format('l');

        $allSchedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['class', 'subject'])
            ->get();

        $classes = $allSchedules->pluck('class')->unique('id')->values();
        $subjects = $allSchedules->pluck('subject')->unique('id')->values();

        $todaySchedules = $allSchedules->where('day', $today)
            ->whereIn('week_type', Setting::scheduleWeekTypesForDate(Carbon::today()));

        $selected_schedule = null;
        if ($request->has('schedule_id')) {
            $selected_schedule = Schedule::where('teacher_id', $teacher->id)
                ->with(['class', 'subject'])
                ->find($request->schedule_id);
        }

        $defaultRooms = $todaySchedules->pluck('room')->filter()->unique()->values();

        $todayClassIds = $todaySchedules->pluck('class_id')->unique()->values();
        $autoClassId = null;
        if (!$selected_schedule && $todayClassIds->count() === 1) {
            $autoClassId = $todayClassIds->first();
        }

        return view('guru.agenda.create', compact('classes', 'subjects', 'selected_schedule', 'defaultRooms', 'autoClassId', 'todaySchedules'));
    }

    public function getScheduleInfo(Request $request)
    {
        $teacher = Auth::user();
        $classId = $request->input('class_id');
        $date    = $request->input('date');

        if (!$classId || !$date) {
            return response()->json(['error' => 'class_id and date required'], 422);
        }

        $dayName = Carbon::parse($date)->format('l');

        $schedules = Schedule::withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->where('day', $dayName)
            ->whereIn('week_type', Setting::scheduleWeekTypesForDate(Carbon::parse($date)))
            ->with(['subject', 'room_model'])
            ->orderBy('start_time')
            ->get();

        $hasSchedule = $schedules->isNotEmpty();

        $subjects = $schedules->map(function ($s) {
            return [
                'id'   => $s->subject_id,
                'name' => $s->subject?->name
                    ?? Subject::withoutGlobalScopes()->whereKey($s->subject_id)->value('name')
                    ?? '-',
                'room' => $s->room_model ? $s->room_model->name : ($s->room ?? null),
            ];
        })->unique('id')->values();

        $roomsForDay = $schedules->map(function ($s) {
            return $s->room_model ? $s->room_model->name : ($s->room ?? null);
        })->filter()->unique()->values();

        $allRoomsForClass = Schedule::withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->whereNotNull('room_id')
            ->with('room_model')
            ->get()
            ->pluck('room_model.name')
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'has_schedule' => $hasSchedule,
            'day'          => $dayName,
            'subjects'     => $subjects,
            'rooms'        => $roomsForDay,
            'all_rooms'    => $allRoomsForClass,
        ]);
    }

    public function store(Request $request)
    {
        $teacher = Auth::user();

        $request->validate([
            'class_id'    => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', $teacher->institution_id)),
            ],
            'subject_id'  => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($q) => $q->where('institution_id', $teacher->institution_id)),
            ],
            'schedule_id' => [
                'nullable',
                Rule::exists('schedules', 'id')->where(fn ($q) => $q->where('institution_id', $teacher->institution_id)),
            ],
            'room'        => 'required|string|max:255',
            'date'        => 'required|date',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'attachment'  => 'nullable|file|mimes:pdf,doc,docx,jpg,png,xlsx|max:2048',
            'status'      => 'required|in:published,draft',
        ]);

        $dayName = Carbon::parse($request->date)->format('l');
        $schedule = Schedule::where('teacher_id', $teacher->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('day', $dayName)
            ->whereIn('week_type', Setting::scheduleWeekTypesForDate(Carbon::parse($request->date)))
            ->when($request->schedule_id, fn ($q) => $q->where('id', $request->schedule_id))
            ->first();

        if (!$schedule) {
            return redirect()->back()->with('error', 'Anda tidak memiliki jadwal untuk kelas dan mata pelajaran ini pada tanggal tersebut.')->withInput();
        }

        $exists = Agenda::where('teacher_id', $teacher->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('date', $request->date)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Agenda untuk pelajaran ini di kelas dan tanggal tersebut sudah pernah diisi.')->withInput();
        }

        $scheduleDate = Carbon::parse($request->date);
        if ($scheduleDate->isToday()) {
            if ($schedule) {
                $nowTime = now()->format('H:i:s');

                if ($schedule->start_time && $nowTime < $schedule->start_time) {
                    $start = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->start_time)->format('H:i');
                    return redirect()->back()->with('error', "Jurnal belum dapat diisi. Jadwal dimulai pukul {$start}. Silakan isi setelah sesi berlangsung.")->withInput();
                }

                if ($schedule->end_time && $nowTime > $schedule->end_time) {
                    $end = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->end_time)->format('H:i');
                    return redirect()->back()->with('error', "Jurnal sudah tidak dapat diisi. Jadwal berakhir pukul {$end}. Silakan isi sebelum sesi berakhir.")->withInput();
                }
            }
        }

        $data = [
            'teacher_id'  => $teacher->id,
            'class_id'    => $request->class_id,
            'subject_id'  => $request->subject_id,
            'schedule_id' => $request->schedule_id,
            'room'        => $request->room,
            'date'        => $request->date,
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => $request->status,
            'institution_id' => $teacher->institution_id,
        ];

        if ($request->hasFile('attachment')) {
            $data['attachments'] = $request->file('attachment')->store('agendas', 'public');
        }

        Agenda::create($data);

        return redirect()->route('guru.agenda.index')->with('success', 'Jurnal mengajar berhasil disimpan.');
    }

    public function edit(Agenda $agenda)
    {
        if ((int) $agenda->teacher_id !== (int) Auth::id()) {
            abort(403);
        }

        $teacher = Auth::user();

        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['class', 'subject'])
            ->get();

        $classes = $schedules->pluck('class')->unique('id')->values();
        $subjects = $schedules->pluck('subject')->unique('id')->values();

        $allRooms = Schedule::where('teacher_id', $teacher->id)
            ->whereNotNull('room')
            ->distinct()
            ->pluck('room')
            ->values();

        return view('guru.agenda.edit', compact('agenda', 'classes', 'subjects', 'allRooms'));
    }

    public function update(Request $request, Agenda $agenda)
    {
        if ((int) $agenda->teacher_id !== (int) Auth::id()) {
            abort(403);
        }

        $teacher = Auth::user();

        $request->validate([
            'class_id'    => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', $teacher->institution_id)),
            ],
            'subject_id'  => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($q) => $q->where('institution_id', $teacher->institution_id)),
            ],
            'room'        => 'required|string|max:255',
            'date'        => 'required|date',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'attachment'  => 'nullable|file|mimes:pdf,doc,docx,jpg,png,xlsx|max:2048',
            'status'      => 'required|in:published,draft',
        ]);

        $dayName = Carbon::parse($request->date)->format('l');
        $schedule = Schedule::where('teacher_id', $teacher->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('day', $dayName)
            ->whereIn('week_type', Setting::scheduleWeekTypesForDate(Carbon::parse($request->date)))
            ->first();

        if (!$schedule) {
            return redirect()->back()->with('error', 'Anda tidak memiliki jadwal untuk kelas dan mata pelajaran ini pada tanggal tersebut.')->withInput();
        }

        $exists = Agenda::where('teacher_id', Auth::id())
            ->where('id', '!=', $agenda->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('date', $request->date)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Agenda untuk pelajaran ini di kelas dan tanggal tersebut sudah pernah diisi.')->withInput();
        }

        $scheduleDate = Carbon::parse($request->date);
        if ($scheduleDate->isToday()) {
            if ($schedule) {
                $nowTime = now()->format('H:i:s');

                if ($schedule->start_time && $nowTime < $schedule->start_time) {
                    $start = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->start_time)->format('H:i');
                    return redirect()->back()->with('error', "Jurnal belum dapat diisi. Jadwal dimulai pukul {$start}. Silakan isi setelah sesi berlangsung.")->withInput();
                }

                if ($schedule->end_time && $nowTime > $schedule->end_time) {
                    $end = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->end_time)->format('H:i');
                    return redirect()->back()->with('error', "Jurnal sudah tidak dapat diisi. Jadwal berakhir pukul {$end}. Silakan isi sebelum sesi berakhir.")->withInput();
                }
            }
        }

        $data = [
            'class_id'    => $request->class_id,
            'subject_id'  => $request->subject_id,
            'room'        => $request->room,
            'date'        => $request->date,
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => $request->status,
            'institution_id' => $teacher->institution_id,
        ];

        if ($request->hasFile('attachment')) {
            if ($agenda->attachments) {
                Storage::disk('public')->delete($agenda->attachments);
            }
            $data['attachments'] = $request->file('attachment')->store('agendas', 'public');
        }

        $agenda->update($data);

        return redirect()->route('guru.agenda.index')->with('success', 'Jurnal mengajar berhasil diperbarui.');
    }

    public function show(Agenda $agenda)
    {
        if ((int) $agenda->teacher_id !== (int) Auth::id()) {
            abort(403);
        }
        $agenda->load(['class', 'subject', 'academicYear']);
        return view('guru.agenda.show', compact('agenda'));
    }

    public function preview(int|string $id)
    {
        $agenda = Agenda::withoutGlobalScope('academic_year')
            ->with(['class', 'teacher', 'subject'])
            ->findOrFail($id);

        if ((int) $agenda->teacher_id !== (int) Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'title'        => $agenda->title,
            'description'  => trim(strip_tags((string) $agenda->description)),
            'date'         => Carbon::parse($agenda->date)->translatedFormat('d F Y'),
            'class_name'   => $agenda->class->name ?? '-',
            'subject_name' => $agenda->subject->name ?? null,
            'teacher_name' => $agenda->teacher->name ?? Auth::user()->name,
            'room'         => $agenda->room,
            'attachments'  => $agenda->attachments ? asset('storage/' . $agenda->attachments) : null,
            'status'       => $agenda->status,
            'edit_url'     => route('guru.agenda.edit', $agenda->id),
        ]);
    }

    public function destroy(Agenda $agenda)
    {
        if ((int) $agenda->teacher_id !== (int) Auth::id()) {
            abort(403);
        }

        return redirect()->route('guru.agenda.index')
            ->with('error', 'Jurnal tidak dapat dihapus karena akan tetap disimpan sebagai arsip.');
    }
}
