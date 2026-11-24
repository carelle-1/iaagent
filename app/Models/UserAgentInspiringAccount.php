<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAgentInspiringAccount extends Model
{
    protected $fillable = [
        'user_agent_id',
        'platform',
        'handle',
        'url',
        'notes',
        'style',
    ];

    public function userAgent(): BelongsTo
    {
        return $this->belongsTo(UserAgent::class);
    }
}