<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- ADDED
use Illuminate\Database\Eloquent\Relations\HasMany;   // <-- ADDED

// --- Make sure you import all your models ---
use App\Models\Department;
use App\Models\User;
use App\Models\PaoGroup;
use App\Models\OfficeCodeBudget;
use App\Models\OfficeCode; // <-- ADDED (assuming this is your model name)


class PaoRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pao_requests';

    protected $fillable = [
        'office_code_id',
        'office_code_budget_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Department relationship
     */
    public function department(): BelongsTo // <-- Added return type
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * User who created this request
     */
    public function creator(): BelongsTo // <-- Added return type
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Groups under this request
     */
    public function groups(): HasMany // <-- Added return type
    {
        return $this->hasMany(PaoGroup::class, 'request_id');
    }

    /**
     * Office Code Budget relationship (Optional but recommended)
     */
    public function officeCodeBudget(): BelongsTo // <-- Added return type
    {
        return $this->belongsTo(OfficeCodeBudget::class);
    }
 

    /**
     * Get the office code associated with this request.
     */
    public function officeCode(): BelongsTo
    {
        // Assumes your model is 'OfficeCode' and foreign key is 'office_code_id'
        return $this->belongsTo(OfficeCode::class, 'office_code_id');
    }
}