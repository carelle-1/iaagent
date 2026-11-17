<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserAgentCalendarEntry extends Model
{
    protected $fillable = [
        'user_agent_id',
        'published_at',
        'platform',
        'pillar',
        'title',
        'caption',
        'generated_content',
        'visual_prompt',
        'hashtags',
        'status',
        'model',
        'meta_account_id',
        'platform_post_id',
        'published_permalink',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function userAgent(): BelongsTo
    {
        return $this->belongsTo(UserAgent::class);
    }

    public function visuals(): HasMany
    {
        return $this->hasMany(UserAgentPublicationVisual::class, 'calendar_entry_id')->latest();
    }
}