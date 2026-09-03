<?php

namespace App\Observers;

use App\Models\GradeHistory;
use App\Models\StudentGrade;
use Illuminate\Support\Facades\Auth;

class StudentGradeObserver
{
    /**
     * Handle the StudentGrade "updated" event.
     *
     * Log perubahan score dan/atau note ke tabel grade_histories
     * sebagai audit trail.
     */
    public function updated(StudentGrade $studentGrade): void
    {
        $scoreChanged = $studentGrade->wasChanged('score');
        $noteChanged = $studentGrade->wasChanged('note');

        if (! $scoreChanged && ! $noteChanged) {
            return;
        }

        GradeHistory::create([
            'student_grade_id' => $studentGrade->id,
            'changed_by'       => Auth::id(),
            'old_score'        => $scoreChanged ? $studentGrade->getOriginal('score') : null,
            'new_score'        => $scoreChanged ? $studentGrade->score : null,
            'old_note'         => $noteChanged ? $studentGrade->getOriginal('note') : null,
            'new_note'         => $noteChanged ? $studentGrade->note : null,
        ]);
    }
}
