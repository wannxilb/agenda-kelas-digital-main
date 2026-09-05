<?php
// app/Http/Controllers/Admin/ScheduleController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\User;
use App\Models\Room;
use App\Models\Setting;
use App\Imports\SchedulesImport;
use App\Exports\SchedulesTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleController extends Controller
{
    private function institutionExistsRule(string $table, ?int $institutionId)
    {
        $rule = Rule::exists($table, 'id');

        if ($institutionId) {
            $rule->where(function ($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)
                    ->orWhereNull('institution_id');
            });
        }

        return $rule;
    }

    public function index(Request $request)
    {
        $query = Schedule::with(['class', 'subject', 'teacher', 'room_model']);

        // Filter sesuai mode penjadwalan:
        //  - 'normal' -> hanya jadwal 'semua'
        //  - 'block'  -> jadwal 'ganjil' DAN 'genap'; di tabel & kalender
        //               ditampilkan terpisah dalam dua bagian bertumpuk
        $scheduleMode = Setting::scheduleMode();
        $weekTypes = $scheduleMode === 'block' ? ['ganjil', 'genap'] : ['semua'];
        $query->whereIn('week_type', $weekTypes);

        if ($request->has('class_id') && $request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->has('day') && $request->day) {
            $query->where('day', $request->day);
        }
        
        $schedules = $query->orderBy('day')
            ->orderBy('start_time')
            ->get()
            ->groupBy('class_id')
            ->sortBy(function ($classSchedules) {
                $class = $classSchedules->first()->class;
                $gradeOrder = ['X' => 1, 'XI' => 2, 'XII' => 3];
                return ($gradeOrder[$class->grade_level] ?? 99) . '|' . $class->name;
            });
            
        $classList = Classes::orderBy('name')->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $weekTypeLabels = ['semua' => 'Setiap Minggu', 'ganjil' => 'Minggu Ganjil', 'genap' => 'Minggu Genap'];
        $activeWeekLabel = implode(' & ', array_map(fn ($wt) => $weekTypeLabels[$wt] ?? $wt, $weekTypes));

        return view('admin.schedules.index', compact('schedules', 'classList', 'days', 'scheduleMode', 'activeWeekLabel'));
    }

    public function getAvailableRooms(Request $request)
    {
        $day = $request->day;
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        $scheduleId = $request->schedule_id;

        if (!$day || !$startTime || !$endTime) {
            return response()->json([]);
        }

        // Ambil ruangan yang BENTROK pada hari dan jam tersebut
        $busyRoomIds = Schedule::where('day', $day)
            ->where(function($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
            })
            ->where(function($q) use ($request) {
                if ($request->week_type && $request->week_type !== 'semua') {
                    $q->whereIn('week_type', ['semua', $request->week_type]);
                }
            })
            ->when($scheduleId, function($q) use ($scheduleId) {
                $q->where('id', '!=', $scheduleId);
            })
            ->whereNotNull('room_id')
            ->pluck('room_id');

        // Ambil semua ruangan yang aktif dan TIDAK ada di daftar bentrok
        $availableRooms = Room::where('is_active', true)
            ->whereNotIn('id', $busyRoomIds)
            ->get();

        return response()->json($availableRooms);
    }

    public function create()
    {
        $classList = Classes::orderBy('name')->get();
        $subjects = Subject::with('teachers')->get();
        $teachers = User::role('teacher')->get();
        $rooms = Room::where('is_active', true)->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $subjectJson = $subjects->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'credit_hours' => $s->credit_hours,
                'teachers' => $s->teachers->map(function($t) {
                    return [
                        'id' => $t->id,
                        'name' => $t->name,
                        'nip' => $t->nip ?? '-',
                    ];
                }),
            ];
        });
        
        return view('admin.schedules.create', compact('classList', 'subjects', 'teachers', 'rooms', 'days', 'subjectJson'));
    }

    public function show(Schedule $schedule)
    {
        $schedule->load(['class', 'subject', 'teacher', 'room_model']);
        return view('admin.schedules.show', compact('schedule'));
    }

    public function store(Request $request)
    {
        $institutionId = Auth::user()?->institution_id;
        $validator = Validator::make($request->all(), [
            'class_id' => [
                'required',
                $this->institutionExistsRule('classes', $institutionId),
            ],
            'subject_id' => [
                'required',
                $this->institutionExistsRule('subjects', $institutionId),
            ],
            'teacher_id' => [
                'required',
                $this->institutionExistsRule('users', $institutionId),
            ],
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'week_type' => 'required|in:semua,ganjil,genap',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:255',
            'room_id' => [
                'nullable',
                $this->institutionExistsRule('rooms', $institutionId),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startTime = strlen($request->start_time) === 5 ? $request->start_time . ':00' : $request->start_time;
        $endTime = strlen($request->end_time) === 5 ? $request->end_time . ':00' : $request->end_time;

        // 1. Cek bentrok kelas
        if (Schedule::withoutGlobalScopes()
            ->where(fn($q) => $q->where('institution_id', $institutionId)->orWhereNull('institution_id'))
            ->where('class_id', $request->class_id)
            ->where('day', $request->day)
            ->where(function($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
            })
            ->where(function($q) use ($request) {
                if ($request->week_type !== 'semua') {
                    $q->whereIn('week_type', ['semua', $request->week_type]);
                }
            })->exists()) {
            return redirect()->back()->with('error', 'Jadwal bentrok dengan jadwal lain di kelas yang sama!')->withInput();
        }

        // 2. Cek bentrok guru
        if (Schedule::withoutGlobalScopes()
            ->where(fn($q) => $q->where('institution_id', $institutionId)->orWhereNull('institution_id'))
            ->where('teacher_id', $request->teacher_id)
            ->where('day', $request->day)
            ->where(function($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
            })
            ->where(function($q) use ($request) {
                if ($request->week_type !== 'semua') {
                    $q->whereIn('week_type', ['semua', $request->week_type]);
                }
            })->exists()) {
            return redirect()->back()->with('error', 'Guru yang bersangkutan sudah memiliki jadwal mengajar di jam yang sama!')->withInput();
        }

        // 3. Cek bentrok ruangan
        if ($request->filled('room_id')) {
            $roomConflict = Schedule::withoutGlobalScopes()
                ->where(fn($q) => $q->where('institution_id', $institutionId)->orWhereNull('institution_id'))
                ->where('day', $request->day)
                ->where('room_id', $request->room_id)
                ->where(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                })
                ->where(function($q) use ($request) {
                    if ($request->week_type !== 'semua') {
                        $q->whereIn('week_type', ['semua', $request->week_type]);
                    }
                })
                ->exists();

            if ($roomConflict) {
                return redirect()->back()->with('error', 'Ruangan sudah digunakan untuk jadwal lain pada jam yang sama!')->withInput();
            }
        }

        $data = $request->except('room');
        $data['room_id'] = $request->room_id;

        Schedule::create($data);

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.schedules.index')->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function edit(Schedule $schedule)
    {
        $classList = Classes::orderBy('name')->get();
        $subjects = Subject::with('teachers')->get();
        $teachers = User::role('teacher')->get();
        $rooms = Room::where('is_active', true)->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $subjectJson = $subjects->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'credit_hours' => $s->credit_hours,
                'teachers' => $s->teachers->map(function($t) {
                    return [
                        'id' => $t->id,
                        'name' => $t->name,
                        'nip' => $t->nip ?? '-',
                    ];
                }),
            ];
        });
        
        return view('admin.schedules.edit', compact('schedule', 'classList', 'subjects', 'teachers', 'rooms', 'days', 'subjectJson'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $institutionId = Auth::user()?->institution_id;
        // Allow HH:MM:SS or HH:MM by using a custom validation logic if necessary,
        // but for now, let's ensure we are validating the input string format properly.
        $validator = Validator::make($request->all(), [
            'class_id' => [
                'required',
                $this->institutionExistsRule('classes', $institutionId),
            ],
            'subject_id' => [
                'required',
                $this->institutionExistsRule('subjects', $institutionId),
            ],
            'teacher_id' => [
                'required',
                $this->institutionExistsRule('users', $institutionId),
            ],
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'week_type' => 'required|in:semua,ganjil,genap',
            'start_time' => 'required|date_format:H:i,H:i:s',
            'end_time' => 'required|date_format:H:i,H:i:s',
            'room' => 'nullable|string|max:255',
            'room_id' => [
                'nullable',
                $this->institutionExistsRule('rooms', $institutionId),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Normalize time format to H:i:s for database consistency
        $startTime = date('H:i:s', strtotime($request->start_time));
        $endTime = date('H:i:s', strtotime($request->end_time));

        if ($startTime >= $endTime) {
            return redirect()->back()->with('error', 'Jam selesai harus setelah jam mulai!')->withInput();
        }

        // 1. Cek bentrok kelas
        if (Schedule::withoutGlobalScopes()
            ->where(fn($q) => $q->where('institution_id', $institutionId)->orWhereNull('institution_id'))
            ->where('class_id', $request->class_id)
            ->where('id', '!=', $schedule->id)
            ->where('day', $request->day)
            ->where(function($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
            })
            ->where(function($q) use ($request) {
                if ($request->week_type !== 'semua') {
                    $q->whereIn('week_type', ['semua', $request->week_type]);
                }
            })->exists()) {
            return redirect()->back()->with('error', 'Jadwal bentrok dengan jadwal lain di kelas yang sama!')->withInput();
        }

        // 2. Cek bentrok guru
        if (Schedule::withoutGlobalScopes()
            ->where(fn($q) => $q->where('institution_id', $institutionId)->orWhereNull('institution_id'))
            ->where('teacher_id', $request->teacher_id)
            ->where('id', '!=', $schedule->id)
            ->where('day', $request->day)
            ->where(function($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
            })
            ->where(function($q) use ($request) {
                if ($request->week_type !== 'semua') {
                    $q->whereIn('week_type', ['semua', $request->week_type]);
                }
            })->exists()) {
            return redirect()->back()->with('error', 'Guru yang bersangkutan sudah memiliki jadwal mengajar di jam yang sama!')->withInput();
        }

        // 3. Cek bentrok ruangan
        if ($request->filled('room_id')) {
            $roomConflict = Schedule::withoutGlobalScopes()
                ->where(fn($q) => $q->where('institution_id', $institutionId)->orWhereNull('institution_id'))
                ->where('id', '!=', $schedule->id)
                ->where('day', $request->day)
                ->where('room_id', $request->room_id)
                ->where(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                })
                ->where(function($q) use ($request) {
                    if ($request->week_type !== 'semua') {
                        $q->whereIn('week_type', ['semua', $request->week_type]);
                    }
                })
                ->exists();

            if ($roomConflict) {
                return redirect()->back()->with('error', 'Ruangan sudah digunakan untuk jadwal lain pada jam yang sama!')->withInput();
            }
        }

        $data = $request->except('room'); 
        $data['room_id'] = $request->room_id;
        $data['start_time'] = $startTime;
        $data['end_time'] = $endTime;

        $schedule->update($data);

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.schedules.index')->with('success', 'Jadwal berhasil diperbarui!');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        $prefix = request()->segment(1);
        return redirect()->route($prefix . '.schedules.index')->with('success', 'Jadwal berhasil dihapus!');
    }

    public function byClass(Classes $class)
    {
        $schedules = Schedule::with(['subject', 'teacher', 'room_model'])
            ->where('class_id', $class->id)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day');
            
        return response()->json($schedules);
    }

    /**
     * Import schedules from Excel/CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new SchedulesImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $skipped  = $import->getSkippedCount();
            $errors   = $import->getErrors();

            $detail = '';
            if ($skipped > 0) {
                $detail .= " {$skipped} baris dilewati.";
            }
            if (!empty($errors)) {
                $detail .= ' Masalah: ' . implode('; ', array_slice($errors, 0, 5));
            }

            if ($imported > 0) {
                return redirect()->back()->with('success', "Berhasil mengimpor {$imported} jadwal." . $detail);
            }

            $fallback = "Tidak ada jadwal yang berhasil diimpor." . $detail;
            return redirect()->back()->with('error', $fallback);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Export existing schedules as template for re-import / editing
     */
    public function exportTemplate()
    {
        return Excel::download(new SchedulesTemplateExport, 'template_jadwal_pelajaran.xlsx');
    }

    /**
     * Ubah mode penjadwalan: 'block' (ganjil/genap bergantian) atau 'normal'
     * (semua jadwal tampil tiap minggu). Dipakai oleh toggle di halaman jadwal.
     */
    public function setScheduleMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:block,normal',
        ]);

        Setting::set('schedule_mode', $request->mode);

        return response()->json([
            'success' => true,
            'mode' => $request->mode,
        ]);
    }
}
