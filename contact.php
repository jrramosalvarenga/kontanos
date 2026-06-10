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
$name       = trim($_POST['requester_name'] ?? '');
$email      = trim($_POST['requester_email'] ?? '');
$phone      = trim($_POST['requester_phone'] ?? '');
$message    = trim($_POST['message'] ?? '');

$provider = DB::fetch("SELECT * FROM provider_profiles WHERE id = $1", [$providerId]);
if (!$provider || !$name || !$email || !$message) {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/') . '?error=' . urlencode('Por favor completa todos los campos requeridos.'));
    exit;
}

DB::query("
    INSERT INTO contact_requests (provider_id, requester_user_id, requester_name, requester_email, requester_phone, message)
    VALUES ($1, $2, $3, $4, $5, $6)
", [
    $providerId,
    isLoggedIn() ? $_SESSION['user_id'] : null,
    $name,
    $email,
    $phone ?: null,
    $message,
]);

// Mark contact as new (update provider)
DB::query("UPDATE provider_profiles SET updated_at = NOW() WHERE id = $1", [$providerId]);

header('Location: /p/' . $provider['slug'] . '?success=' . urlencode('¡Mensaje enviado! ' . explode(' ', $provider['full_name'])[0] . ' te contactará pronto.'));
exit;
