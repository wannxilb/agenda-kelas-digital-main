<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use App\Traits\BelongsToInstitution;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, BelongsToInstitution;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'nis', 'nip', 'phone', 'parent_phone', 'address', 'avatar', 'class_id', 'status', 'is_wakasek', 'gender',
        'nisn', 'tempat_lahir', 'tanggal_lahir', 'rt', 'rw', 'kelurahan', 'kecamatan', 'institution_id', 'password_changed_at'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function classes()
    {
        return $this->hasMany(Classes::class, 'homeroom_teacher_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_user', 'user_id', 'subject_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class, 'teacher_id');
    }

    public function classHomeroom()
    {
        return $this->hasMany(Classes::class, 'homeroom_teacher_id');
    }

    public function teachingSchedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->teachingSchedules();
    }

    public function classHistories()
    {
        return $this->hasMany(ClassHistory::class);
    }

    public function academicYears()
    {
        return $this->belongsToMany(AcademicYear::class, 'class_histories');
    }

    public function getClassInAcademicYear($academicYearId)
    {
        $history = $this->classHistories()->where('academic_year_id', $academicYearId)->first();
        return $history ? $history->class : null;
    }

    public function scopeInClassAndAcademicYear($query, $classId, $academicYearIds)
    {
        $academicYearIds = is_array($academicYearIds) ? $academicYearIds : [$academicYearIds];

        return $query->where(function($q) use ($classId, $academicYearIds) {
            $q->whereHas('classHistories', function($qh) use ($classId, $academicYearIds) {
                $qh->where('class_id', $classId)->whereIn('academic_year_id', $academicYearIds);
            })->orWhere(function($qo) use ($classId, $academicYearIds) {
                $qo->where('class_id', $classId)
                   ->whereNotExists(function($qe) use ($academicYearIds) {
                       $qe->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('class_histories')
                          ->whereColumn('class_histories.user_id', 'users.id')
                          ->whereIn('class_histories.academic_year_id', $academicYearIds);
                   });
            });
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($user) {
            $institutionId = $user->institution_id ?? 'all';
            Cache::forget('stats_male_students_active_' . $institutionId);
            Cache::forget('stats_female_students_active_' . $institutionId);
        });

        static::deleted(function ($user) {
            $institutionId = $user->institution_id ?? 'all';
            Cache::forget('stats_male_students_active_' . $institutionId);
            Cache::forget('stats_female_students_active_' . $institutionId);
        });

        static::restored(function ($user) {
            $institutionId = $user->institution_id ?? 'all';
            Cache::forget('stats_male_students_active_' . $institutionId);
            Cache::forget('stats_female_students_active_' . $institutionId);
        });
    }
}
