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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'azure' => [
        'storage' => [
            'endpoint' => env('AZURE_STORAGE_API_ENDPOINT'),
            'read_sas_token' => env('AZURE_STORAGE_SAS_TOKEN'),
            'write_sas_token' => env('AZURE_STORAGE_PUT_SAS_TOKEN'),
            'delete_sas_token' => env('AZURE_STORAGE_DEL_SAS_TOKEN'),
        ],
        'maps' => [
            'endpoint' => env('AZURE_MAPS_ENDPOINT', 'https://atlas.microsoft.com/search/address/reverse/json'),
            'subscription_key' => env('AZURE_MAPS_SUBSCRIPTION_KEY'),
        ],
    ],

];
