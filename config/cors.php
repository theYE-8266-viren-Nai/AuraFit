<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',       // ⭐ CRITICAL: Add this
        'register',    // ⭐ CRITICAL: Add this
        'logout',      // ⭐ CRITICAL: Add this
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'https://aura-fit-frontend.vercel.app',        // ⭐ Add Vercel
        'https://brilliant-boba-e75a8d.netlify.app',   // ⭐ Add Netlify
    ],

    'allowed_origins_patterns' => [
        '/\.vercel\.app$/',
        '/\.netlify\.app$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];