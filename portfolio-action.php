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

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $imageUrl = trim($_POST['image_url'] ?? '');
    $title    = trim($_POST['title'] ?? '');
    $desc     = trim($_POST['description'] ?? '');

    if ($imageUrl && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        $count = (int)(DB::fetch("SELECT COUNT(*) as n FROM portfolio_items WHERE provider_id = $1", [$profile['id']])['n'] ?? 0);
        if ($count < 20) {
            DB::query(
                "INSERT INTO portfolio_items (provider_id, image_url, title, description, sort_order) VALUES ($1, $2, $3, $4, $5)",
                [$profile['id'], $imageUrl, $title ?: null, $desc ?: null, $count + 1]
            );
        }
    }
} elseif ($action === 'delete') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    if ($itemId) {
        DB::query(
            "DELETE FROM portfolio_items WHERE id = $1 AND provider_id = $2",
            [$itemId, $profile['id']]
        );
    }
}

header('Location: /portfolio.php');
exit;
