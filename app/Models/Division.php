<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'division_name',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function plantillas()
    {
        return $this->hasMany(Plantilla::class);
    }
}
