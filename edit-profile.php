<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireProvider();
$user     = currentUser();
$profile  = DB::fetch("SELECT * FROM provider_profiles WHERE user_id = $1", [$user['id']]);
$categories = getCategories();
$locations  = getLocations();

$errors = [];
$saved  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $data = [
        'full_name'        => trim($_POST['full_name'] ?? ''),
        'tagline'          => trim($_POST['tagline'] ?? ''),
        'bio'              => trim($_POST['bio'] ?? ''),
        'phone'            => trim($_POST['phone'] ?? ''),
        'whatsapp'         => trim($_POST['whatsapp'] ?? ''),
        'website'          => trim($_POST['website'] ?? ''),
        'instagram'        => trim($_POST['instagram'] ?? ''),
        'facebook'         => trim($_POST['facebook'] ?? ''),
        'linkedin'         => trim($_POST['linkedin'] ?? ''),
        'category_id'      => (int)($_POST['category_id'] ?? 0) ?: null,
        'location_id'      => (int)($_POST['location_id'] ?? 0) ?: null,
        'years_experience' => (int)($_POST['years_experience'] ?? 0),
        'response_time'    => trim($_POST['response_time'] ?? ''),
        'avatar_url'       => trim($_POST['avatar_url'] ?? ''),
    ];

    if (!$data['full_name']) {
        $errors[] = 'El nombre es requerido.';
    }

    if (empty($errors)) {
        if ($profile) {
            DB::query("
                UPDATE provider_profiles SET
                    full_name = $1, tagline = $2, bio = $3, phone = $4, whatsapp = $5,
                    website = $6, instagram = $7, facebook = $8, linkedin = $9,
                    category_id = $10, location_id = $11, years_experience = $12,
                    response_time = $13, avatar_url = $14, updated_at = NOW()
                WHERE user_id = $15
            ", array_merge(array_values($data), [$user['id']]));
        } else {
            $slug = slugify($data['full_name']) . '-' . substr(uniqid(), -4);
            DB::query("
                INSERT INTO provider_profiles (user_id, slug, full_name, tagline, bio, phone, whatsapp, website, instagram, facebook, linkedin, category_id, location_id, years_experience, response_time, avatar_url)
                VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16)
            ", [$user['id'], $slug, ...array_values($data)]);
        }

        // Update tags
        $tagsRaw = trim($_POST['tags'] ?? '');
        if ($profile && $tagsRaw !== '') {
            DB::query("DELETE FROM provider_tags WHERE provider_id = $1", [$profile['id']]);
            $tagList = array_unique(array_filter(array_map('trim', explode(',', $tagsRaw))));
            foreach (array_slice($tagList, 0, 15) as $tagName) {
                $tagSlug = slugify($tagName);
                $tag = DB::fetch("SELECT id FROM tags WHERE slug = $1", [$tagSlug]);
                if (!$tag) {
                    $tagId = DB::insert("INSERT INTO tags (name, slug) VALUES ($1, $2) RETURNING id", [$tagName, $tagSlug]);
                } else {
                    $tagId = $tag['id'];
                }
                DB::query("INSERT INTO provider_tags (provider_id, tag_id) VALUES ($1, $2) ON CONFLICT DO NOTHING", [$profile['id'], $tagId]);
            }
        }

        $profile = DB::fetch("SELECT * FROM provider_profiles WHERE user_id = $1", [$user['id']]);
        $saved = true;
    }
}

// Get existing tags
$existingTags = $profile ? DB::fetchAll("SELECT t.name FROM tags t JOIN provider_tags pt ON pt.tag_id = t.id WHERE pt.provider_id = $1", [$profile['id']]) : [];
$tagsStr = implode(', ', array_column($existingTags, 'name'));

// Pre-load locations for cascade selector
$allLocations = DB::fetchAll("SELECT id, country, state, city FROM locations WHERE is_active = TRUE ORDER BY country, city");
$locationsByCountry = [];
foreach ($allLocations as $loc) {
    $locationsByCountry[$loc['country']][] = ['id' => (int)$loc['id'], 'city' => $loc['city'], 'state' => $loc['state']];
}
uksort($locationsByCountry, function($a, $b) {
    if ($a === 'Honduras') return -1;
    if ($b === 'Honduras') return 1;
    return strcmp($a, $b);
});
$locationsJson = json_encode($locationsByCountry, JSON_UNESCAPED_UNICODE);

$currentCountry = '';
$currentLocationId = (int)($profile['location_id'] ?? 0);
if ($currentLocationId) {
    foreach ($allLocations as $loc) {
        if ((int)$loc['id'] === $currentLocationId) {
            $currentCountry = $loc['country'];
            break;
        }
    }
}

