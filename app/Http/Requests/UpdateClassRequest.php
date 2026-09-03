<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $classId = $this->route('class');
        $institutionId = Auth::user()?->institution_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('classes')->where(function ($query) use ($institutionId) {
                    return $query->where('grade_level', $this->grade_level)
                                 ->where('academic_year', $this->academic_year)
                                 ->where('institution_id', $institutionId);
                })->ignore($classId)
            ],
            'major' => 'required|string|max:255',
            'grade_level' => 'required|in:X,XI,XII',
            'academic_year' => 'required|string',
            'homeroom_teacher_id' => [
                'nullable',
                'exists:users,id',
                \Illuminate\Validation\Rule::unique('classes')->where(function ($query) use ($institutionId) {
                    return $query->where('is_active', true)
                                 ->where('institution_id', $institutionId);
                })->ignore($classId)
            ],
            'description' => 'nullable|string|max:500'
        ];
    }
}
