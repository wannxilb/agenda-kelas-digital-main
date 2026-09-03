<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Schedule;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function rooms(Request $request)
    {
        $query = Room::query();

        // Filter pencarian nama
        if ($request->filled('search')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($request->search) . '%']);
        }

        // Filter tipe
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $rooms = $query->get()->map(function ($room) {
            $now = now();
            $activeSchedule = Schedule::currentlyOccupied($now)
                ->where(function($q) use ($room) {
                    $q->where('room_id', $room->id)
                      ->orWhere('room', $room->name);
                })
                ->with(['class', 'teacher'])
                ->first();

            $room->is_occupied = (bool)$activeSchedule;
            $room->active_schedule = $activeSchedule;
            return $room;
        });

        // Filter status (occupied/available)
        if ($request->filled('status')) {
            $rooms = $rooms->filter(function ($room) use ($request) {
                return $request->status == 'occupied' ? $room->is_occupied : !$room->is_occupied;
            });
        }

        return view('admin.monitoring.rooms', compact('rooms'));
    }
}
