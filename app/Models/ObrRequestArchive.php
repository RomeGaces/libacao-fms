<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObrRequestArchive extends Model
{
    use HasFactory;

    protected $fillable = [
        'obr_request_id',
        'archived_by',
        'archive_reason',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    /**
     * Get the OBR request that was archived
     */
    public function obrRequest(): BelongsTo
    {
        return $this->belongsTo(ObrRequest::class, 'obr_request_id');
    }

    /**
     * Get the user who archived this OBR
     */
    public function archivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}