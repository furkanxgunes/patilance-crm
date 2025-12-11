<?php

return [
    'api_url' => env('NETGSM_API_URL', 'https://api.netgsm.com.tr/sms/send/get'),
    'username' => env('NETGSM_USERNAME'),
    'password' => env('NETGSM_PASSWORD'),
    'header' => env('NETGSM_HEADER', 'PATILANCE'),
    'charset' => 'TR',
    'language' => 'TR',
];
