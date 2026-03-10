<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'gsis_id',      // make sure this is fillable
        'name',
        'email',
        'employee_id',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    protected $appends = [
        'department_details'
    ];


    // Automatically hash passwords
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function username()
    {
        return 'gsis_id';
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->employee->department();
    }

        /**
     * Accessor for the 'department_details' attribute.
     *
     * @return object|null
     */
    public function getDepartmentDetailsAttribute()
    {
        // Eager load the relationships if they aren't already
        // This is still safe to do
        $this->loadMissing('employee.department');

        if ($this->employee && $this->employee->department) {
            return [
                'id' => $this->employee->department->id,
                'name' => $this->employee->department->department_name,
                'code' => $this->employee->department->department_code
            ];
        }

        // Return null if the user has no department
        return null;
    }

}
