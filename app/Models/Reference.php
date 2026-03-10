<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    use HasFactory;
    protected $table = 'references';
    protected $fillable = [
        'employee_id',
        'name',
        'address',
        'telephone_no',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
