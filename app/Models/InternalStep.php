<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternalStep extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'step_id',
        'approval_title',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the step that this internal step belongs to.
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(Step::class);
    }
}
