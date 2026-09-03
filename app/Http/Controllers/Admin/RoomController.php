<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        $totalRooms = $rooms->count();
        $roomsByType = $rooms->groupBy('type')->map->count();
        
        return view('admin.rooms.index', compact('rooms', 'totalRooms', 'roomsByType'));
    }

    public function create()
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request)
    {
        $institutionId = Auth::user()?->institution_id;
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('rooms', 'name')->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'type' => 'required|string|max:50',
            'capacity' => 'nullable|integer',
        ]);

        Room::create($request->all() + ['institution_id' => $institutionId]);
        return redirect()->route('admin.rooms.index')->with('success', 'Ruangan berhasil ditambahkan!');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $institutionId = Auth::user()?->institution_id;
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('rooms', 'name')->ignore($room->id)->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'type' => 'required|string|max:50',
            'capacity' => 'nullable|integer',
        ]);

        $room->update($request->all());
        return redirect()->route('admin.rooms.index')->with('success', 'Ruangan berhasil diperbarui!');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('admin.rooms.index')->with('success', 'Ruangan berhasil dihapus!');
    }
}
