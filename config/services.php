<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

'meta' => [
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'graph_version' => env('META_GRAPH_VERSION', 'v21.0'),
    ],

    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
        'model' => env('OLLAMA_MODEL', 'qwen3:4b'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 420),
    ],

    'comfyui' => [
        'url' => env('COMFYUI_URL', 'http://127.0.0.1:8188'),
        'checkpoint' => env('COMFYUI_CHECKPOINT', 'v1-5-pruned-emaonly.safetensors'),
        'steps' => (int) env('COMFYUI_STEPS', 15),
        'cfg' => (float) env('COMFYUI_CFG', 7),
        'width' => (int) env('COMFYUI_WIDTH', 512),
        'height' => (int) env('COMFYUI_HEIGHT', 512),
        'sampler' => env('COMFYUI_SAMPLER', 'euler'),
        'scheduler' => env('COMFYUI_SCHEDULER', 'normal'),
        'timeout' => (int) env('COMFYUI_TIMEOUT', 14400),
        'negative_prompt' => env('COMFYUI_NEGATIVE_PROMPT', 'text, watermark, logo, signature, low quality, blurry, deformed, extra limbs, bad anatomy, cropped, out of frame, worst quality, jpeg artifacts, ugly, duplicate, morbid, mutilated, mutation, deformed, ugly, bad anatomy, bad proportions, extra limbs, cloned face, disfigured, gross proportions, malformed limbs, missing arms, missing legs, extra arms, extra legs, fused fingers, too many fingers, long neck, username, watermark, signature, text, title, subtitle, date, time, header, footer, margin, border, frame, grid, lines, boxes, ui, interface, screen, monitor, display, browser, window, menu, button, icon, cursor, pointer, scrollbar, toolbar, address bar, tab, bookmark'),
    ],

];
