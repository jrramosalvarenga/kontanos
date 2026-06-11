<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

if (!FACEBOOK_APP_ID) {
    header('Location: /login.php?error=' . urlencode('Facebook Login no está configurado.'));
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_role']  = in_array($_GET['role'] ?? '', ['provider', 'client']) ? $_GET['role'] : 'client';
$_SESSION['oauth_ref']   = trim($_GET['ref'] ?? '');

$params = http_build_query([
    'client_id'     => FACEBOOK_APP_ID,
    'redirect_uri'  => FACEBOOK_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'email,public_profile',
    'state'         => $state,
]);

header('Location: https://www.facebook.com/v19.0/dialog/oauth?' . $params);
exit;
