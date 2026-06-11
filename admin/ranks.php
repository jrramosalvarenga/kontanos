<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            DB::query("
                INSERT INTO ranks (name, slug, min_points, badge_icon, badge_color, search_boost, sort_order)
                VALUES ($1, $2, $3, $4, $5, $6, $7)
            ", [
                $name,
                slugify($name),
                (int)($_POST['min_points'] ?? 0),
                trim($_POST['badge_icon'] ?? '') ?: '⭐',
                trim($_POST['badge_color'] ?? '') ?: '#15803d',
                (int)($_POST['search_boost'] ?? 0),
                (int)($_POST['sort_order'] ?? 0),
            ]);
            $message = 'Rango creado.';
        }
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        DB::query("
            UPDATE ranks SET name=$1, min_points=$2, badge_icon=$3, badge_color=$4, search_boost=$5, sort_order=$6
            WHERE id=$7
        ", [
            trim($_POST['name']),
            (int)$_POST['min_points'],
            trim($_POST['badge_icon']) ?: '⭐',
            trim($_POST['badge_color']) ?: '#15803d',
            (int)$_POST['search_boost'],
            (int)$_POST['sort_order'],
            $id,
        ]);
        $message = 'Rango actualizado.';
    }
}

$ranks = getRanks();

$pageTitle = 'Rangos de Referidos';
$activeNav = 'ranks';
require __DIR__ . '/includes/layout_header.php';
?>

<?php if ($message): ?>
<div class="bg-brand-50 border border-brand-200 text-brand-700 rounded-xl px-4 py-3 mb-6 text-sm"><?= e($message) ?></div>
<?php endif; ?>

<p class="text-sm text-gray-500 mb-6 max-w-2xl">
    Los rangos definen los privilegios que obtienen los usuarios según los puntos acumulados por su red de referidos.
    "Boost de búsqueda" se suma al ordenamiento de resultados de profesionales con ese rango.
</p>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- New rank -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 h-fit">
        <h2 class="font-bold text-gray-900 mb-4">Nuevo rango</h2>
        <form method="POST" class="space-y-3">
            <input type="hidden" name="_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Puntos mínimos</label>
                <input type="number" name="min_points" class="form-input" value="0" min="0">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Icono (emoji)</label>
                    <input type="text" name="badge_icon" class="form-input" value="⭐" maxlength="10">
                </div>
                <div>
                    <label class="form-label">Color</label>
                    <input type="color" name="badge_color" class="form-input h-11" value="#15803d">
                </div>
            </div>
            <div>
                <label class="form-label">Boost de búsqueda</label>
                <input type="number" name="search_boost" class="form-input" value="0">
            </div>
            <div>
                <label class="form-label">Orden</label>
                <input type="number" name="sort_order" class="form-input" value="0">
            </div>
            <button type="submit" class="btn-primary w-full">Crear rango</button>
        </form>
    </div>

    <!-- Ranks list -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 mb-4">Rangos existentes</h2>
        <div class="space-y-3">
            <?php foreach ($ranks as $rank): ?>
            <details class="border border-gray-100 rounded-xl p-3" <?= $rank['sort_order'] <= 1 ? 'open' : '' ?>>
                <summary class="flex items-center gap-3 cursor-pointer">
                    <?= renderRankBadge($rank) ?: '<span class="badge badge-gray text-xs">' . e($rank['badge_icon']) . ' ' . e($rank['name']) . '</span>' ?>
                    <span class="text-xs text-gray-400 ml-auto"><?= (int)$rank['min_points'] ?> pts mínimos · boost +<?= (int)$rank['search_boost'] ?></span>
                </summary>
                <form method="POST" class="mt-3 pt-3 border-t border-gray-100 grid sm:grid-cols-2 gap-3">
                    <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int)$rank['id'] ?>">
                    <div>
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-input" value="<?= e($rank['name']) ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Puntos mínimos</label>
                        <input type="number" name="min_points" class="form-input" value="<?= (int)$rank['min_points'] ?>" min="0">
                    </div>
                    <div>
                        <label class="form-label">Icono (emoji)</label>
                        <input type="text" name="badge_icon" class="form-input" value="<?= e($rank['badge_icon']) ?>" maxlength="10">
                    </div>
                    <div>
                        <label class="form-label">Color</label>
                        <input type="color" name="badge_color" class="form-input h-11" value="<?= e($rank['badge_color']) ?>">
                    </div>
                    <div>
                        <label class="form-label">Boost de búsqueda</label>
                        <input type="number" name="search_boost" class="form-input" value="<?= (int)$rank['search_boost'] ?>">
                    </div>
                    <div>
                        <label class="form-label">Orden</label>
                        <input type="number" name="sort_order" class="form-input" value="<?= (int)$rank['sort_order'] ?>">
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-primary text-sm">Guardar cambios</button>
                    </div>
                </form>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
