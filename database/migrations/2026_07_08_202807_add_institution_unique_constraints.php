<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->unique(['email', 'institution_id']);

            $table->dropUnique('users_nis_unique');
            $table->unique(['nis', 'institution_id']);

            $table->dropUnique('users_nip_unique');
            $table->unique(['nip', 'institution_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_nisn_unique');
            $table->unique(['nisn', 'institution_id']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_code_unique');
            $table->unique(['code', 'institution_id']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->unique(['name', 'institution_id']);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique(['name', 'institution_id']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['code', 'institution_id']);
            $table->unique('code');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nisn', 'institution_id']);
            $table->unique('nisn');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nip', 'institution_id']);
            $table->unique('nip');

            $table->dropUnique(['nis', 'institution_id']);
            $table->unique('nis');

            $table->dropUnique(['email', 'institution_id']);
            $table->unique('email');
        });
    }
};
