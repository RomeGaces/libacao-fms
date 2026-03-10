<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaoObject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pao_objects';

    protected $fillable = [
        'request_id',
        'group_id',
        'object_expenditure_id',
        'amount',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Request relationship
     */
    public function request()
    {
        return $this->belongsTo(PaoRequest::class, 'request_id');
    }

    /**
     * Group relationship
     */
    public function group()
    {
        return $this->belongsTo(PaoGroup::class, 'group_id');
    }

    /**
     * Object expenditure definition
     */
    public function expenditure()
    {
        return $this->belongsTo(ObjectExpenditure::class, 'object_expenditure_id');
    }
}
