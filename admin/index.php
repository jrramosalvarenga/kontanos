<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$userCounts = DB::fetchAll("SELECT role, COUNT(*) as c FROM users GROUP BY role");
$counts = ['provider' => 0, 'client' => 0, 'admin' => 0];
foreach ($userCounts as $row) {
    $counts[$row['role']] = (int)$row['c'];
}

$totalProviders = (int)DB::fetch("SELECT COUNT(*) as c FROM provider_profiles")['c'];
$totalContacts  = (int)DB::fetch("SELECT COUNT(*) as c FROM contact_requests")['c'];
$pendingContacts = (int)DB::fetch("SELECT COUNT(*) as c FROM contact_requests WHERE status = 'pending'")['c'];
$totalViews     = (int)DB::fetch("SELECT COALESCE(SUM(profile_views),0) as c FROM provider_profiles")['c'];

$topCategories = DB::fetchAll("
    SELECT c.name, c.color, c.icon, COUNT(pp.id) as total
    FROM categories c
    LEFT JOIN provider_profiles pp ON pp.category_id = c.id
    GROUP BY c.id, c.name, c.color, c.icon
    ORDER BY total DESC, c.sort_order
    LIMIT 8
");

$signups30d = DB::fetchAll("
    SELECT DATE(created_at) as day, COUNT(*) as c
    FROM users
    WHERE created_at >= NOW() - INTERVAL '30 days'
    GROUP BY DATE(created_at)
    ORDER BY day DESC
    LIMIT 10
");

$topReferrers = DB::fetchAll("
    SELECT u.id, u.email, u.points, r.name as rank_name, r.badge_icon, r.badge_color,
           COALESCE(pp.full_name, split_part(u.email,'@',1)) as display_name,
           (SELECT COUNT(*) FROM users u2 WHERE u2.referred_by = u.id) as direct_count
    FROM users u
    JOIN ranks r ON r.id = u.rank_id
    LEFT JOIN provider_profiles pp ON pp.user_id = u.id
    WHERE u.points > 0
    ORDER BY u.points DESC
    LIMIT 10
");

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/layout_header.php';
?>

<!-- Stat cards -->
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php
    $stats = [
        ['label' => 'Profesionales', 'value' => number_format($totalProviders), 'color' => 'green'],
        ['label' => 'Clientes', 'value' => number_format($counts['client']), 'color' => 'blue'],
        ['label' => 'Vistas de perfil (total)', 'value' => number_format($totalViews), 'color' => 'amber'],
        ['label' => 'Solicitudes de contacto', 'value' => number_format($totalContacts) . ($pendingContacts ? " ({$pendingContacts} nuevas)" : ''), 'color' => 'purple'],
    ];
    $colorMap = ['green' => 'bg-brand-100 text-brand-700', 'blue' => 'bg-blue-100 text-blue-700', 'amber' => 'bg-amber-100 text-amber-700', 'purple' => 'bg-purple-100 text-purple-700'];
    foreach ($stats as $s):
    ?>
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="text-2xl font-extrabold text-gray-900"><?= $s['value'] ?></div>
        <div class="text-xs text-gray-500 mt-1"><?= e($s['label']) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Top categories -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 mb-4">Categorías con más profesionales</h2>
        <div class="space-y-3">
            <?php foreach ($topCategories as $cat): ?>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" style="background-color:<?= e($cat['color']) ?>"></span>
                    <?= e($cat['name']) ?>
                </span>
                <span class="text-sm font-bold text-gray-900"><?= (int)$cat['total'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Signups last 30 days -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 mb-4">Altas recientes (últimos 30 días)</h2>
        <?php if (empty($signups30d)): ?>
        <p class="text-sm text-gray-400">Sin registros en este periodo.</p>
        <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($signups30d as $row): ?>
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600"><?= date('d/m/Y', strtotime($row['day'])) ?></span>
                <span class="font-bold text-gray-900"><?= (int)$row['c'] ?> registro<?= $row['c'] != 1 ? 's' : '' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top referrers -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 mb-4">Top enroladores</h2>
        <?php if (empty($topReferrers)): ?>
        <p class="text-sm text-gray-400">Aún no hay referidos registrados.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($topReferrers as $i => $u): ?>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 flex items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-500 flex-shrink-0"><?= $i + 1 ?></span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate"><?= e($u['display_name']) ?></p>
                    <p class="text-xs text-gray-400"><?= e($u['badge_icon']) ?> <?= e($u['rank_name']) ?> · <?= (int)$u['direct_count'] ?> referidos directos</p>
                </div>
                <span class="text-sm font-bold text-brand-700 flex-shrink-0"><?= (int)$u['points'] ?> pts</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
