<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAgentContact extends Model
{
    protected $fillable = [
        'user_agent_id',
        'name',
        'phone',
        'email',
        'notes',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function userAgent(): BelongsTo
    {
        return $this->belongsTo(UserAgent::class);
    }
}