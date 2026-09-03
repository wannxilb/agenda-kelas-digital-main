<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $institutionId = 1;
        $rooms = [];

        // Laboratorium Komputer 1-9
        for ($i = 1; $i <= 9; $i++) {
            $rooms[] = [
                'name' => 'Laboratorium Komputer ' . $i,
                'type' => 'Laboratorium',
                'capacity' => 40,
                'is_active' => true,
                'institution_id' => $institutionId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Kelas B - Z
        foreach (range('B', 'Z') as $letter) {
            $rooms[] = [
                'name' => 'Kelas ' . $letter,
                'type' => 'Kelas',
                'capacity' => 36,
                'is_active' => true,
                'institution_id' => $institutionId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Kelas AA - AM
        $suffixes = ['AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM'];
        foreach ($suffixes as $s) {
            $rooms[] = [
                'name' => 'Kelas ' . $s,
                'type' => 'Kelas',
                'capacity' => 36,
                'is_active' => true,
                'institution_id' => $institutionId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Room::insert($rooms);

        $this->command->info('Berhasil insert ' . count($rooms) . ' ruangan.');
    }
}
