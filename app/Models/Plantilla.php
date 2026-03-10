<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    use HasFactory;

    protected $fillable = [
        'plantilla_item_number',
        'salary_grade',
        'step',
        'eligibility_requirement',
        'educational_requirement',
        'department_id',
        'division_id',
        'experience',
    ];

    protected $appends = ['monthly_rate'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function getMonthlyRateAttribute()
    {
        if (!$this->salary_grade || !$this->step) return null;

        $schedule = SalarySchedule::where('salary_grade', $this->salary_grade)->first();
        if ($schedule) {
            $stepColumn = 'step_' . $this->step;
            return $schedule->$stepColumn;
        }
        return null;
    }
}
