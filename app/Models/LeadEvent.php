<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadEvent extends Model
{
    protected $table = 'lead_events';

    protected $fillable = [
        'lead_id',
        'event_type',
        'score_delta',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
