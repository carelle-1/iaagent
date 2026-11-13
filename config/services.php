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
        'model' => env('OLLAMA_MODEL'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 420),
    ],

    'comfyui' => [
        'url' => env('COMFYUI_URL', 'http://127.0.0.1:8188'),
        'checkpoint' => env('COMFYUI_CHECKPOINT', 'v1-5-pruned-emaonly.safetensors'),
        'steps' => (int) env('COMFYUI_STEPS', 10),
        'cfg' => (float) env('COMFYUI_CFG', 7),
        'width' => (int) env('COMFYUI_WIDTH', 512),
        'height' => (int) env('COMFYUI_HEIGHT', 512),
        'sampler' => env('COMFYUI_SAMPLER', 'euler'),
        'scheduler' => env('COMFYUI_SCHEDULER', 'normal'),
        'timeout' => (int) env('COMFYUI_TIMEOUT', 300),
        'negative_prompt' => env('COMFYUI_NEGATIVE_PROMPT', 'text, watermark, logo, signature, low quality, blurry, deformed, extra limbs, bad anatomy, cropped, out of frame, worst quality, jpeg artifacts'),
    ],

];
