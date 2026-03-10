<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'audit_logs';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'changes' => 'array',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'auditable_id',
        'auditable_type',
        'changes',
        'remarks',
        'updated_by',
        'updated_at',
    ];

    /**
     * Get the user who made the change.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by', 'employee_id');
    }

    /**
     * Get the auditable model (polymorphic relationship).
     */
    public function auditable()
    {
        return $this->morphTo();
    }
}