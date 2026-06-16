<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireProvider();
verifyCsrf();

$user = currentUser();
$url  = trim($_POST['avatar_url'] ?? '');

if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
    $url = '';
}

DB::query(
    "UPDATE provider_profiles SET avatar_url = $1, updated_at = NOW() WHERE user_id = $2",
    [$url ?: null, $user['id']]
);

header('Location: /dashboard.php');
exit;
