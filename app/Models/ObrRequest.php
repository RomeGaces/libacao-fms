<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\ObrRejection;
use App\Models\ObrRequestArchive;

class ObrRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'obr_request';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_id',
        'obr_no',
        'office_address',
        'is_archived', // Added for archive functionality
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_archived' => 'boolean', // Cast to boolean for proper handling
    ];

    /**
     * The attributes that should be set to default values.
     *
     * @var array
     */
    protected $attributes = [
        'is_archived' => false, // Default value
    ];

    /**
     * Get the PAO request that owns this OBR.
     */
    public function paoRequest(): BelongsTo
    {
        return $this->belongsTo(PaoRequest::class, 'request_id');
    }

    /**
     * Get the expenditure objects associated with this OBR.
     */
    public function obrObjects(): HasMany
    {
        return $this->hasMany(ObrObject::class, 'obr_id');
    }

    /**
     * Get the paper trail status (single/first).
     */
    public function paperTrailStatus()
    {
        return $this->hasOne(PaperTrailStatus::class, 'request_id');
    }

    /**
     * Get the most recent paper trail status associated with the OBR.
     */
    public function latestStatus(): HasOne
    {
        return $this->hasOne(PaperTrailStatus::class, 'request_id')->latestOfMany();
    }

    /**
     * Get all rejections for this OBR request.
     */
    public function rejections(): HasMany
    {
        return $this->hasMany(ObrRejection::class, 'request_id');
    }

    /**
     * Get all archive records for this OBR request.
     */
    public function archives(): HasMany
    {
        return $this->hasMany(ObrRequestArchive::class, 'obr_request_id');
    }

    /**
     * Get the latest archive record for this OBR request.
     */
   public function latestArchive(): HasOne
    {
        return $this->hasOne(ObrRequestArchive::class, 'obr_request_id')->latestOfMany('archived_at');
    }
}