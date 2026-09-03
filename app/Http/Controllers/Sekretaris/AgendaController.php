<?php

// app/Http/Controllers/Sekretaris/AgendaController.php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Agenda;
use App\Models\Classes;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\User;
use App\Support\ImageCompressor;
use App\Traits\ResolvesSekretarisClassContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AgendaController extends Controller
{
    use ResolvesSekretarisClassContext;

    public function index(Request $request)
    {
        $context = $this->sekretarisClassContext($request);
        $classId = $context['selectedClassId'];

        if (! $classId) {
            return redirect()->route('sekretaris.dashboard')->with('error', 'Data kelas untuk periode yang dipilih tidak ditemukan.');
        }

        $status = $request->query('status');
        $query = Agenda::where('class_id', $classId)
            ->with(['class', 'teacher', 'subject']);

        if ($status === 'all') {
            // show all statuses
        } elseif ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', 'published');
        }

        if ($request->has('search') && $request->search) {
            $query->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($request->search).'%']);
        }

        if ($request->has('date') && $request->date) {
            $query->where('date', $request->date);
        }

        $agendas = $query->orderBy('date', 'desc')->paginate(15);
        $classes = collect([$context['selectedClass']]);

        $todayStr = Carbon::today()->toDateString();
        $yesterdayStr = Carbon::yesterday()->toDateString();

        $groupedAgendas = [];
        foreach ($agendas as $agenda) {
            $dateKey = $agenda->date instanceof Carbon
                ? $agenda->date->toDateString()
                : (string) $agenda->date;

            if (! isset($groupedAgendas[$dateKey])) {
                $groupedAgendas[$dateKey] = [];
            }
            $groupedAgendas[$dateKey][] = $agenda;
        }

        // Get all dates that have agendas for calendar dots (last 3 months to current month end)
        $agendaDates = Agenda::where('class_id', $classId)
            ->where('status', 'published')
            ->where('date', '>=', Carbon::now()->subMonths(3)->startOfMonth()->toDateString())
            ->where('date', '<=', Carbon::now()->endOfMonth()->toDateString())
            ->pluck('date')
            ->map(function ($d) {
                return $d instanceof Carbon ? $d->toDateString() : (string) $d;
            })
            ->unique()
            ->values();

        return view('sekretaris.agenda.index', array_merge(
            compact('agendas', 'classes', 'groupedAgendas', 'todayStr', 'yesterdayStr', 'agendaDates'),
            $context
        ));
    }

    public function create()
    {
        $classId = Auth::user()->class_id;
        if (! $classId) {
            return redirect()->route('sekretaris.dashboard')->with('error', 'Anda belum terdaftar di kelas manapun.');
        }

        $classes = Classes::where('id', $classId)->get();
        $today = Carbon::today()->format('l');
        $todayDate = Carbon::today();

        // Subjects filtered by today's schedule
        $subjects = Subject::whereHas('schedules', function ($q) use ($classId, $today, $todayDate) {
            $q->where('class_id', $classId)->where('day', $today)->whereIn('week_type', Setting::scheduleWeekTypesForDate($todayDate));
        })->with('teachers')->get();

        $teachers = User::role('teacher')->whereHas('schedules', fn ($q) => $q->where('class_id', $classId))->get();

        // Load today's schedules
        $todaySchedules = Schedule::where('class_id', $classId)
            ->where('day', $today)
            ->whereIn('week_type', Setting::scheduleWeekTypesForDate($todayDate))
            ->with(['subject', 'teacher', 'room_model'])
            ->get();

        // Fallback: if no schedule today, show all subjects/rooms/teachers for this class
        $isFallback = $todaySchedules->isEmpty();
        if ($isFallback) {
            $subjects = Subject::whereHas('schedules', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })->with('teachers')->get();
        }

        $scheduleRooms = $todaySchedules->filter(function ($s) {
            $room = $s->room_model ? $s->room_model->name : ($s->room ?? '');

            return ! empty($room);
        })->map(function ($s) {
            return $s->room_model ? $s->room_model->name : $s->room;
        })->unique()->values();

        $allRooms = Schedule::where('class_id', $classId)->whereNotNull('room')
            ->distinct()->pluck('room');

        // Build schedule data map for JS
        $scheduleData = [];
        if ($isFallback) {
            // No schedule today — build map from ALL schedules so dropdown + map stay in sync
            $allClassSchedules = Schedule::where('class_id', $classId)
                ->with(['subject', 'teacher', 'room_model'])
                ->get();
            foreach ($allClassSchedules as $s) {
                $room = $s->room_model ? $s->room_model->name : ($s->room ?? '');
                $teacherName = $s->teacher?->name
                    ?? User::withoutGlobalScopes()->whereKey($s->teacher_id)->value('name')
                    ?? '';
                if (!isset($scheduleData[$s->subject_id])) {
                    $scheduleData[$s->subject_id] = [
                        'teacher_id' => $s->teacher_id,
                        'teacher_name' => $teacherName,
                        'room' => $room,
                    ];
                }
            }
        } else {
            // Has schedule today — map from today only
            foreach ($todaySchedules as $s) {
                $room = $s->room_model ? $s->room_model->name : ($s->room ?? '');
                $teacherName = $s->teacher?->name
                    ?? User::withoutGlobalScopes()->whereKey($s->teacher_id)->value('name')
                    ?? '';
                $scheduleData[$s->subject_id] = [
                    'teacher_id' => $s->teacher_id,
                    'teacher_name' => $teacherName,
                    'room' => $room,
                ];
            }
        }

        // Teachers for dropdown
        $scheduleTeachers = $todaySchedules->pluck('teacher_id')->unique()->map(function ($id) use ($todaySchedules) {
            $s = $todaySchedules->firstWhere('teacher_id', $id);

            return ['id' => $id, 'name' => $s ? $s->teacher->name : ''];
        })->values();
        if ($scheduleTeachers->isEmpty()) {
            $scheduleTeachers = $teachers->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values();
        }

        return view('sekretaris.agenda.create', compact('classes', 'subjects', 'teachers', 'scheduleRooms', 'allRooms', 'scheduleData', 'scheduleTeachers'));
    }

    /**
     * AJAX endpoint: returns subjects & rooms for a given date based on the class schedule.
     */
    public function getScheduleInfo(Request $request)
    {
        $classId = Auth::user()->class_id;
        if (! $classId) {
            return response()->json(['error' => 'No class assigned'], 403);
        }

        $date = $request->input('date');
        if (! $date) {
            return response()->json(['error' => 'Date required'], 422);
        }

        $dayName = Carbon::parse($date)->format('l'); // e.g. 'Monday'

        $schedules = Schedule::withoutGlobalScopes()
            ->where('class_id', $classId)
            ->where('day', $dayName)
            ->whereIn('week_type', Setting::scheduleWeekTypesForDate(Carbon::parse($date)))
            ->with(['subject', 'teacher', 'room_model'])
            ->orderBy('start_time')
            ->get();

        $hasSchedule = $schedules->isNotEmpty();

        // Fallback: if no schedule for this day, show all subjects/rooms/teachers for this class
        if ($schedules->isEmpty()) {
            $schedules = Schedule::withoutGlobalScopes()
                ->where('class_id', $classId)
                ->with(['subject', 'teacher', 'room_model'])
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();
        }

        $subjects = $schedules->map(function ($s) {
            $subjectName = $s->subject?->name
                ?? Subject::withoutGlobalScopes()->whereKey($s->subject_id)->value('name')
                ?? '-';
            $teacherName = $s->teacher?->name
                ?? User::withoutGlobalScopes()->whereKey($s->teacher_id)->value('name')
                ?? '-';

            return [
                'id' => $s->subject_id,
                'name' => $subjectName,
                'teacher_id' => $s->teacher_id,
                'teacher' => $teacherName,
                // Ensure room is always a string name
                'room' => $s->room_model ? $s->room_model->name : ($s->room ?? null),
            ];
        })->unique('id')->values();

        // Get all teachers scheduled for this class on this day
        $teachersForDay = $schedules->map(function ($s) {
            return [
                'id' => $s->teacher_id,
                'name' => $s->teacher?->name
                    ?? User::withoutGlobalScopes()->whereKey($s->teacher_id)->value('name')
                    ?? '-',
            ];
        })->unique('id')->values();

        // Get rooms scheduled for this class on this specific day
        $roomsForDay = $schedules->map(function ($s) {
            return $s->room_model ? $s->room_model->name : ($s->room ?? null);
        })->filter()->unique()->values();

        return response()->json([
            'has_schedule' => $hasSchedule,
            'day' => $dayName,
            'subjects' => $subjects,
            'teachers' => $teachersForDay, // Send teachers scheduled on this day
            'rooms' => $roomsForDay,    // Send rooms scheduled on this day
        ]);
    }

    public function store(Request $request)
    {
        $institutionId = Auth::user()->institution_id;
        $subjectExistsRule = Rule::exists('subjects', 'id');
        $teacherExistsRule = Rule::exists('users', 'id');
        if ($institutionId) {
            $subjectExistsRule->where(fn ($q) => $q->where('institution_id', $institutionId));
            $teacherExistsRule->where(fn ($q) => $q->where('institution_id', $institutionId));
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subject_id' => [
                'nullable',
                $subjectExistsRule,
            ],
            'teacher_id' => [
                'required',
                $teacherExistsRule,
            ],
            'room' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'required|string',
            'status' => 'required|in:published,draft',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Cek apakah agenda sudah ada
        $existsQuery = Agenda::where('teacher_id', $request->teacher_id)
            ->where('class_id', Auth::user()->class_id)
            ->where('date', $request->date);

        if ($request->subject_id) {
            $existsQuery->where('subject_id', $request->subject_id);
        } else {
            $existsQuery->whereNull('subject_id');
        }

        $exists = $existsQuery->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Agenda untuk guru tersebut di kelas dan tanggal ini sudah pernah diisi.')->withInput();
        }

        $dayName = Carbon::parse($request->date)->format('l');
        $scheduleQuery = Schedule::withoutGlobalScopes()
            ->where('class_id', Auth::user()->class_id)
            ->where('teacher_id', $request->teacher_id)
            ->where('day', $dayName)
            ->whereIn('week_type', Setting::scheduleWeekTypesForDate(Carbon::parse($request->date)));

        if ($request->subject_id) {
            $scheduleQuery->where('subject_id', $request->subject_id);
        }

        $schedules = $scheduleQuery->orderBy('start_time')->get();

        if ($schedules->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Guru dan mata pelajaran yang dipilih tidak memiliki jadwal di kelas ini pada tanggal tersebut.')
                ->withInput();
        }

        if ($message = $this->getScheduleTimeRestrictionMessage($request->date, $schedules)) {
            return redirect()->back()->with('error', $message)->withInput();
        }

        $data = $request->only(['teacher_id', 'subject_id', 'room', 'date', 'title', 'description', 'status']);
        $data['class_id'] = Auth::user()->class_id;
        $data['institution_id'] = Auth::user()->institution_id;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('agendas', 'public');
            if (in_array($request->file('attachment')->getClientMimeType(), ['image/jpeg', 'image/png'])) {
                ImageCompressor::compress($path);
            }
            $data['attachments'] = $path;
        }

        Agenda::create($data);

        return redirect()->route('sekretaris.agenda.index')
            ->with('success', 'Agenda berhasil dibuat!');
    }

    public function edit(Agenda $agenda)
    {
        $classId = Auth::user()->class_id;
        if ((int) $agenda->class_id !== (int) $classId) {
            abort(403, 'Unauthorized action.');
        }

        $classes = Classes::where('id', $classId)->get();
        $dayName = Carbon::parse($agenda->date)->format('l');

        // Subjects filtered by the agenda's date day
        $subjects = Subject::whereHas('schedules', function ($q) use ($classId, $dayName, $agenda) {
            $q->where('class_id', $classId)->where('day', $dayName)->whereIn('week_type', Setting::scheduleWeekTypesForDate(Carbon::parse($agenda->date)));
        })->with('teachers')->get();

        if ($subjects->isEmpty()) {
            $subjects = Subject::whereHas('schedules', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })->with('teachers')->get();
        }

        $teachers = User::role('teacher')->whereHas('schedules', fn ($q) => $q->where('class_id', $classId))->get();

        // Load schedules for the agenda's date
        $daySchedules = Schedule::where('class_id', $classId)
            ->where('day', $dayName)
            ->whereIn('week_type', Setting::scheduleWeekTypesForDate(Carbon::parse($agenda->date)))
            ->with(['subject', 'teacher', 'room_model'])
            ->get();

        $scheduleRooms = $daySchedules->filter(function ($s) {
            $room = $s->room_model ? $s->room_model->name : ($s->room ?? '');

            return ! empty($room);
        })->map(function ($s) {
            return $s->room_model ? $s->room_model->name : $s->room;
        })->unique()->values();

        $allRooms = Schedule::where('class_id', $classId)
            ->whereNotNull('room')
            ->distinct()
            ->pluck('room')
            ->values();

        // Build schedule data map for JS initialization
        $scheduleData = [];
        foreach ($daySchedules as $s) {
            $room = $s->room_model ? $s->room_model->name : ($s->room ?? '');
            $scheduleData[$s->subject_id] = [
                'teacher_id' => $s->teacher_id,
                'teacher_name' => $s->teacher->name,
                'room' => $room,
            ];
        }

        // Teachers scheduled for this day (pre-filtered dropdown)
        $scheduleTeachers = $daySchedules->pluck('teacher_id')->unique()->map(function ($id) use ($daySchedules) {
            $s = $daySchedules->firstWhere('teacher_id', $id);

            return ['id' => $id, 'name' => $s ? $s->teacher->name : ''];
        })->values();
        if ($scheduleTeachers->isEmpty()) {
            $scheduleTeachers = $teachers->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values();
        }

        return view('sekretaris.agenda.edit', compact('agenda', 'classes', 'subjects', 'teachers', 'scheduleRooms', 'allRooms', 'scheduleData', 'scheduleTeachers'));
    }

    public function update(Request $request, Agenda $agenda)
    {
        if ((int) $agenda->class_id !== (int) Auth::user()->class_id) {
            abort(403);
        }

        $institutionId = Auth::user()->institution_id;
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subject_id' => [
                'nullable',
                Rule::exists('subjects', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'teacher_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'room' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'required|string',
            'status' => 'required|in:published,draft',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Cek duplikat (kecuali untuk agenda yang sedang diupdate)
        $existsQuery = Agenda::where('teacher_id', $request->teacher_id)
            ->where('class_id', Auth::user()->class_id)
            ->where('date', $request->date)
            ->where('id', '!=', $agenda->id);

        if ($request->subject_id) {
            $existsQuery->where('subject_id', $request->subject_id);
        } else {
            $existsQuery->whereNull('subject_id');
        }

        if ($existsQuery->exists()) {
            return redirect()->back()->with('error', 'Agenda untuk guru tersebut di kelas dan tanggal ini sudah pernah diisi.')->withInput();
        }

        $dayName = Carbon::parse($request->date)->format('l');
        $scheduleQuery = Schedule::where('class_id', Auth::user()->class_id)
            ->where('teacher_id', $request->teacher_id)
            ->where('day', $dayName)
            ->whereIn('week_type', Setting::scheduleWeekTypesForDate(Carbon::parse($request->date)));

        if ($request->subject_id) {
            $scheduleQuery->where('subject_id', $request->subject_id);
        }

        $schedules = $scheduleQuery->orderBy('start_time')->get();

        if ($schedules->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Guru dan mata pelajaran yang dipilih tidak memiliki jadwal di kelas ini pada tanggal tersebut.')
                ->withInput();
        }

        if ($message = $this->getScheduleTimeRestrictionMessage($request->date, $schedules)) {
            return redirect()->back()->with('error', $message)->withInput();
        }

        $data = $request->only(['teacher_id', 'subject_id', 'room', 'date', 'title', 'description', 'status']);
        $data['class_id'] = Auth::user()->class_id;
        $data['institution_id'] = Auth::user()->institution_id;

        if ($request->hasFile('attachment')) {
            // Delete old attachment
            if ($agenda->attachments) {
                Storage::disk('public')->delete($agenda->attachments);
            }
            $path = $request->file('attachment')->store('agendas', 'public');
            if (in_array($request->file('attachment')->getClientMimeType(), ['image/jpeg', 'image/png'])) {
                ImageCompressor::compress($path);
            }
            $data['attachments'] = $path;
        }

        $agenda->update($data);

        return redirect()->route('sekretaris.agenda.index')
            ->with('success', 'Agenda berhasil diperbarui!');
    }

    public function destroy(Agenda $agenda)
    {
        if ((int) $agenda->class_id !== (int) Auth::user()->class_id) {
            abort(403);
        }

        if ($agenda->attachments) {
            Storage::disk('public')->delete($agenda->attachments);
        }
        $agenda->delete();

        return redirect()->route('sekretaris.agenda.index')
            ->with('success', 'Agenda berhasil dihapus!');
    }

    public function preview(int|string $id)
    {
        $agenda = Agenda::with(['class', 'teacher', 'subject'])->findOrFail($id);

        /** @var User $user */
        $user = Auth::user();
        $classIds = $user->classHistories()->pluck('class_id')->toArray();
        if ($user->class_id && ! in_array($user->class_id, $classIds)) {
            $classIds[] = $user->class_id;
        }

        if (! in_array($agenda->class_id, $classIds)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'title' => $agenda->title,
            'description' => trim(strip_tags((string) $agenda->description)),
            'date' => Carbon::parse($agenda->date)->format('d F Y'),
            'class_name' => $agenda->class->name,
            'subject_name' => $agenda->subject->name ?? null,
            'teacher_name' => $agenda->teacher->name,
            'room' => $agenda->room,
            'attachments' => $agenda->attachments ? asset('storage/'.$agenda->attachments) : null,
            'status' => $agenda->status,
            'edit_url' => route('sekretaris.agenda.edit', $agenda->id),
        ]);
    }

    private function getScheduleTimeRestrictionMessage(string $date, Collection $schedules): ?string
    {
        $targetDate = Carbon::parse($date);
        
        if ($targetDate->isFuture() && !$targetDate->isToday()) {
            return "Agenda tidak dapat diisi untuk tanggal di masa depan.";
        }

        if ($targetDate->isPast() && !$targetDate->isToday()) {
            // Allow backfilling for past dates
            return null;
        }

        $nowTime = now()->format('H:i:s');

        $activeSchedule = $schedules->first(function (Schedule $schedule) use ($nowTime) {
            return (! $schedule->start_time || $nowTime >= $schedule->start_time)
                && (! $schedule->end_time || $nowTime <= $schedule->end_time);
        });

        if ($activeSchedule) {
            return null;
        }

        $upcomingSchedule = $schedules->first(function (Schedule $schedule) use ($nowTime) {
            return $schedule->start_time && $nowTime < $schedule->start_time;
        });

        if ($upcomingSchedule) {
            $start = Carbon::createFromFormat('H:i:s', $upcomingSchedule->start_time)->format('H:i');

            return "Agenda belum dapat diisi. Jadwal dimulai pukul {$start}. Silakan isi setelah sesi berlangsung.";
        }

        $lastSchedule = $schedules->filter(fn (Schedule $schedule) => $schedule->end_time)->last();
        if ($lastSchedule) {
            $end = Carbon::createFromFormat('H:i:s', $lastSchedule->end_time)->format('H:i');

            return "Agenda sudah tidak dapat diisi. Jadwal berakhir pukul {$end}. Silakan isi sebelum sesi berakhir.";
        }

        return 'Agenda hanya dapat diisi saat jadwal pelajaran sedang berlangsung.';
    }
}
