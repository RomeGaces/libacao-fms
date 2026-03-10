<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObjectExpenditure extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'group_id',
        'object_expenditure',
        'account_code',
    ];

    public function group()
    {
        return $this->belongsTo(GroupObjectExpenditure::class, 'group_id');
    }
}
