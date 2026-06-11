<?php
// Local overrides (gitignored) - credenciales y config por entorno
if (file_exists(__DIR__ . '/local.php')) {
    require_once __DIR__ . '/local.php';
}

define('APP_NAME', 'Kontactanos');
define('APP_TAGLINE', 'Conectamos Cerca');
if (!defined('APP_URL')) {
    define('APP_URL', getenv('APP_URL') ?: 'http://kontactanos.test');
}
define('APP_DOMAIN', 'kontanos.com');
define('APP_VERSION', '1.0.0');

// PostgreSQL Database
// Si existe DATABASE_URL (Render la inyecta al vincular la BD), se usa esa.
$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl) {
    $dbParts = parse_url($databaseUrl);
    define('DB_HOST', $dbParts['host']);
    define('DB_PORT', (string)($dbParts['port'] ?? '5432'));
    define('DB_NAME', ltrim($dbParts['path'], '/'));
    define('DB_USER', $dbParts['user']);
    define('DB_PASS', $dbParts['pass']);
} else {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', getenv('DB_PORT') ?: '5432');
    define('DB_NAME', getenv('DB_NAME') ?: 'kontactanos');
    define('DB_USER', getenv('DB_USER') ?: 'postgres');
    define('DB_PASS', getenv('DB_PASS') ?: 'Olanchano.3');
}

// Session
define('SESSION_LIFETIME', 86400 * 7);
define('COOKIE_SECURE', getenv('COOKIE_SECURE') === '1');

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
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
}
define('GOOGLE_REDIRECT_URI',  APP_URL . '/oauth/callback.php');

// Facebook Login
// Obtén tus credenciales en: https://developers.facebook.com/apps
if (!defined('FACEBOOK_APP_ID')) {
    define('FACEBOOK_APP_ID', getenv('FACEBOOK_APP_ID') ?: '');
}
if (!defined('FACEBOOK_APP_SECRET')) {
    define('FACEBOOK_APP_SECRET', getenv('FACEBOOK_APP_SECRET') ?: '');
}
define('FACEBOOK_REDIRECT_URI', APP_URL . '/oauth/facebook-callback.php');
