<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/push.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || empty($data['_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $data['_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Token CSRF inválido']);
    exit;
}

$endpoint = $data['endpoint'] ?? '';
$keys     = $data['keys'] ?? [];
$p256dh   = $keys['p256dh'] ?? '';
$auth     = $keys['auth'] ?? '';

if (!$endpoint || !$p256dh || !$auth) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos de suscripción incompletos']);
    exit;
}

savePushSubscription((int)$_SESSION['user_id'], $endpoint, $p256dh, $auth);
echo json_encode(['success' => true]);
