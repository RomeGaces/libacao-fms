<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObrObject extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'obr_objects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'obr_id',
        'object_expenditure_id',
        'amount',
    ];

    /**
     * Get the OBR request that this object belongs to.
     */
    public function obrRequest(): BelongsTo
    {
        return $this->belongsTo(ObrRequest::class, 'obr_id');
    }
}