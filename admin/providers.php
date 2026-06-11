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

    if ($action === 'toggle_featured') {
        DB::query("UPDATE provider_profiles SET is_featured = NOT is_featured WHERE id = $1", [$id]);
        $message = 'Estado "Destacado" actualizado.';
    } elseif ($action === 'toggle_verified') {
        DB::query("UPDATE provider_profiles SET is_verified = NOT is_verified WHERE id = $1", [$id]);
        $message = 'Estado "Verificado" actualizado.';
    } elseif ($action === 'set_priority') {
        DB::query("UPDATE provider_profiles SET admin_priority = $1 WHERE id = $2", [(int)$_POST['admin_priority'], $id]);
        $message = 'Prioridad de búsqueda actualizada.';
    }
}

$category = trim($_GET['category'] ?? '');
$city     = trim($_GET['city'] ?? '');

$conditions = [];
$params = [];
$i = 1;
if ($category) {
    $conditions[] = "c.slug = $" . $i;
    $params[] = $category;
    $i++;
}
if ($city) {
    $conditions[] = "l.slug = $" . $i;
    $params[] = $city;
    $i++;
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$providers = DB::fetchAll("
    SELECT pp.id, pp.slug, pp.full_name, pp.is_featured, pp.is_verified, pp.admin_priority,
           pp.rating_avg, pp.rating_count, pp.profile_views,
           c.name as category_name, l.city, l.country,
           u.email, u.points, r.name as rank_name, r.badge_icon, r.badge_color
    FROM provider_profiles pp
    JOIN users u ON u.id = pp.user_id
    LEFT JOIN categories c ON c.id = pp.category_id
    LEFT JOIN locations l ON l.id = pp.location_id
    LEFT JOIN ranks r ON r.id = u.rank_id
    $where
    ORDER BY pp.admin_priority DESC, pp.full_name
", $params);

$categories = getCategories();
$locations  = getLocations();

$pageTitle = 'Profesionales';
$activeNav = 'providers';
require __DIR__ . '/includes/layout_header.php';
?>

<?php if (!empty($_GET['created'])): ?>
<div class="bg-brand-50 border border-brand-200 text-brand-700 rounded-xl px-4 py-3 mb-6 text-sm">Profesional creado correctamente.</div>
<?php endif; ?>

<?php if ($message): ?>
<div class="bg-brand-50 border border-brand-200 text-brand-700 rounded-xl px-4 py-3 mb-6 text-sm"><?= e($message) ?></div>
<?php endif; ?>

<!-- Filters -->
<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-col sm:flex-row gap-3">
    <select name="category" class="form-input sm:w-64">
        <option value="">Todas las categorías</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= e($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="city" class="form-input sm:w-64">
        <option value="">Todas las ciudades</option>
        <?php foreach ($locations as $loc): ?>
        <option value="<?= e($loc['slug']) ?>" <?= $city === $loc['slug'] ? 'selected' : '' ?>><?= e($loc['city']) ?>, <?= e($loc['country']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-primary">Filtrar</button>
    <?php if ($category || $city): ?>
    <a href="/admin/providers.php" class="btn-outline">Limpiar</a>
    <?php endif; ?>
    <a href="/admin/provider-new.php" class="btn-primary sm:ml-auto text-center">+ Agregar profesional</a>
</form>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Profesional</th>
                <th class="text-left px-4 py-3">Categoría</th>
                <th class="text-left px-4 py-3">Ciudad</th>
                <th class="text-left px-4 py-3">Rango</th>
                <th class="text-center px-4 py-3">Vistas</th>
                <th class="text-center px-4 py-3">Destacado</th>
                <th class="text-center px-4 py-3">Verificado</th>
                <th class="text-center px-4 py-3">Prioridad</th>
                <th class="text-right px-4 py-3">Perfil</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($providers as $p): ?>
            <tr>
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-800"><?= e($p['full_name']) ?></div>
                    <div class="text-xs text-gray-400"><?= e($p['email']) ?></div>
                </td>
                <td class="px-4 py-3 text-gray-600"><?= e($p['category_name'] ?? '—') ?></td>
                <td class="px-4 py-3 text-gray-600"><?= e($p['city'] ? $p['city'] . ', ' . $p['country'] : '—') ?></td>
                <td class="px-4 py-3 text-gray-600"><?= e($p['badge_icon'] . ' ' . $p['rank_name']) ?></td>
                <td class="px-4 py-3 text-center text-gray-600"><?= number_format($p['profile_views']) ?></td>
                <td class="px-4 py-3 text-center">
                    <form method="POST">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="toggle_featured">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="badge <?= $p['is_featured'] ? 'badge-amber' : 'badge-gray' ?> text-xs">
                            <?= $p['is_featured'] ? '⭐ Sí' : 'No' ?>
                        </button>
                    </form>
                </td>
                <td class="px-4 py-3 text-center">
                    <form method="POST">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="toggle_verified">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="badge <?= $p['is_verified'] ? 'badge-blue' : 'badge-gray' ?> text-xs">
                            <?= $p['is_verified'] ? '✓ Sí' : 'No' ?>
                        </button>
                    </form>
                </td>
                <td class="px-4 py-3 text-center">
                    <form method="POST" class="flex items-center justify-center gap-1">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="set_priority">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <input type="number" name="admin_priority" value="<?= (int)$p['admin_priority'] ?>"
                               class="form-input w-16 px-2 py-1 text-center" style="padding:0.35rem">
                        <button type="submit" class="btn-outline text-xs px-2 py-1">OK</button>
                    </form>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="/p/<?= e($p['slug']) ?>" target="_blank" class="text-brand-600 hover:underline text-xs font-semibold">Ver →</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($providers)): ?>
            <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No hay profesionales con estos filtros.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
