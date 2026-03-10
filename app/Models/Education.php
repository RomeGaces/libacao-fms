<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;
    protected $table = 'educations';
    protected $fillable = [
        'employee_id',
        'level',
        'school_name',
        'degree_course',
        'year_graduated',
        'units_earned',
        'from',
        'to',
        'honors',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
