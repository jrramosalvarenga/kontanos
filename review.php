<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /search.php');
    exit;
}

verifyCsrf();

$providerId = (int)($_POST['provider_id'] ?? 0);
$name       = trim($_POST['reviewer_name'] ?? '');
$rating     = (int)($_POST['rating'] ?? 0);
$comment    = trim($_POST['comment'] ?? '');

$provider = DB::fetch("SELECT * FROM provider_profiles WHERE id = $1", [$providerId]);
if (!$provider || !$name || !$rating || $rating < 1 || $rating > 5) {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/') . '?error=' . urlencode('Por favor completa todos los campos de la reseña.'));
    exit;
}

DB::query("
    INSERT INTO reviews (provider_id, reviewer_user_id, reviewer_name, rating, comment)
    VALUES ($1, $2, $3, $4, $5)
", [
    $providerId,
    isLoggedIn() ? $_SESSION['user_id'] : null,
    $name,
    $rating,
    $comment ?: null,
]);

header('Location: /p/' . $provider['slug'] . '?success=' . urlencode('¡Reseña publicada! Gracias por tu opinión.') . '#reviews');
exit;
