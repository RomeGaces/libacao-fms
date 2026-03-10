<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_grade',
        'step_1',
        'step_2',
        'step_3',
        'step_4',
        'step_5',
        'step_6',
        'step_7',
        'step_8',
    ];

    protected $casts = [
        'step_1' => 'decimal:2',
        'step_2' => 'decimal:2',
        'step_3' => 'decimal:2',
        'step_4' => 'decimal:2',
        'step_5' => 'decimal:2',
        'step_6' => 'decimal:2',
        'step_7' => 'decimal:2',
        'step_8' => 'decimal:2',
    ];
}