$pageTitle = 'Editar Perfil | Kontactanos';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="/dashboard.php" class="text-sm text-brand-600 hover:underline flex items-center gap-1 mb-1">
                ← Volver al panel
            </a>
            <h1 class="text-2xl font-extrabold text-gray-900">Editar Perfil Público</h1>
        </div>
        <?php if ($profile): ?>
        <a href="/p/<?= e($profile['slug']) ?>" target="_blank" class="btn-outline text-sm">
            Ver perfil →
        </a>
        <?php endif; ?>
    </div>

    <?php if ($saved): ?>
    <div class="bg-brand-50 border border-brand-200 text-brand-800 rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        Perfil actualizado exitosamente.
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm"><?= e($errors[0]) ?></div>
    <?php endif; ?>

    <form method="POST" action="/edit-profile.php" class="space-y-6"
          x-data="{
              locations: <?= e($locationsJson) ?>,
              selectedCountry: '<?= addslashes($currentCountry) ?>',
              selectedCity: <?= $currentLocationId ?: 'null' ?>,
              get countries() { return Object.keys(this.locations); },
              get cities() { return this.selectedCountry ? (this.locations[this.selectedCountry] || []) : []; },
              onCountryChange() { this.selectedCity = null; }
          }">
        <input type="hidden" name="_token" value="<?= csrfToken() ?>">

        <!-- Basic info -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 mb-5 text-base">Información básica</h2>
            <div class="space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="form-label">Nombre completo / Nombre del negocio *</label>
                        <input type="text" name="full_name" class="form-input" required
                               value="<?= e($profile['full_name'] ?? '') ?>"
                               placeholder="Carlos Rodríguez">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Tagline <span class="text-gray-400 font-normal">(describe lo que haces en una línea)</span></label>
                        <input type="text" name="tagline" class="form-input"
                               value="<?= e($profile['tagline'] ?? '') ?>"
                               placeholder="Electricista certificado con 10 años de experiencia"
                               data-maxlength="150">
                    </div>
                    <div>
                        <label class="form-label">Categoría</label>
                        <select name="category_id" class="form-input">
                            <option value="">Seleccionar</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ($profile['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">País</label>
                        <select class="form-input"
                                x-model="selectedCountry"
                                @change="onCountryChange()">
                            <option value="">Seleccionar país</option>
                            <template x-for="c in countries" :key="c">
                                <option :value="c" x-text="c"></option>
                            </template>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Ciudad / Municipio</label>
                        <select name="location_id" class="form-input"
                                x-model="selectedCity"
                                :disabled="!selectedCountry">
                            <option value="">
                                <span x-show="!selectedCountry">Primero elige el país</span>
                                <span x-show="selectedCountry">Seleccionar ciudad</span>
                            </option>
                            <template x-for="city in cities" :key="city.id">
                                <option :value="city.id" x-text="city.city + (city.state ? ', ' + city.state : '')"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Años de experiencia</label>
                        <input type="number" name="years_experience" class="form-input" min="0" max="60"
                               value="<?= (int)($profile['years_experience'] ?? 0) ?>">
                    </div>
                    <div>
                        <label class="form-label">Tiempo de respuesta</label>
                        <select name="response_time" class="form-input">
                            <option value="< 1 hora" <?= ($profile['response_time'] ?? '') === '< 1 hora' ? 'selected' : '' ?>>Menos de 1 hora</option>
                            <option value="< 3 horas" <?= ($profile['response_time'] ?? '') === '< 3 horas' ? 'selected' : '' ?>>Menos de 3 horas</option>
                            <option value="< 24 horas" <?= ($profile['response_time'] ?? '') === '< 24 horas' ? 'selected' : '' ?>>Menos de 24 horas</option>
                            <option value="1-2 días" <?= ($profile['response_time'] ?? '') === '1-2 días' ? 'selected' : '' ?>>1-2 días</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Foto de perfil (URL de imagen)</label>
                    <input type="url" name="avatar_url" class="form-input"
                           value="<?= e($profile['avatar_url'] ?? '') ?>"
                           placeholder="https://...">
                    <p class="text-xs text-gray-400 mt-1">Usa una URL de imagen pública. Recomendamos fotos cuadradas de al menos 300x300px.</p>
                </div>

                <div>
                    <label class="form-label">Sobre mí / Descripción</label>
                    <textarea name="bio" class="form-input" rows="5" data-maxlength="2000"
                              placeholder="Cuéntale a tus clientes quién eres, tu experiencia y qué te diferencia..."><?= e($profile['bio'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="form-label">Habilidades / Etiquetas <span class="text-gray-400 font-normal">(separadas por comas)</span></label>
                    <input type="text" name="tags" class="form-input"
                           value="<?= e($tagsStr) ?>"
                           placeholder="Instalación eléctrica, Tableros, Automatización, Iluminación LED">
                    <p class="text-xs text-gray-400 mt-1">Máximo 15 etiquetas. Ayudan a aparecer en búsquedas.</p>
                </div>
            </div>
        </div>

        <!-- Contact info -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 mb-5 text-base">Información de contacto</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Teléfono</label>
                    <input type="tel" name="phone" class="form-input"
                           value="<?= e($profile['phone'] ?? '') ?>"
                           placeholder="+58 412 0000000">
                </div>
                <div>
                    <label class="form-label">WhatsApp</label>
                    <input type="tel" name="whatsapp" class="form-input"
                           value="<?= e($profile['whatsapp'] ?? '') ?>"
                           placeholder="+58 412 0000000">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Sitio web</label>
                    <input type="url" name="website" class="form-input"
                           value="<?= e($profile['website'] ?? '') ?>"
                           placeholder="https://misitioweb.com">
                </div>
            </div>
        </div>

        <!-- Social media -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 mb-5 text-base">Redes sociales</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Instagram</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@</span>
                        <input type="text" name="instagram" class="form-input pl-7"
                               value="<?= e($profile['instagram'] ?? '') ?>" placeholder="usuario">
                    </div>
                </div>
                <div>
                    <label class="form-label">Facebook</label>
                    <input type="text" name="facebook" class="form-input"
                           value="<?= e($profile['facebook'] ?? '') ?>" placeholder="pagina-o-usuario">
                </div>
                <div>
                    <label class="form-label">LinkedIn</label>
                    <input type="text" name="linkedin" class="form-input"
                           value="<?= e($profile['linkedin'] ?? '') ?>" placeholder="in/usuario">
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="btn-primary flex-1 py-3.5 text-base">
                Guardar cambios
            </button>
            <a href="/dashboard.php" class="btn-outline px-8">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
