<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/push.php';

requireProvider();
verifyCsrf();

$user    = currentUser();
$profile = DB::fetch("SELECT id FROM provider_profiles WHERE user_id = $1", [$user['id']]);

if ($profile) {
    $contactId = (int)($_POST['contact_id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? '';
    if ($contactId && in_array($newStatus, ['pending', 'read', 'replied', 'closed'])) {
        notifyClientIfReplied($contactId, $newStatus);
        DB::query(
            "UPDATE contact_requests SET status = $1,
                replied_at = COALESCE(replied_at, CASE WHEN $1 IN ('replied', 'closed') THEN NOW() END)
             WHERE id = $2 AND provider_id = $3",
            [$newStatus, $contactId, $profile['id']]
        );
    }
}

$back = $_POST['redirect_to'] ?? '/inbox.php';
if (!preg_match('/^\/[a-zA-Z0-9\/_\-\.?=&%]*$/', $back)) {
    $back = '/dashboard.php';
}
header('Location: ' . $back);
exit;
