<?php
// Global configuration for blog
return [
    'site_name' => 'My Personal Blog',
    'storage' => getenv('BLOG_STORAGE') ?: 'file', // 'mysql' or 'file'
    'db' => [
        'host' => 'localhost',
        'name' => 'personal_blog',
        'user' => 'blog_user',
        'pass' => 'supersecret',
        'charset' => 'utf8mb4',
    ],
    'admin' => [
        'username' => 'admin',
        // Change this hash using password_hash('your-password', PASSWORD_DEFAULT)
        'password_hash' => password_hash('changeme', PASSWORD_DEFAULT),
    ],
    'api_key' => 'changeme-api-key',
    'upload' => [
        'directory' => __DIR__ . '/uploads',
        'max_size' => 2 * 1024 * 1024, // 2MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    ],
];