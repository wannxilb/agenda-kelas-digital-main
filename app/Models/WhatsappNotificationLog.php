<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappNotificationLog extends Model
{
    use BelongsToInstitution, HasFactory;

    protected $fillable = [
        'student_daily_attendance_id',
        'teacher_status_id',
        'early_leave_request_id',
        'student_id',
        'recipient_phone',
        'event_type',
        'message',
        'status',
        'sent_at',
        'error_message',
        'response_body',
        'attempts',
        'last_attempt_at',
        'institution_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    public function studentDailyAttendance()
    {
        return $this->belongsTo(StudentDailyAttendance::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacherStatus()
    {
        return $this->belongsTo(TeacherStatus::class, 'teacher_status_id');
    }

    public function earlyLeaveRequest()
    {
        return $this->belongsTo(StudentEarlyLeaveRequest::class, 'early_leave_request_id');
    }
}
