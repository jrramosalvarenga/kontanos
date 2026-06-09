<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireProvider();
$user    = currentUser();
$profile = DB::fetch("SELECT * FROM provider_profiles WHERE user_id = $1", [$user['id']]);

if (!$profile) {
    header('Location: /edit-profile.php?info=' . urlencode('Completa tu perfil primero.'));
    exit;
}

$errors = [];
$saved  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title      = trim($_POST['title'] ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $priceFrom  = $_POST['price_from'] !== '' ? (float)$_POST['price_from'] : null;
    $priceTo    = $_POST['price_to'] !== '' ? (float)$_POST['price_to'] : null;
    $priceType  = in_array($_POST['price_type'], ['fixed','hourly','negotiable','free_quote']) ? $_POST['price_type'] : 'fixed';
    $currency   = $_POST['currency'] ?? 'USD';

    if (!$title) $errors[] = 'El título del servicio es requerido.';

    if (empty($errors)) {
        DB::query("
            INSERT INTO services (provider_id, title, description, price_from, price_to, price_type, currency)
            VALUES ($1, $2, $3, $4, $5, $6, $7)
        ", [$profile['id'], $title, $desc ?: null, $priceFrom, $priceTo, $priceType, $currency]);
        $saved = true;
    }
}

$pageTitle = 'Agregar Servicio | Kontactanos';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="/dashboard.php" class="text-sm text-brand-600 hover:underline flex items-center gap-1 mb-6">
        ← Volver al panel
    </a>
    <h1 class="text-2xl font-extrabold text-gray-900 mb-8">Nuevo Servicio</h1>

    <?php if ($saved): ?>
    <div class="bg-brand-50 border border-brand-200 text-brand-800 rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        ¡Servicio publicado exitosamente!
        <a href="/create-service.php" class="ml-2 underline font-semibold">Agregar otro</a>
        <a href="/p/<?= e($profile['slug']) ?>" target="_blank" class="ml-1 underline font-semibold">Ver en perfil →</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm"><?= e($errors[0]) ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
        <form method="POST" action="/create-service.php" class="space-y-5">
            <input type="hidden" name="_token" value="<?= csrfToken() ?>">

            <div>
                <label class="form-label">Nombre del servicio *</label>
                <input type="text" name="title" class="form-input" required
                       value="<?= e($_POST['title'] ?? '') ?>"
                       placeholder="Ej: Instalación eléctrica residencial">
            </div>

            <div>
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-input" rows="4" data-maxlength="500"
                          placeholder="Describe el servicio: qué incluye, qué problemas resuelve, detalles importantes..."><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="form-label">Tipo de precio</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <?php foreach (['fixed' => '💰 Precio fijo', 'hourly' => '⏱ Por hora', 'negotiable' => '🤝 A convenir', 'free_quote' => '📋 Cotización gratis'] as $val => $label): ?>
                    <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition-all
                                  <?= ($_POST['price_type'] ?? 'fixed') === $val ? 'border-brand-400 bg-brand-50' : 'border-gray-200 hover:border-brand-200' ?>">
                        <input type="radio" name="price_type" value="<?= $val ?>"
                               <?= ($_POST['price_type'] ?? 'fixed') === $val ? 'checked' : '' ?>
                               class="accent-brand-600">
                        <span class="text-xs font-medium text-gray-700"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Precio desde</label>
                    <input type="number" name="price_from" class="form-input" min="0" step="0.01"
                           value="<?= e($_POST['price_from'] ?? '') ?>" placeholder="0.00">
                </div>
                <div>
                    <label class="form-label">Precio hasta (opcional)</label>
                    <input type="number" name="price_to" class="form-input" min="0" step="0.01"
                           value="<?= e($_POST['price_to'] ?? '') ?>" placeholder="0.00">
                </div>
                <div>
                    <label class="form-label">Moneda</label>
                    <select name="currency" class="form-input">
                        <option value="USD" selected>USD $</option>
                        <option value="VES">VES Bs.</option>
                        <option value="COP">COP $</option>
                        <option value="EUR">EUR €</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-4 pt-2">
                <button type="submit" class="btn-primary flex-1 py-3.5">Publicar servicio</button>
                <a href="/dashboard.php" class="btn-outline px-8">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
