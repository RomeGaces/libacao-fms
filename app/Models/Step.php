<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Step extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'set_id',
        'office_code_id',
        'step_no',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the set that this step belongs to.
     */
    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }

    /**
     * Get the Office Code that owns this step.
     * * *** This is the missing method that fixes the error ***
     */
    public function officeCode(): BelongsTo
    {
        // This assumes your OfficeCode model is App\Models\OfficeCode
        return $this->belongsTo(OfficeCode::class, 'office_code_id');
    }

    /**
     * Get the internal steps for this step.
     */
    public function internalSteps(): HasMany
    {
        return $this->hasMany(InternalStep::class);
    }

    public function paperTrailStatuses()
    {
        return $this->hasMany(PaperTrailStatus::class);
    }

}
