<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
         'key_secours' => env('GEMINI_API_KEY_SECOURS'),
    ],
    'monetbil' => [
      'service_key' => env('MONETBIL_SERVICE_KEY'),
      'service_secret' => env('MONETBIL_SERVICE_SECRET'),
      'api_url' => env('MONETBIL_API_URL', 'https://api.monetbil.com/widget/v2.1'),
      'devise' => env('MONETBIL_DEVISE', 'XAF'),
      'pays' => env('MONETBIL_PAYS', 'CM'),
      'notify_url' => env('MONETBIL_NOTIFY_URL'),
      'return_url' => env('MONETBIL_RETURN_URL'),
      'abonnement_montant' => (int) env('ABONNEMENT_PREMIUM_MONTANT', 1000),
      'abonnement_duree_jours' => (int) env('ABONNEMENT_PREMIUM_DUREE_JOURS', 30),
 ],
];
