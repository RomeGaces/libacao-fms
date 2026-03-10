<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObrRejection extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'rejection_details',
        'rejected_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function obrRequest()
    {
        return $this->belongsTo(ObrRequest::class, 'request_id');
    }

    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}