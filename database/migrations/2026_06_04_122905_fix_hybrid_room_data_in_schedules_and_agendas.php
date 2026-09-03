<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Schedule;
use App\Models\Agenda;
use App\Models\Room;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sync schedules
        $schedules = Schedule::whereNull('room_id')->whereNotNull('room')->get();
        foreach ($schedules as $schedule) {
            $room = Room::where('name', $schedule->room)->first();
            if ($room) {
                $schedule->update(['room_id' => $room->id]);
            }
        }

        // Sync agendas
        $agendas = Agenda::whereNull('room_id')->whereNotNull('room')->get();
        foreach ($agendas as $agenda) {
            $room = Room::where('name', $agenda->room)->first();
            if ($room) {
                $agenda->update(['room_id' => $room->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
