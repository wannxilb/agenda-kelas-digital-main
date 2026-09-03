<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
            
            $table->unique(['class_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_locks');
    }
};
