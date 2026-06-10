<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

if (!GOOGLE_CLIENT_ID) {
    header('Location: /login.php?error=' . urlencode('Google OAuth no está configurado.'));
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_role']  = in_array($_GET['role'] ?? '', ['provider', 'client']) ? $_GET['role'] : 'client';

$params = http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'state'         => $state,
    'access_type'   => 'online',
    'prompt'        => 'select_account',
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;
