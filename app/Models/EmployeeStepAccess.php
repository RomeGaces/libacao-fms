<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeStepAccess extends Model
{
    use HasFactory;

    protected $table = 'employee_step_access';

    protected $fillable = [
        'employee_id',
        'internal_step_id',
        'has_access',
    ];

    protected $casts = [
        'has_access' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function internalStep(): BelongsTo
    {
        return $this->belongsTo(InternalStep::class);
    }
}