<?php

$origins = [env('FRONTEND_URL', 'http://localhost:3000')];

// 追加オリジン（カンマ区切りで複数指定可）
if (env('FRONTEND_URL_EXTRA')) {
    $extras = array_map('trim', explode(',', env('FRONTEND_URL_EXTRA')));
    $origins = array_merge($origins, $extras);
}

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => $origins,
    'allowed_origins_patterns' => [
        // Vercelの全デプロイURLを許可
        '#^https://.*\.vercel\.app$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 86400,
    'supports_credentials' => true,
];
