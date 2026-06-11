<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$errors = [];
$old = [
    'email'    => '',
    'full_name' => '',
    'tagline'  => '',
    'phone'    => '',
    'whatsapp' => '',
    'bio'      => '',
    'category_id' => '',
    'location_id' => '',
    'is_verified' => true,
    'is_featured' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $old['email']       = strtolower(trim($_POST['email'] ?? ''));
    $old['full_name']   = trim($_POST['full_name'] ?? '');
    $old['tagline']     = trim($_POST['tagline'] ?? '');
    $old['phone']       = trim($_POST['phone'] ?? '');
    $old['whatsapp']    = trim($_POST['whatsapp'] ?? '');
    $old['bio']         = trim($_POST['bio'] ?? '');
    $old['category_id'] = $_POST['category_id'] ?? '';
    $old['location_id'] = $_POST['location_id'] ?? '';
    $old['is_verified'] = !empty($_POST['is_verified']);
    $old['is_featured'] = !empty($_POST['is_featured']);

    if ($old['full_name'] === '') {
        $errors[] = 'El nombre del profesional es obligatorio.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ingresa un email válido.';
    } elseif (DB::fetch("SELECT id FROM users WHERE email = $1", [$old['email']])) {
        $errors[] = 'Ya existe un usuario con ese email.';
    }
    if (empty($old['category_id'])) {
        $errors[] = 'Selecciona una categoría.';
    }
    if (empty($old['location_id'])) {
        $errors[] = 'Selecciona una ciudad.';
    }

    if (empty($errors)) {
        DB::conn()->beginTransaction();
        try {
            $referralCode = generateReferralCode();
            $randomHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

            $userId = DB::insert("
                INSERT INTO users (email, password_hash, role, is_verified, is_active, referral_code)
                VALUES ($1, $2, 'provider', TRUE, TRUE, $3)
                RETURNING id
            ", [$old['email'], $randomHash, $referralCode]);

            $slug = slugify($old['full_name']) . '-' . substr(uniqid(), -4);

            DB::query("
                INSERT INTO provider_profiles
                    (user_id, slug, full_name, tagline, bio, phone, whatsapp, category_id, location_id, is_verified, is_featured)
                VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)
            ", [
                $userId,
                $slug,
                $old['full_name'],
                $old['tagline'] ?: null,
                $old['bio'] ?: null,
                $old['phone'] ?: null,
                $old['whatsapp'] ?: null,
                (int)$old['category_id'],
                (int)$old['location_id'],
                $old['is_verified'],
                $old['is_featured'],
            ]);

            DB::conn()->commit();
            header('Location: /admin/providers.php?created=1');
            exit;
        } catch (Exception $e) {
            DB::conn()->rollBack();
            error_log($e->getMessage());
            $errors[] = 'Error al crear el profesional. Intenta de nuevo.';
        }
    }
}

$categories = getCategories();
$locations  = getLocations();

$pageTitle = 'Agregar Profesional';
$activeNav = 'providers';
require __DIR__ . '/includes/layout_header.php';
?>

<a href="/admin/providers.php" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-600 mb-4">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Volver a Profesionales
</a>

<?php if ($errors): ?>
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm">
    <ul class="list-disc list-inside space-y-1">
        <?php foreach ($errors as $err): ?>
        <li><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-2xl space-y-5">
    <input type="hidden" name="_token" value="<?= csrfToken() ?>">

    <div>
        <label class="form-label">Nombre completo / Negocio *</label>
        <input type="text" name="full_name" value="<?= e($old['full_name']) ?>" required class="form-input">
    </div>

    <div>
        <label class="form-label">Email *</label>
        <input type="email" name="email" value="<?= e($old['email']) ?>" required class="form-input">
        <p class="text-xs text-gray-400 mt-1">Se usará para identificar la cuenta. El profesional podrá reclamarla más adelante.</p>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Categoría *</label>
            <select name="category_id" required class="form-input">
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= (string)$old['category_id'] === (string)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Ciudad *</label>
            <select name="location_id" required class="form-input">
                <option value="">Selecciona una ciudad</option>
                <?php foreach ($locations as $loc): ?>
                <option value="<?= (int)$loc['id'] ?>" <?= (string)$old['location_id'] === (string)$loc['id'] ? 'selected' : '' ?>>
                    <?= e($loc['city']) ?>, <?= e($loc['state']) ?>, <?= e($loc['country']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div>
        <label class="form-label">Tagline (frase corta)</label>
        <input type="text" name="tagline" value="<?= e($old['tagline']) ?>" maxlength="200" class="form-input">
    </div>

    <div>
        <label class="form-label">Descripción</label>
        <textarea name="bio" rows="4" class="form-input"><?= e($old['bio']) ?></textarea>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Teléfono</label>
            <input type="text" name="phone" value="<?= e($old['phone']) ?>" class="form-input">
        </div>
        <div>
            <label class="form-label">WhatsApp</label>
            <input type="text" name="whatsapp" value="<?= e($old['whatsapp']) ?>" placeholder="50498765432" class="form-input">
        </div>
    </div>

    <div class="flex items-center gap-6">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_verified" value="1" <?= $old['is_verified'] ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
            Verificado
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_featured" value="1" <?= $old['is_featured'] ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
            Destacado
        </label>
    </div>

    <div class="flex gap-3 pt-2">
        <button type="submit" class="btn-primary">Crear profesional</button>
        <a href="/admin/providers.php" class="btn-outline">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
