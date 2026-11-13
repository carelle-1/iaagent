<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAgentIdentity extends Model
{
    protected $fillable = [
        'user_agent_id',
        'objective',
        'palette',
        'charter',
        'logo_path',
    ];

    protected $casts = [
        'palette' => 'array',
    ];

    public function userAgent(): BelongsTo
    {
        return $this->belongsTo(UserAgent::class);
    }
}