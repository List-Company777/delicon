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

    'line' => [
        'client_id'                => env('LINE_CHANNEL_ID'),
        'client_secret'            => env('LINE_CHANNEL_SECRET'),
        'redirect'                 => env('LINE_REDIRECT_URI'),
        'messaging_token'          => env('LINE_MESSAGING_CHANNEL_ACCESS_TOKEN'),
        'messaging_channel_secret' => env('LINE_MESSAGING_CHANNEL_SECRET'),
        'bot_add_friend_url'       => env('LINE_BOT_ADD_FRIEND_URL'),
    ],

    'upstage' => [
        'xml_feed_url' => env('UPSTAGE_XML_FEED_URL'),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
    ],

];
