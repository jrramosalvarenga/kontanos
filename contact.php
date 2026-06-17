<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/push.php';

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

$provider = DB::fetch("
    SELECT pp.*, u.email AS user_email, u.id AS user_id_owner
    FROM provider_profiles pp
    JOIN users u ON u.id = pp.user_id
    WHERE pp.id = $1
", [$providerId]);

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

// Send email notification to provider
if (!empty($provider['user_email'])) {
    $providerFirstName = explode(' ', $provider['full_name'])[0];
    $html = buildContactNotificationEmail(
        $providerFirstName,
        ['requester_name' => $name, 'requester_email' => $email, 'requester_phone' => $phone, 'message' => $message],
        APP_URL . '/inbox.php'
    );
    sendMail($provider['user_email'], $provider['full_name'], "Nuevo mensaje de {$name} en Kontactanos", $html);
}

// Send push notification to provider
sendPushToUser(
    (int)$provider['user_id_owner'],
    'Nuevo mensaje de ' . $name,
    mb_strimwidth($message, 0, 100, '...'),
    '/inbox.php'
);

header('Location: /p/' . $provider['slug'] . '?success=' . urlencode('¡Mensaje enviado! ' . explode(' ', $provider['full_name'])[0] . ' te contactará pronto.'));
exit;
