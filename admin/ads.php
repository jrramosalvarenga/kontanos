<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$message = '';
$positions = ['home_banner' => 'Banner home', 'search_top' => 'Top de búsqueda', 'sidebar' => 'Barra lateral'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $title    = trim($_POST['title'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');
        $linkUrl  = trim($_POST['link_url'] ?? '') ?: null;
        $position = in_array($_POST['position'] ?? '', array_keys($positions)) ? $_POST['position'] : 'home_banner';
        $startsAt = trim($_POST['starts_at'] ?? '') ?: null;
        $endsAt   = trim($_POST['ends_at'] ?? '') ?: null;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']);

        if ($title && $imageUrl) {
            if ($action === 'create') {
                DB::query("
                    INSERT INTO ads (title, image_url, link_url, position, is_active, starts_at, ends_at, sort_order)
                    VALUES ($1,$2,$3,$4,$5,$6,$7,$8)
                ", [$title, $imageUrl, $linkUrl, $position, $isActive, $startsAt, $endsAt, $sortOrder]);
                $message = 'Anuncio creado.';
            } else {
                $id = (int)$_POST['id'];
                DB::query("
                    UPDATE ads SET title=$1, image_url=$2, link_url=$3, position=$4, is_active=$5, starts_at=$6, ends_at=$7, sort_order=$8
                    WHERE id=$9
                ", [$title, $imageUrl, $linkUrl, $position, $isActive, $startsAt, $endsAt, $sortOrder, $id]);
                $message = 'Anuncio actualizado.';
            }
        }
    } elseif ($action === 'toggle') {
        DB::query("UPDATE ads SET is_active = NOT is_active WHERE id = $1", [(int)$_POST['id']]);
        $message = 'Estado actualizado.';
    } elseif ($action === 'delete') {
        DB::query("DELETE FROM ads WHERE id = $1", [(int)$_POST['id']]);
        $message = 'Anuncio eliminado.';
    }
}

$ads = DB::fetchAll("SELECT * FROM ads ORDER BY position, sort_order, id DESC");

$pageTitle = 'Publicidad';
$activeNav = 'ads';
require __DIR__ . '/includes/layout_header.php';
?>

<?php if ($message): ?>
<div class="bg-brand-50 border border-brand-200 text-brand-700 rounded-xl px-4 py-3 mb-6 text-sm"><?= e($message) ?></div>
<?php endif; ?>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- New ad -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 h-fit">
        <h2 class="font-bold text-gray-900 mb-4">Nuevo anuncio</h2>
        <form method="POST" class="space-y-3">
            <input type="hidden" name="_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="form-label">Título *</label>
                <input type="text" name="title" class="form-input" required>
            </div>
            <div>
                <label class="form-label">URL de imagen *</label>
                <input type="url" name="image_url" class="form-input" required placeholder="https://...">
            </div>
            <div>
                <label class="form-label">URL de destino (link)</label>
                <input type="url" name="link_url" class="form-input" placeholder="https://...">
            </div>
            <div>
                <label class="form-label">Posición</label>
                <select name="position" class="form-input">
                    <?php foreach ($positions as $key => $label): ?>
                    <option value="<?= $key ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Inicia</label>
                    <input type="date" name="starts_at" class="form-input">
                </div>
                <div>
                    <label class="form-label">Termina</label>
                    <input type="date" name="ends_at" class="form-input">
                </div>
            </div>
            <div>
                <label class="form-label">Orden</label>
                <input type="number" name="sort_order" class="form-input" value="0">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="is_active" checked class="accent-brand-600"> Activo
            </label>
            <button type="submit" class="btn-primary w-full">Crear anuncio</button>
        </form>
    </div>

    <!-- Ads list -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 mb-4">Anuncios (<?= count($ads) ?>)</h2>
        <div class="space-y-3">
            <?php foreach ($ads as $ad): ?>
            <details class="border border-gray-100 rounded-xl p-3">
                <summary class="flex items-center gap-3 cursor-pointer">
                    <img src="<?= e($ad['image_url']) ?>" alt="" class="w-16 h-10 object-cover rounded-lg flex-shrink-0 bg-gray-100">
                    <span class="font-semibold text-gray-800 text-sm flex-1"><?= e($ad['title']) ?></span>
                    <span class="text-xs text-gray-400"><?= e($positions[$ad['position']] ?? $ad['position']) ?></span>
                    <span class="badge <?= $ad['is_active'] ? 'badge-green' : 'badge-gray' ?> text-xs"><?= $ad['is_active'] ? 'Activo' : 'Inactivo' ?></span>
                    <span class="text-xs text-gray-400"><?= (int)$ad['clicks'] ?> clics / <?= (int)$ad['impressions'] ?> vistas</span>
                </summary>
                <form method="POST" class="mt-3 pt-3 border-t border-gray-100 grid sm:grid-cols-2 gap-3">
                    <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int)$ad['id'] ?>">
                    <div>
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-input" value="<?= e($ad['title']) ?>" required>
                    </div>
                    <div>
                        <label class="form-label">URL de imagen</label>
                        <input type="url" name="image_url" class="form-input" value="<?= e($ad['image_url']) ?>" required>
                    </div>
                    <div>
                        <label class="form-label">URL de destino</label>
                        <input type="url" name="link_url" class="form-input" value="<?= e($ad['link_url'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="form-label">Posición</label>
                        <select name="position" class="form-input">
                            <?php foreach ($positions as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $ad['position'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Inicia</label>
                        <input type="date" name="starts_at" class="form-input" value="<?= $ad['starts_at'] ? date('Y-m-d', strtotime($ad['starts_at'])) : '' ?>">
                    </div>
                    <div>
                        <label class="form-label">Termina</label>
                        <input type="date" name="ends_at" class="form-input" value="<?= $ad['ends_at'] ? date('Y-m-d', strtotime($ad['ends_at'])) : '' ?>">
                    </div>
                    <div>
                        <label class="form-label">Orden</label>
                        <input type="number" name="sort_order" class="form-input" value="<?= (int)$ad['sort_order'] ?>">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-600 mt-6">
                        <input type="checkbox" name="is_active" <?= $ad['is_active'] ? 'checked' : '' ?> class="accent-brand-600"> Activo
                    </label>
                    <div class="sm:col-span-2 flex gap-2">
                        <button type="submit" class="btn-primary text-sm">Guardar cambios</button>
                    </div>
                </form>
                <form method="POST" class="mt-2" onsubmit="return confirm('¿Eliminar este anuncio?');">
                    <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$ad['id'] ?>">
                    <button type="submit" class="text-red-600 hover:underline text-xs font-semibold">Eliminar</button>
                </form>
            </details>
            <?php endforeach; ?>
            <?php if (empty($ads)): ?>
            <p class="text-sm text-gray-400 text-center py-8">No hay anuncios creados.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
