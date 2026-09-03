<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDailyAttendanceCorrection extends Model
{
    use HasFactory, BelongsToInstitution;

    protected $fillable = [
        'student_daily_attendance_id', 'student_id', 'institution_id', 'target_event',
        'requested_status', 'reason', 'evidence_path', 'evidence_mime', 'status', 'reviewed_by',
        'reviewed_at', 'reviewer_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(StudentDailyAttendance::class, 'student_daily_attendance_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
