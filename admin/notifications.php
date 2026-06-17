<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/push.php';
requireAdmin();

$message = '';
$sentCount = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title  = trim($_POST['title'] ?? '');
    $body   = trim($_POST['body'] ?? '');
    $url    = trim($_POST['url'] ?? '') ?: '/';
    $target = $_POST['target'] ?? 'all';
    $role   = in_array($target, ['provider', 'client'], true) ? $target : null;

    if (!preg_match('/^\/[a-zA-Z0-9\/_\-\.?=&%]*$/', $url)) {
        $url = '/';
    }

    if ($title && $body) {
        $sentCount = sendPushBroadcast($title, $body, $url, $role);
        $message = "Notificación enviada a {$sentCount} suscripción(es).";
    } else {
        $message = 'El título y el mensaje son obligatorios.';
    }
}

$subCounts = DB::fetch("
    SELECT
        COUNT(*) AS total,
        COUNT(*) FILTER (WHERE u.role = 'provider') AS providers,
        COUNT(*) FILTER (WHERE u.role = 'client') AS clients
    FROM push_subscriptions ps
    JOIN users u ON u.id = ps.user_id
");

$pageTitle = 'Notificaciones Push';
$activeNav = 'notifications';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="max-w-2xl">
    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-xl <?= $sentCount !== null ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
        <?= e($message) ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <p class="text-sm text-gray-500">
            Suscripciones activas: <strong><?= (int)$subCounts['total'] ?></strong>
            (<?= (int)$subCounts['providers'] ?> profesionales, <?= (int)$subCounts['clients'] ?> clientes)
        </p>
    </div>

    <form method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        <input type="hidden" name="_token" value="<?= csrfToken() ?>">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Destinatarios</label>
            <select name="target" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-200 focus:border-brand-400 outline-none">
                <option value="all">Todos los usuarios</option>
                <option value="provider">Solo profesionales</option>
                <option value="client">Solo clientes</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Título</label>
            <input type="text" name="title" required maxlength="100"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-200 focus:border-brand-400 outline-none"
                   placeholder="Ej: ¡Nueva función disponible!">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Mensaje</label>
            <textarea name="body" required maxlength="200" rows="3"
                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-200 focus:border-brand-400 outline-none"
                      placeholder="Cuéntales qué hay de nuevo..."></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">URL al hacer clic (opcional)</label>
            <input type="text" name="url" placeholder="/search.php"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-200 focus:border-brand-400 outline-none">
        </div>

        <button type="submit" class="btn-primary w-full sm:w-auto">Enviar Notificación</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
