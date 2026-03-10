<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaoGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pao_groups';

    protected $fillable = [
        'request_id',
        'group_id',
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
     * Group definition (from group_object_expenditures)
     */
    public function groupDefinition()
    {
        return $this->belongsTo(GroupObjectExpenditure::class, 'group_id');
    }

    /**
     * Objects under this group
     */
    public function objects()
    {
        return $this->hasMany(PaoObject::class, 'group_id');
    }
}
