<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_daily_attendances', function (Blueprint $table) {
            $table->foreignId('overridden_by')->nullable()->after('verified_by')->constrained('users')->nullOnDelete();
            $table->timestamp('overridden_at')->nullable()->after('overridden_by');
        });
    }

    public function down(): void
    {
        Schema::table('student_daily_attendances', function (Blueprint $table) {
            $table->dropForeign(['overridden_by']);
            $table->dropColumn(['overridden_by', 'overridden_at']);
        });
    }
};
