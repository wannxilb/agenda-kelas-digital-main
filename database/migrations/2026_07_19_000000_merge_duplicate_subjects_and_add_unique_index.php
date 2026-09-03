<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Merge duplicate subjects manually
        // Find subjects with same name + institution_id
        $subjects = DB::table('subjects')
            ->select('name', 'institution_id')
            ->groupBy('name', 'institution_id')
            ->havingRaw('count(*) > 1')
            ->get();

        foreach ($subjects as $dupe) {
            $ids = DB::table('subjects')
                ->where('name', $dupe->name)
                ->where('institution_id', $dupe->institution_id)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            if (count($ids) <= 1) continue;

            $keepId = $ids[0];

            for ($i = 1; $i < count($ids); $i++) {
                $removeId = $ids[$i];

                // Move teachers from duplicate to kept subject
                $teachers = DB::table('subject_user')
                    ->where('subject_id', $removeId)
                    ->pluck('user_id')
                    ->toArray();

                foreach ($teachers as $teacherId) {
                    $exists = DB::table('subject_user')
                        ->where('subject_id', $keepId)
                        ->where('user_id', $teacherId)
                        ->exists();
                    if (!$exists) {
                        DB::table('subject_user')->insert([
                            'subject_id' => $keepId,
                            'user_id' => $teacherId,
                        ]);
                    }
                }

                // Update schedules
                DB::table('schedules')->where('subject_id', $removeId)->update(['subject_id' => $keepId]);

                // Update agendas
                DB::table('agendas')->where('subject_id', $removeId)->update(['subject_id' => $keepId]);

                // Delete pivot entries
                DB::table('subject_user')->where('subject_id', $removeId)->delete();

                // Delete duplicate subject
                DB::table('subjects')->where('id', $removeId)->delete();
            }
        }

        // 2. Add unique composite index on (name, institution_id)
        Schema::table('subjects', function (Blueprint $table) {
            $table->unique(['name', 'institution_id']);
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['name', 'institution_id']);
        });
    }
};
