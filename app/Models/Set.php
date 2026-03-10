<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Set extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'set_no',
        'office_code',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the steps for the paper trail set, ordered by step number.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(Step::class)->orderBy('step_no');
    }
}
