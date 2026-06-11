<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'set_status') {
        $status = $_POST['status'] ?? 'pending';
        if (in_array($status, ['pending', 'read', 'replied', 'closed'])) {
            DB::query("UPDATE contact_requests SET status = $1 WHERE id = $2", [$status, $id]);
            $message = 'Estado actualizado.';
        }
    }
}

$status = trim($_GET['status'] ?? '');
$where = '';
$params = [];
if ($status && in_array($status, ['pending', 'read', 'replied', 'closed'])) {
    $where = "WHERE cr.status = $1";
    $params[] = $status;
}

$contacts = DB::fetchAll("
    SELECT cr.*, pp.full_name as provider_name, pp.slug as provider_slug
    FROM contact_requests cr
    JOIN provider_profiles pp ON pp.id = cr.provider_id
    $where
    ORDER BY cr.created_at DESC
    LIMIT 200
", $params);

$pageTitle = 'Solicitudes de Contacto';
$activeNav = 'contacts';
require __DIR__ . '/includes/layout_header.php';
?>

<?php if ($message): ?>
<div class="bg-brand-50 border border-brand-200 text-brand-700 rounded-xl px-4 py-3 mb-6 text-sm"><?= e($message) ?></div>
<?php endif; ?>

<!-- Filter -->
<div class="flex gap-2 mb-6 overflow-x-auto pb-1">
    <a href="/admin/contacts.php" class="filter-chip <?= !$status ? 'active' : '' ?>">Todas</a>
    <?php foreach (['pending' => 'Pendientes', 'read' => 'Leídas', 'replied' => 'Respondidas', 'closed' => 'Cerradas'] as $key => $label): ?>
    <a href="/admin/contacts.php?status=<?= $key ?>" class="filter-chip <?= $status === $key ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="space-y-3">
    <?php foreach ($contacts as $c): ?>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
        <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
            <div>
                <p class="font-semibold text-gray-800">
                    <?= e($c['requester_name'] ?? 'Anónimo') ?>
                    <span class="text-gray-400 font-normal text-sm"> → </span>
                    <a href="/p/<?= e($c['provider_slug']) ?>" target="_blank" class="text-brand-600 hover:underline"><?= e($c['provider_name']) ?></a>
                </p>
                <p class="text-xs text-gray-400">
                    <?= timeAgo($c['created_at']) ?>
                    <?php if ($c['requester_email']): ?> · <?= e($c['requester_email']) ?><?php endif; ?>
                    <?php if ($c['requester_phone']): ?> · <?= e($c['requester_phone']) ?><?php endif; ?>
                </p>
            </div>
            <form method="POST" class="flex items-center gap-2">
                <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="action" value="set_status">
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                <select name="status" onchange="this.form.submit()" class="form-input text-xs py-1.5">
                    <?php foreach (['pending' => 'Pendiente', 'read' => 'Leída', 'replied' => 'Respondida', 'closed' => 'Cerrada'] as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $c['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php if ($c['message']): ?>
        <p class="text-sm text-gray-600 bg-gray-50 rounded-xl p-3"><?= nl2br(e($c['message'])) ?></p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if (empty($contacts)): ?>
    <div class="text-center py-12 text-gray-400 bg-white rounded-2xl border border-gray-100">No hay solicitudes con este filtro.</div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
