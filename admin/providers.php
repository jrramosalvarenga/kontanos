<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    $qs = http_build_query(array_filter([
        'category' => trim($_POST['_category'] ?? $_GET['category'] ?? ''),
        'city'     => trim($_POST['_city']     ?? $_GET['city']     ?? ''),
        'q'        => trim($_POST['_q']        ?? $_GET['q']        ?? ''),
    ]));

    $redirect = static function(string $msg) use ($qs): never {
        $sep = $qs ? '&' : '?';
        header('Location: /admin/providers.php' . ($qs ? "?$qs" : '') . "{$sep}msg={$msg}");
        exit;
    };

    if ($action === 'toggle_featured') {
        DB::query("UPDATE provider_profiles SET is_featured = NOT is_featured WHERE id = $1", [$id]);
        $redirect('featured');
    } elseif ($action === 'toggle_verified') {
        DB::query("UPDATE provider_profiles SET is_verified = NOT is_verified WHERE id = $1", [$id]);
        $redirect('verified');
    } elseif ($action === 'set_priority') {
        DB::query("UPDATE provider_profiles SET admin_priority = $1 WHERE id = $2", [(int)$_POST['admin_priority'], $id]);
        $redirect('priority');
    } elseif ($action === 'update_avatar') {
        $url = trim($_POST['avatar_url'] ?? '');
        if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
            $url = '';
        }
        DB::query("UPDATE provider_profiles SET avatar_url = $1, updated_at = NOW() WHERE id = $2", [$url ?: null, $id]);
        $redirect('avatar');
    }
}

$msgs = [
    'featured' => 'Estado "Destacado" actualizado.',
    'verified' => 'Estado "Verificado" actualizado.',
    'priority' => 'Prioridad de búsqueda actualizada.',
    'avatar'   => 'Foto de perfil actualizada correctamente.',
];
$message = $msgs[$_GET['msg'] ?? ''] ?? '';

$category = trim($_GET['category'] ?? '');
$city     = trim($_GET['city'] ?? '');
$search   = trim($_GET['q'] ?? '');

$conditions = [];
$params = [];
$i = 1;
if ($category) {
    $conditions[] = "c.slug = $" . $i++;
    $params[] = $category;
}
if ($city) {
    $conditions[] = "l.slug = $" . $i++;
    $params[] = $city;
}
if ($search) {
    $conditions[] = "(pp.full_name ILIKE $" . $i . " OR u.email ILIKE $" . $i . ")";
    $params[] = '%' . $search . '%';
    $i++;
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$providers = DB::fetchAll("
    SELECT pp.id, pp.slug, pp.full_name, pp.avatar_url, pp.business_name,
           pp.is_featured, pp.is_verified, pp.admin_priority,
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
<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-col sm:flex-row gap-3 flex-wrap">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Buscar por nombre o email…" class="form-input sm:w-56">
    <select name="category" class="form-input sm:w-52">
        <option value="">Todas las categorías</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= e($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="city" class="form-input sm:w-52">
        <option value="">Todas las ciudades</option>
        <?php foreach ($locations as $loc): ?>
        <option value="<?= e($loc['slug']) ?>" <?= $city === $loc['slug'] ? 'selected' : '' ?>><?= e($loc['city']) ?>, <?= e($loc['country']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-primary">Filtrar</button>
    <?php if ($category || $city || $search): ?>
    <a href="/admin/providers.php" class="btn-outline">Limpiar</a>
    <?php endif; ?>
    <a href="/admin/provider-new.php" class="btn-primary sm:ml-auto text-center">+ Agregar profesional</a>
</form>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ modal: null, tempUrl: '', previewOk: true }">

    <!-- Avatar edit modal -->
    <div x-show="modal !== null" x-cloak x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
         @keydown.escape.window="modal = null"
         @click.self="modal = null">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900">Editar foto de perfil</h3>
                <button @click="modal = null" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <!-- Preview -->
            <div class="flex justify-center mb-4">
                <div class="w-28 h-28 rounded-2xl bg-gray-100 border-2 border-gray-200 overflow-hidden flex items-center justify-center">
                    <template x-if="tempUrl && previewOk">
                        <img :src="tempUrl" class="w-full h-full object-cover" @error="previewOk = false">
                    </template>
                    <template x-if="!tempUrl || !previewOk">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </template>
                </div>
            </div>
            <form method="POST" action="/admin/providers.php">
                <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="action" value="update_avatar">
                <input type="hidden" name="id" :value="modal">
                <input type="hidden" name="_category" value="<?= e($category) ?>">
                <input type="hidden" name="_city" value="<?= e($city) ?>">
                <input type="hidden" name="_q" value="<?= e($search) ?>">
                <div class="mb-4">
                    <label class="form-label">URL de la imagen</label>
                    <input type="url" name="avatar_url" class="form-input" x-model="tempUrl"
                           @input="previewOk = true"
                           placeholder="https://ejemplo.com/foto.jpg">
                    <p class="text-xs text-gray-400 mt-1.5">
                        Pega una URL directa de imagen (imgur.com, postimages.org, etc.).<br>
                        Déjalo en blanco para eliminar la foto actual.
                    </p>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1 py-2.5 text-sm">Guardar foto</button>
                    <button type="button" @click="modal = null"
                            class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Foto</th>
                <th class="text-left px-4 py-3">Profesional</th>
                <th class="text-left px-4 py-3">Categoría</th>
                <th class="text-left px-4 py-3">Ciudad</th>
                <th class="text-left px-4 py-3">Rango</th>
                <th class="text-center px-4 py-3">Vistas</th>
                <th class="text-center px-4 py-3">Destacado</th>
                <th class="text-center px-4 py-3">Verificado</th>
                <th class="text-center px-4 py-3">Prioridad</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($providers as $p): ?>
            <tr class="hover:bg-gray-50">
                <!-- Avatar cell -->
                <td class="px-4 py-3">
                    <button type="button"
                            @click="modal = <?= (int)$p['id'] ?>; tempUrl = '<?= addslashes(e($p['avatar_url'] ?? '')) ?>'; previewOk = true"
                            class="relative group block">
                        <img src="<?= e(getAvatar($p['avatar_url'], $p['full_name'], '48')) ?>"
                             alt="" class="w-10 h-10 rounded-xl object-cover border border-gray-200">
                        <div class="absolute inset-0 rounded-xl bg-black/0 group-hover:bg-black/50 transition-all flex items-center justify-center">
                            <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </button>
                </td>
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-800">
                        <?= e(!empty($p['business_name']) ? $p['business_name'] : $p['full_name']) ?>
                    </div>
                    <?php if (!empty($p['business_name'])): ?>
                    <div class="text-xs text-brand-600">🏢 <?= e($p['full_name']) ?></div>
                    <?php endif; ?>
                    <div class="text-xs text-gray-400"><?= e($p['email']) ?></div>
                </td>
                <td class="px-4 py-3 text-gray-600"><?= e($p['category_name'] ?? '—') ?></td>
                <td class="px-4 py-3 text-gray-600"><?= e($p['city'] ? $p['city'] . ', ' . $p['country'] : '—') ?></td>
                <td class="px-4 py-3 text-gray-600"><?= e(($p['badge_icon'] ?? '') . ' ' . ($p['rank_name'] ?? '')) ?></td>
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
            <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400">No hay profesionales con estos filtros.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
