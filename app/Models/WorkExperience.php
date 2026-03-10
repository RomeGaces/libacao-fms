<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    use HasFactory;
    protected $table = 'work_experiences';
    protected $fillable = [
        'employee_id',
        'period_from',
        'period_to',
        'position_title',
        'department_agency',
        'monthly_salary',
        'salary_grade',
        'status_of_appointment',
        'is_gov_service',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
