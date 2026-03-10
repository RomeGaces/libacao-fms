<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eligibility extends Model
{
    use HasFactory;
    protected $table = 'eligibilities';
    protected $fillable = [
        'employee_id',
        'eligibility',
        'rating',
        'exam_date',
        'exam_place',
        'license_number',
        'license_validity',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
