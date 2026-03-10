<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoluntaryWork extends Model
{
    use HasFactory;
    protected $table = 'voluntary_works';
    protected $fillable = [
        'employee_id',
        'organization',
        'period_from',
        'period_to',
        'hours',
        'position',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
