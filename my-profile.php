<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireProvider();
$user    = currentUser();
$profile = DB::fetch("SELECT * FROM provider_profiles WHERE user_id = $1", [$user['id']]);

if ($profile) {
    header('Location: /p/' . $profile['slug']);
} else {
    header('Location: /edit-profile.php');
}
exit;
