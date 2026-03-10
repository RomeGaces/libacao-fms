<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;
    protected $table = 'employees';
    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'name_extension',
        'birth_date',
        'place_of_birth',
        'sex',
        'civil_status',
        'citizenship',
        'height',
        'weight',
        'blood_type',
        'gsis_id_no',
        'pagibig_id_no',
        'philhealth_no',
        'sss_no',
        'tin_no',
        'agency_employee_no',
        'residential_address',
        'residential_zip',
        'permanent_address',
        'permanent_zip',
        'telephone_no',
        'mobile_no',
        'email',
        'ctc_number',
        'ctc_place_of_issuance',
        'ctc_date_of_issuance',
    ];

    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function eligibilities()
    {
        return $this->hasMany(Eligibility::class);
    }

    public function workExperiences()
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function voluntaryWorks()
    {
        return $this->hasMany(VoluntaryWork::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }

    public function otherInfos()
    {
        return $this->hasMany(OtherInfo::class);
    }

    public function references()
    {
        return $this->hasMany(Reference::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function stepAccess(): HasMany
    {
        return $this->hasMany(EmployeeStepAccess::class);
    }
}