<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/push.php';
requireAdmin();

$message = '';
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title    = trim($_POST['title'] ?? '');
    $body     = trim($_POST['body'] ?? '');
    $url      = trim($_POST['url'] ?? '') ?: '/';
    $target   = $_POST['target'] ?? 'provider';
    $role     = in_array($target, ['provider', 'client'], true) ? $target : null;
    $viaPush  = !empty($_POST['channel_push']);
    $viaEmail = !empty($_POST['channel_email']);

    if (!preg_match('/^\/[a-zA-Z0-9\/_\-\.?=&%]*$/', $url)) {
        $url = '/';
    }

    if (!$title || !$body) {
        $message = 'El título y el mensaje son obligatorios.';
    } elseif (!$viaPush && !$viaEmail) {
        $message = 'Elige al menos un canal de envío.';
    } else {
        if ($viaPush) {
            $n = sendPushBroadcast($title, $body, $url, $role);
            $results[] = "{$n} notificación(es) push";
        }
        if ($viaEmail) {
            $ctaUrl = $url !== '/' ? (APP_URL . $url) : null;
            $n = sendEmailBroadcast($title, $body, $ctaUrl, 'Ver en Kontactanos', $role);
            $results[] = "{$n} email(s)";
        }
        $message = 'Enviado: ' . implode(' y ', $results) . '.';
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

$userCounts = DB::fetch("
    SELECT
        COUNT(*) FILTER (WHERE role = 'provider') AS providers,
        COUNT(*) FILTER (WHERE role = 'client') AS clients
    FROM users WHERE is_active = TRUE
");

$pageTitle = 'Comunicación con Usuarios';
$activeNav = 'notifications';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="max-w-2xl">
    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-xl <?= $results ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
        <?= e($message) ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6 grid grid-cols-2 gap-4 text-sm text-gray-500">
        <div>
            <strong class="text-gray-900 block text-lg"><?= (int)$userCounts['providers'] ?></strong>
            profesionales registrados
        </div>
        <div>
            <strong class="text-gray-900 block text-lg"><?= (int)$subCounts['providers'] ?></strong>
            con notificaciones push activas
        </div>
    </div>

    <form method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        <input type="hidden" name="_token" value="<?= csrfToken() ?>">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Destinatarios</label>
            <select name="target" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-200 focus:border-brand-400 outline-none">
                <option value="provider" selected>Solo profesionales (emprendedores)</option>
                <option value="client">Solo clientes</option>
                <option value="all">Todos los usuarios</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Canal de envío</label>
            <div class="flex flex-col gap-2">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="channel_email" value="1" checked class="rounded border-gray-300 text-brand-600 focus:ring-brand-400">
                    Email (llega a todos, sin importar si activaron notificaciones)
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="channel_push" value="1" checked class="rounded border-gray-300 text-brand-600 focus:ring-brand-400">
                    Push (solo a quienes activaron notificaciones en el sitio)
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Título</label>
            <input type="text" name="title" required maxlength="100"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-200 focus:border-brand-400 outline-none"
                   placeholder="Ej: ¡Nueva función disponible!">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Mensaje</label>
            <textarea name="body" required maxlength="1000" rows="5"
                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-200 focus:border-brand-400 outline-none"
                      placeholder="Cuéntales qué hay de nuevo..."></textarea>
            <p class="text-xs text-gray-400 mt-1">En push solo se muestran los primeros ~200 caracteres; en email se ve completo.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">URL al hacer clic (opcional)</label>
            <input type="text" name="url" placeholder="/search.php"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-200 focus:border-brand-400 outline-none">
        </div>

        <button type="submit" class="btn-primary w-full sm:w-auto">Enviar</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
