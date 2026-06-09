<?php
define('APP_NAME', 'Kontactanos');
define('APP_TAGLINE', 'Conectamos Cerca');
define('APP_URL', 'http://kontactanos.test');
define('APP_DOMAIN', 'kontactanos.com');
define('APP_VERSION', '1.0.0');

// PostgreSQL Database
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'kontactanos');
define('DB_USER', 'postgres');
define('DB_PASS', 'Olanchano.3');

// Session
define('SESSION_LIFETIME', 86400 * 7);
define('COOKIE_SECURE', false);

// Uploads
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', APP_URL . '/assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Pagination
define('PER_PAGE', 12);

// Brand colors
define('BRAND_PRIMARY', '#15803d');
define('BRAND_SECONDARY', '#4ade80');

// Unsplash placeholder (sin API key, usando source.unsplash.com)
define('UNSPLASH_BASE', 'https://images.unsplash.com');

// Google OAuth 2.0
// Obtén tus credenciales en: https://console.cloud.google.com/apis/credentials
define('GOOGLE_CLIENT_ID',     getenv('GOOGLE_CLIENT_ID')     ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
define('GOOGLE_REDIRECT_URI',  APP_URL . '/oauth/callback.php');
