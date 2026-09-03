<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            // Drop the old unique index on 'name'
            $table->dropUnique(['name']);
            // Add a new unique index on the combination of 'name' and 'semester'
            $table->unique(['name', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropUnique(['name', 'semester']);
            $table->unique('name');
        });
    }
};
