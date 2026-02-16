<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'tenant_id',
        'source',
        'name',
        'phone',
        'interest',
        'conversation',
        'memory',
        'score',
        'status',
        'forwarded_to',
    ];

    protected $casts = [
        'conversation' => 'array',
        'memory' => 'array',
    ];
}
