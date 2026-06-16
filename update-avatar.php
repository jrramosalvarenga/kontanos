<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireProvider();
verifyCsrf();

$user    = currentUser();
$profile = DB::fetch("SELECT id FROM provider_profiles WHERE user_id = $1", [$user['id']]);

if (!$profile) {
    header('Location: /dashboard.php');
    exit;
}

$url = null;

// File upload takes priority over URL
if (!empty($_FILES['avatar_file']['tmp_name']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
    $url = saveAvatarUpload($_FILES['avatar_file'], $user['id']);
} else {
    $raw = trim($_POST['avatar_url'] ?? '');
    if ($raw && filter_var($raw, FILTER_VALIDATE_URL)) {
        $url = $raw;
    }
}

DB::query(
    "UPDATE provider_profiles SET avatar_url = $1, updated_at = NOW() WHERE user_id = $2",
    [$url, $user['id']]
);

$back = trim($_POST['redirect_to'] ?? '/dashboard.php');
if (!preg_match('/^\/[a-zA-Z0-9\/_\-\.?=&%]*$/', $back)) {
    $back = '/dashboard.php';
}
header('Location: ' . $back);
exit;
