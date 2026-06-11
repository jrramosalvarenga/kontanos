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

    if ($action === 'toggle_active') {
        DB::query("UPDATE users SET is_active = NOT is_active WHERE id = $1", [$id]);
        $message = 'Estado del usuario actualizado.';
    }
}

$viewId = (int)($_GET['view'] ?? 0);

$users = DB::fetchAll("
    SELECT u.id, u.email, u.role, u.is_active, u.points, u.created_at,
           r.name as rank_name, r.badge_icon, r.badge_color,
           COALESCE(pp.full_name, split_part(u.email,'@',1)) as display_name,
           ref.email as referred_by_email,
           (SELECT COUNT(*) FROM users u2 WHERE u2.referred_by = u.id) as direct_count
    FROM users u
    JOIN ranks r ON r.id = u.rank_id
    LEFT JOIN provider_profiles pp ON pp.user_id = u.id
    LEFT JOIN users ref ON ref.id = u.referred_by
    ORDER BY u.points DESC, u.created_at DESC
");

$networkUser = null;
$directReferrals = [];
$indirectReferrals = [];
if ($viewId) {
    $networkUser = DB::fetch("
        SELECT u.*, COALESCE(pp.full_name, split_part(u.email,'@',1)) as display_name
        FROM users u LEFT JOIN provider_profiles pp ON pp.user_id = u.id
        WHERE u.id = $1
    ", [$viewId]);
    if ($networkUser) {
        $directReferrals = DB::fetchAll("
            SELECT u.id, u.email, u.role, u.points, u.created_at,
                   COALESCE(pp.full_name, split_part(u.email,'@',1)) as display_name
            FROM users u LEFT JOIN provider_profiles pp ON pp.user_id = u.id
            WHERE u.referred_by = $1 ORDER BY u.created_at DESC
        ", [$viewId]);
        $indirectReferrals = DB::fetchAll("
            SELECT u.id, u.email, u.role, u.points, u.created_at,
                   COALESCE(pp.full_name, split_part(u.email,'@',1)) as display_name,
                   ref.email as referred_by_email
            FROM users u
            LEFT JOIN provider_profiles pp ON pp.user_id = u.id
            LEFT JOIN users ref ON ref.id = u.referred_by
            WHERE u.referred_by IN (SELECT id FROM users WHERE referred_by = $1)
            ORDER BY u.created_at DESC
        ", [$viewId]);
    }
}

$pageTitle = 'Usuarios y Red de Referidos';
$activeNav = 'users';
require __DIR__ . '/includes/layout_header.php';
?>

<?php if ($message): ?>
<div class="bg-brand-50 border border-brand-200 text-brand-700 rounded-xl px-4 py-3 mb-6 text-sm"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($networkUser): ?>
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-gray-900">Red de <?= e($networkUser['display_name']) ?></h2>
        <a href="/admin/users.php" class="text-sm text-brand-600 hover:underline">← Volver al listado</a>
    </div>
    <div class="grid sm:grid-cols-2 gap-6">
        <div>
            <h3 class="text-sm font-bold text-gray-700 mb-2">Referidos directos (nivel 1) — <?= count($directReferrals) ?></h3>
            <?php if (empty($directReferrals)): ?>
            <p class="text-sm text-gray-400">Sin referidos directos.</p>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($directReferrals as $r): ?>
                <div class="flex items-center justify-between bg-gray-50 rounded-xl px-3 py-2 text-sm">
                    <span><?= e($r['display_name']) ?> <span class="text-xs text-gray-400">(<?= $r['role'] === 'provider' ? 'profesional' : 'cliente' ?>)</span></span>
                    <span class="text-xs text-gray-400"><?= timeAgo($r['created_at']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-700 mb-2">Referidos de 2do nivel — <?= count($indirectReferrals) ?></h3>
            <?php if (empty($indirectReferrals)): ?>
            <p class="text-sm text-gray-400">Sin referidos de 2do nivel.</p>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($indirectReferrals as $r): ?>
                <div class="flex items-center justify-between bg-gray-50 rounded-xl px-3 py-2 text-sm">
                    <span><?= e($r['display_name']) ?> <span class="text-xs text-gray-400">(<?= $r['role'] === 'provider' ? 'profesional' : 'cliente' ?>)</span></span>
                    <span class="text-xs text-gray-400">vía <?= e($r['referred_by_email']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Usuario</th>
                <th class="text-left px-4 py-3">Rol</th>
                <th class="text-left px-4 py-3">Referido por</th>
                <th class="text-center px-4 py-3">Puntos</th>
                <th class="text-left px-4 py-3">Rango</th>
                <th class="text-center px-4 py-3">Red</th>
                <th class="text-center px-4 py-3">Activo</th>
                <th class="text-center px-4 py-3">Registrado</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($users as $u): ?>
            <tr>
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-800"><?= e($u['display_name']) ?></div>
                    <div class="text-xs text-gray-400"><?= e($u['email']) ?></div>
                </td>
                <td class="px-4 py-3">
                    <span class="badge <?= $u['role'] === 'provider' ? 'badge-green' : ($u['role'] === 'admin' ? 'badge-amber' : 'badge-blue') ?> text-xs">
                        <?= e(['provider' => 'Profesional', 'client' => 'Cliente', 'admin' => 'Admin'][$u['role']] ?? $u['role']) ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs"><?= e($u['referred_by_email'] ?? '—') ?></td>
                <td class="px-4 py-3 text-center font-bold text-gray-900"><?= (int)$u['points'] ?></td>
                <td class="px-4 py-3 text-gray-600 text-xs"><?= e($u['badge_icon'] . ' ' . $u['rank_name']) ?></td>
                <td class="px-4 py-3 text-center">
                    <?php if ((int)$u['direct_count'] > 0): ?>
                    <a href="/admin/users.php?view=<?= (int)$u['id'] ?>" class="text-brand-600 hover:underline text-xs font-semibold">
                        Ver (<?= (int)$u['direct_count'] ?>)
                    </a>
                    <?php else: ?>
                    <span class="text-xs text-gray-300">—</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-center">
                    <form method="POST">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="toggle_active">
                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                        <button type="submit" class="badge <?= $u['is_active'] ? 'badge-green' : 'badge-gray' ?> text-xs">
                            <?= $u['is_active'] ? 'Sí' : 'No' ?>
                        </button>
                    </form>
                </td>
                <td class="px-4 py-3 text-center text-xs text-gray-400"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
