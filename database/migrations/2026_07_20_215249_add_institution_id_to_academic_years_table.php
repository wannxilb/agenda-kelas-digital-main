<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('id')->constrained('institutions')->onDelete('cascade');
        });

        // Backfill: assign academic years to all existing institutions
        $institutions = \App\Models\Institution::all();
        if ($institutions->isNotEmpty()) {
            foreach ($institutions as $institution) {
                \DB::table('academic_years')
                    ->whereNull('institution_id')
                    ->update(['institution_id' => $institution->id]);
            }
        }

        // Make non-nullable after backfill
        Schema::table('academic_years', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn('institution_id');
        });
    }
};
