<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$ad = $id ? DB::fetch("SELECT link_url FROM ads WHERE id = $1", [$id]) : null;

if (!$ad || empty($ad['link_url'])) {
    header('Location: /');
    exit;
}

DB::query("UPDATE ads SET clicks = clicks + 1 WHERE id = $1", [$id]);

header('Location: ' . $ad['link_url']);
exit;
