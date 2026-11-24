<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAgentPublicationVisual extends Model
{
    protected $fillable = [
        'calendar_entry_id',
        'prompt',
        'negative_prompt',
        'image_path',
        'image_name',
        'model',
        'prompt_id',
        'client_id',
        'theme',
        'status',
        'error',
        'visual_subject',
        'visual_style',
        'aspect_ratio',
        'visual_direction',
        'comfy_filename',
        'comfy_subfolder',
        'comfy_type',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'visual_direction' => 'array',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(UserAgentCalendarEntry::class, 'calendar_entry_id');
    }
}