<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('major_grade_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('major_id')->constrained()->cascadeOnDelete();
            $table->enum('grade_level', ['X', 'XI', 'XII']);
            $table->string('code');
            $table->unsignedTinyInteger('rombel_count')->default(1);
            $table->timestamps();

            $table->unique(['major_id', 'grade_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('major_grade_templates');
    }
};
