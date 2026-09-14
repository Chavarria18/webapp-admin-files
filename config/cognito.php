<?php

return [
    'region'        => env('AWS_COGNITO_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
    'user_pool_id'  => env('AWS_COGNITO_USER_POOL_ID'),
    'client_id'     => env('AWS_COGNITO_CLIENT_ID'),
    'client_secret' => env('AWS_COGNITO_CLIENT_SECRET'),
    'version'       => env('AWS_COGNITO_VERSION', 'latest'),
];