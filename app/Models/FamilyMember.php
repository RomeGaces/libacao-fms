<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    use HasFactory;
    protected $table = 'family_members';
    protected $fillable = [
        'employee_id',
        'relationship',
        'name',
        'birth_date',
        'occupation',
        'employer',
        'business_address',
        'telephone_no',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
