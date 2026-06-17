<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

function savePushSubscription(int $userId, string $endpoint, string $p256dh, string $auth): void {
    DB::query("
        INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth)
        VALUES ($1, $2, $3, $4)
        ON CONFLICT (endpoint) DO UPDATE SET user_id = $1, p256dh = $3, auth = $4
    ", [$userId, $endpoint, $p256dh, $auth]);
}

function deletePushSubscription(string $endpoint): void {
    DB::query("DELETE FROM push_subscriptions WHERE endpoint = $1", [$endpoint]);
}

/**
 * Encola y envía un push a un conjunto de filas de push_subscriptions, podando las inválidas.
 * Devuelve el número de suscripciones a las que se intentó enviar.
 */
function dispatchPushToSubscriptions(array $subscriptions, string $title, string $body, string $url = '/'): int {
    if (!VAPID_PUBLIC_KEY || !VAPID_PRIVATE_KEY || !$subscriptions) {
        return 0;
    }

    $webPush = new WebPush([
        'VAPID' => [
            'subject'    => VAPID_SUBJECT,
            'publicKey'  => VAPID_PUBLIC_KEY,
            'privateKey' => VAPID_PRIVATE_KEY,
        ],
    ]);

    $payload = json_encode([
        'title' => $title,
        'body'  => $body,
        'url'   => $url,
    ], JSON_UNESCAPED_UNICODE);

    foreach ($subscriptions as $sub) {
        $webPush->queueNotification(
            Subscription::create([
                'endpoint' => $sub['endpoint'],
                'keys' => [
                    'p256dh' => $sub['p256dh'],
                    'auth'   => $sub['auth'],
                ],
            ]),
            $payload
        );
    }

    foreach ($webPush->flush() as $report) {
        if (!$report->isSuccess() && in_array($report->getResponse()?->getStatusCode(), [404, 410], true)) {
            deletePushSubscription($report->getEndpoint());
        }
    }

    return count($subscriptions);
}

/**
 * Envía una notificación push a todas las suscripciones activas de un usuario.
 */
function sendPushToUser(int $userId, string $title, string $body, string $url = '/'): void {
    $subscriptions = DB::fetchAll("SELECT * FROM push_subscriptions WHERE user_id = $1", [$userId]);
    dispatchPushToSubscriptions($subscriptions, $title, $body, $url);
}

/**
 * Envía una notificación push a todos los usuarios suscritos, opcionalmente filtrando por rol.
 * Devuelve el número de suscripciones a las que se intentó enviar.
 */
function sendPushBroadcast(string $title, string $body, string $url = '/', ?string $role = null): int {
    $sql = "SELECT ps.* FROM push_subscriptions ps JOIN users u ON u.id = ps.user_id";
    $params = [];
    if ($role) {
        $sql .= " WHERE u.role = $1";
        $params[] = $role;
    }
    $subscriptions = DB::fetchAll($sql, $params);
    return dispatchPushToSubscriptions($subscriptions, $title, $body, $url);
}

/**
 * Si una solicitud de contacto pasa a estado "replied", avisa al cliente (si tiene cuenta).
 * Debe llamarse ANTES de actualizar el estado en la base de datos.
 */
function notifyClientIfReplied(int $contactId, string $newStatus): void {
    if ($newStatus !== 'replied') {
        return;
    }

    $contact = DB::fetch("
        SELECT cr.status, cr.requester_user_id, pp.full_name AS provider_name
        FROM contact_requests cr
        JOIN provider_profiles pp ON pp.id = cr.provider_id
        WHERE cr.id = $1
    ", [$contactId]);

    if (!$contact || $contact['status'] === 'replied' || !$contact['requester_user_id']) {
        return;
    }

    sendPushToUser(
        (int)$contact['requester_user_id'],
        'Tu solicitud fue respondida',
        $contact['provider_name'] . ' respondió tu mensaje en Kontactanos.',
        '/dashboard.php'
    );
}
