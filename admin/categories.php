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
            $slug = slugify($name);
            DB::query("
                INSERT INTO categories (name, slug, icon, color, description, sort_order, is_active)
                VALUES ($1, $2, $3, $4, $5, $6, TRUE)
            ", [
                $name, $slug,
                trim($_POST['icon'] ?? '') ?: 'briefcase',
                trim($_POST['color'] ?? '') ?: '#15803d',
                trim($_POST['description'] ?? '') ?: null,
                (int)($_POST['sort_order'] ?? 0),
            ]);
            $message = 'Categoría creada.';
        }
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        DB::query("
            UPDATE categories SET name=$1, icon=$2, color=$3, description=$4, sort_order=$5
            WHERE id=$6
        ", [
            trim($_POST['name']),
            trim($_POST['icon']) ?: 'briefcase',
            trim($_POST['color']) ?: '#15803d',
            trim($_POST['description']) ?: null,
            (int)$_POST['sort_order'],
            $id,
        ]);
        $message = 'Categoría actualizada.';
    } elseif ($action === 'toggle') {
        $id = (int)$_POST['id'];
        DB::query("UPDATE categories SET is_active = NOT is_active WHERE id = $1", [$id]);
        $message = 'Estado actualizado.';
    }
}

$categories = DB::fetchAll("SELECT * FROM categories ORDER BY sort_order, name");

$pageTitle = 'Categorías';
$activeNav = 'categories';
require __DIR__ . '/includes/layout_header.php';
?>

<?php if ($message): ?>
<div class="bg-brand-50 border border-brand-200 text-brand-700 rounded-xl px-4 py-3 mb-6 text-sm"><?= e($message) ?></div>
<?php endif; ?>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- New category form -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 h-fit">
        <h2 class="font-bold text-gray-900 mb-4">Nueva categoría</h2>
        <form method="POST" class="space-y-3">
            <input type="hidden" name="_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Icono (heroicon slug)</label>
                <input type="text" name="icon" class="form-input" placeholder="briefcase">
            </div>
            <div>
                <label class="form-label">Color</label>
                <input type="color" name="color" class="form-input h-11" value="#15803d">
            </div>
            <div>
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-input" rows="2"></textarea>
            </div>
            <div>
                <label class="form-label">Orden</label>
                <input type="number" name="sort_order" class="form-input" value="0">
            </div>
            <button type="submit" class="btn-primary w-full">Crear categoría</button>
        </form>
    </div>

    <!-- Categories list -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 mb-4">Categorías (<?= count($categories) ?>)</h2>
        <div class="space-y-3">
            <?php foreach ($categories as $cat): ?>
            <details class="border border-gray-100 rounded-xl p-3">
                <summary class="flex items-center gap-3 cursor-pointer">
                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color:<?= e($cat['color']) ?>"></span>
                    <span class="font-semibold text-gray-800 text-sm flex-1"><?= e($cat['name']) ?></span>
                    <span class="text-xs text-gray-400">/<?= e($cat['slug']) ?></span>
                    <span class="badge <?= $cat['is_active'] ? 'badge-green' : 'badge-gray' ?> text-xs">
                        <?= $cat['is_active'] ? 'Activa' : 'Inactiva' ?>
                    </span>
                </summary>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <form method="POST" class="space-y-3">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Nombre</label>
                                <input type="text" name="name" class="form-input" value="<?= e($cat['name']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Icono</label>
                                <input type="text" name="icon" class="form-input" value="<?= e($cat['icon']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Color</label>
                                <input type="color" name="color" class="form-input h-11" value="<?= e($cat['color']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Orden</label>
                                <input type="number" name="sort_order" class="form-input" value="<?= (int)$cat['sort_order'] ?>">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-input" rows="2"><?= e($cat['description'] ?? '') ?></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-primary text-sm">Guardar cambios</button>
                        </div>
                    </form>
                    <form method="POST" class="mt-2">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                        <button type="submit" class="btn-outline text-sm">
                            <?= $cat['is_active'] ? 'Desactivar' : 'Activar' ?>
                        </button>
                    </form>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
