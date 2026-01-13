<?php

return [

    'default' => env('AI_PROVIDER', 'deepseek'),


    'providers' => [
        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY'),
            'api_url' => 'https://api.deepseek.com/v1/chat/completions',
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'max_tokens' => 2000,
            'temperature' => 0.7,
        ],
    ],


    'rate_limits' => [
        'requests_per_minute' => 60,
        'tokens_per_minute' => 90000,
    ],


    'cache' => [
        'ttl' => 3600, // 1 hour default
        'analysis_ttl' => 7200, // 2 hours for analysis
    ],


    'mock_mode' => env('AI_MOCK_MODE', false),
];