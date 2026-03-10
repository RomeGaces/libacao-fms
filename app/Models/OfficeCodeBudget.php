<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeCodeBudget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'office_code_id',
        'annual_budget_id',
        'budget',
    ];

    /**
     * Get the parent annual budget.
     */
    public function annualBudget(): BelongsTo
    {
        return $this->belongsTo(AnnualBudget::class);
    }

    /**
     * Get the associated office code.
     * Assumes you have an OfficeCode model.
     */
    public function officeCode(): BelongsTo
    {
        return $this->belongsTo(OfficeCode::class);
    }
}