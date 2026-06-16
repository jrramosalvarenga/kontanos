<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /admin/providers.php'); exit; }

$provider = DB::fetch("
    SELECT pp.*, u.email, u.points
    FROM provider_profiles pp
    JOIN users u ON u.id = pp.user_id
    WHERE pp.id = $1
", [$id]);
if (!$provider) { header('Location: /admin/providers.php'); exit; }

$categories = getCategories();
$errors = [];
$saved  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $ptRaw = trim($_POST['profile_type'] ?? 'personal');
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
        'profile_type'     => in_array($ptRaw, ['personal', 'business']) ? $ptRaw : 'personal',
        'business_name'    => trim($_POST['business_name'] ?? ''),
        'is_featured'      => isset($_POST['is_featured']) ? true : false,
        'is_verified'      => isset($_POST['is_verified']) ? true : false,
        'admin_priority'   => (int)($_POST['admin_priority'] ?? 0),
    ];

    if (!$data['full_name']) $errors[] = 'El nombre es requerido.';

    if (empty($errors)) {
        $businessName = ($data['profile_type'] === 'business' && $data['business_name'] !== '') ? $data['business_name'] : null;

        DB::query("
            UPDATE provider_profiles SET
                full_name = $1, tagline = $2, bio = $3, phone = $4, whatsapp = $5,
                website = $6, instagram = $7, facebook = $8, linkedin = $9,
                category_id = $10, location_id = $11, years_experience = $12,
                response_time = $13, profile_type = $14, business_name = $15,
                is_featured = $16, is_verified = $17, admin_priority = $18,
                updated_at = NOW()
            WHERE id = $19
        ", [
            $data['full_name'], $data['tagline'], $data['bio'], $data['phone'], $data['whatsapp'],
            $data['website'], $data['instagram'], $data['facebook'], $data['linkedin'],
            $data['category_id'], $data['location_id'], $data['years_experience'],
            $data['response_time'], $data['profile_type'], $businessName,
            $data['is_featured'] ? 'true' : 'false',
            $data['is_verified'] ? 'true' : 'false',
            $data['admin_priority'],
            $id,
        ]);

        // Tags
        $tagsRaw = trim($_POST['tags'] ?? '');
        DB::query("DELETE FROM provider_tags WHERE provider_id = $1", [$id]);
        if ($tagsRaw !== '') {
            $tagList = array_unique(array_filter(array_map('trim', explode(',', $tagsRaw))));
            foreach (array_slice($tagList, 0, 15) as $tagName) {
                $tagSlug = slugify($tagName);
                $tag = DB::fetch("SELECT id FROM tags WHERE slug = $1", [$tagSlug]);
                if (!$tag) {
                    $tagId = DB::insert("INSERT INTO tags (name, slug) VALUES ($1, $2) RETURNING id", [$tagName, $tagSlug]);
                } else {
                    $tagId = $tag['id'];
                }
                DB::query("INSERT INTO provider_tags (provider_id, tag_id) VALUES ($1, $2) ON CONFLICT DO NOTHING", [$id, $tagId]);
            }
        }

        $provider = DB::fetch("SELECT pp.*, u.email, u.points FROM provider_profiles pp JOIN users u ON u.id = pp.user_id WHERE pp.id = $1", [$id]);
        $saved = true;
    }
}

// Tags
$existingTags = DB::fetchAll("SELECT t.name FROM tags t JOIN provider_tags pt ON pt.tag_id = t.id WHERE pt.provider_id = $1", [$id]);
$tagsStr  = implode(', ', array_column($existingTags, 'name'));
$tagsJson = json_encode(array_values(array_filter(array_map('trim', explode(',', $tagsStr)))), JSON_UNESCAPED_UNICODE);

// Locations cascade
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

$currentCountry    = '';
$currentLocationId = (int)($provider['location_id'] ?? 0);
if ($currentLocationId) {
    foreach ($allLocations as $loc) {
        if ((int)$loc['id'] === $currentLocationId) { $currentCountry = $loc['country']; break; }
    }
}

$pageTitle = 'Editar: ' . ($provider['full_name'] ?? '');
$activeNav = 'providers';
require __DIR__ . '/includes/layout_header.php';
?>

<div class="max-w-2xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="/admin/providers.php" class="text-sm text-brand-600 hover:text-brand-800 flex items-center gap-1">
            ← Volver a profesionales
        </a>
        <span class="text-gray-300">|</span>
        <a href="/p/<?= e($provider['slug']) ?>" target="_blank" class="text-sm text-brand-600 hover:underline">
            Ver perfil público →
        </a>
    </div>

    <?php if ($saved): ?>
    <div class="bg-brand-50 border border-brand-200 text-brand-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        Perfil actualizado correctamente.
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm"><?= e($errors[0]) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5"
          x-data="{
              locations: <?= e($locationsJson) ?>,
              selectedCountry: '<?= addslashes($currentCountry) ?>',
              selectedCity: <?= $currentLocationId ?: 'null' ?>,
              get countries() { return Object.keys(this.locations); },
              get cities() { return this.selectedCountry ? (this.locations[this.selectedCountry] || []) : []; },
              onCountryChange() { this.selectedCity = null; },
              profileType: '<?= addslashes($provider['profile_type'] ?? 'personal') ?>',
              tagInput: '',
              tags: <?= $tagsJson ?>,
              addTag() {
                  const t = this.tagInput.trim().replace(/,/g, '');
                  if (t && !this.tags.includes(t) && this.tags.length < 15) this.tags.push(t);
                  this.tagInput = '';
              },
              removeTag(i) { this.tags.splice(i, 1); },
              get tagsStr() { return this.tags.join(','); },
              bioLen: <?= mb_strlen($provider['bio'] ?? '') ?>,
              taglineLen: <?= mb_strlen($provider['tagline'] ?? '') ?>
          }">
        <input type="hidden" name="_token" value="<?= csrfToken() ?>">

        <!-- Admin flags -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2.5">
                <span class="w-6 h-6 bg-gray-700 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">A</span>
                <h2 class="font-bold text-gray-900">Configuración admin</h2>
            </div>
            <div class="p-6">
                <div class="grid sm:grid-cols-3 gap-4">
                    <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-gray-200 hover:border-amber-300 transition-colors">
                        <input type="checkbox" name="is_featured" value="1" <?= $provider['is_featured'] ? 'checked' : '' ?>
                               class="w-4 h-4 text-amber-500 rounded">
                        <span class="text-sm font-medium text-gray-700">⭐ Destacado</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-gray-200 hover:border-blue-300 transition-colors">
                        <input type="checkbox" name="is_verified" value="1" <?= $provider['is_verified'] ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-500 rounded">
                        <span class="text-sm font-medium text-gray-700">✓ Verificado</span>
                    </label>
                    <div class="flex items-center gap-2 p-3 rounded-xl border border-gray-200">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Prioridad</label>
                        <input type="number" name="admin_priority" value="<?= (int)$provider['admin_priority'] ?>"
                               class="form-input py-1 px-2 text-center flex-1" min="0" max="100">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-3">Email: <span class="font-mono"><?= e($provider['email']) ?></span> · Puntos: <?= (int)$provider['points'] ?></p>
            </div>
        </div>

        <!-- Presentación -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2.5">
                <span class="w-6 h-6 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">1</span>
                <h2 class="font-bold text-gray-900">Presentación</h2>
            </div>
            <div class="p-6 space-y-5">

                <!-- Tipo de perfil -->
                <div>
                    <label class="form-label">Tipo de perfil</label>
                    <input type="hidden" name="profile_type" :value="profileType">
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="profileType = 'personal'"
                                :class="profileType === 'personal' ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-gray-600 border-gray-200 hover:border-brand-300'"
                                class="border-2 rounded-xl py-2.5 px-3 text-sm font-semibold transition-all text-left flex items-center gap-2">
                            <span class="text-lg">👤</span>
                            <span>
                                <span class="block text-sm">Profesional</span>
                                <span class="text-xs font-normal opacity-70">Freelancer · independiente</span>
                            </span>
                        </button>
                        <button type="button" @click="profileType = 'business'"
                                :class="profileType === 'business' ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-gray-600 border-gray-200 hover:border-brand-300'"
                                class="border-2 rounded-xl py-2.5 px-3 text-sm font-semibold transition-all text-left flex items-center gap-2">
                            <span class="text-lg">🏢</span>
                            <span>
                                <span class="block text-sm">Negocio / empresa</span>
                                <span class="text-xs font-normal opacity-70">Taller · empresa · tienda</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div x-show="profileType === 'business'" x-transition>
                    <label class="form-label">Nombre del negocio</label>
                    <input type="text" name="business_name" class="form-input"
                           value="<?= e($provider['business_name'] ?? '') ?>"
                           placeholder="Ej: Taller Mecánico López">
                </div>

                <div>
                    <label class="form-label" x-text="profileType === 'business' ? 'Nombre del propietario / representante *' : 'Nombre completo *'"></label>
                    <input type="text" name="full_name" class="form-input" required
                           value="<?= e($provider['full_name'] ?? '') ?>">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="form-label mb-0">Frase descriptiva</label>
                        <span class="text-xs" :class="taglineLen > 130 ? 'text-amber-500' : 'text-gray-400'"><span x-text="taglineLen"></span>/150</span>
                    </div>
                    <input type="text" name="tagline" class="form-input"
                           value="<?= e($provider['tagline'] ?? '') ?>"
                           maxlength="150"
                           @input="taglineLen = $event.target.value.length">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="form-label mb-0">Sobre mí / descripción</label>
                        <span class="text-xs" :class="bioLen > 1800 ? 'text-amber-500' : 'text-gray-400'"><span x-text="bioLen"></span>/2000</span>
                    </div>
                    <textarea name="bio" class="form-input" rows="6" maxlength="2000"
                              @input="bioLen = $event.target.value.length"><?= e($provider['bio'] ?? '') ?></textarea>
                </div>

            </div>
        </div>

        <!-- Categoría y ubicación -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2.5">
                <span class="w-6 h-6 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">2</span>
                <h2 class="font-bold text-gray-900">Categoría y ubicación</h2>
            </div>
            <div class="p-6 space-y-4">

                <div>
                    <label class="form-label">Categoría</label>
                    <select name="category_id" class="form-input">
                        <option value="">Sin categoría</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ($provider['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">País</label>
                        <select class="form-input" x-model="selectedCountry" @change="onCountryChange()">
                            <option value="">Seleccionar país</option>
                            <template x-for="c in countries" :key="c">
                                <option :value="c" x-text="c"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Ciudad</label>
                        <select name="location_id" class="form-input" x-model="selectedCity" :disabled="!selectedCountry">
                            <option value="" x-text="selectedCountry ? 'Seleccionar ciudad' : 'Primero elige el país'"></option>
                            <template x-for="city in cities" :key="city.id">
                                <option :value="city.id" x-text="city.city + (city.state ? ', ' + city.state : '')"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Años de experiencia</label>
                        <input type="number" name="years_experience" class="form-input" min="0" max="60"
                               value="<?= (int)($provider['years_experience'] ?? 0) ?>">
                    </div>
                    <div>
                        <label class="form-label">Tiempo de respuesta</label>
                        <select name="response_time" class="form-input">
                            <?php foreach (['< 1 hora' => 'Menos de 1 hora', '< 3 horas' => 'Menos de 3 horas', '< 24 horas' => 'Menos de 24 horas', '1-2 días' => '1 a 2 días'] as $val => $label): ?>
                            <option value="<?= e($val) ?>" <?= ($provider['response_time'] ?? '') === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Tags -->
                <div>
                    <label class="form-label">Habilidades y especialidades</label>
                    <div class="border border-gray-200 rounded-xl p-3 focus-within:border-brand-400 focus-within:ring-2 focus-within:ring-brand-100 transition-all bg-white min-h-[52px]">
                        <div class="flex flex-wrap gap-1.5 mb-2" x-show="tags.length > 0">
                            <template x-for="(tag, i) in tags" :key="i">
                                <span class="inline-flex items-center gap-1 bg-brand-100 text-brand-800 text-xs font-medium rounded-full px-2.5 py-1">
                                    <span x-text="tag"></span>
                                    <button type="button" @click="removeTag(i)" class="text-brand-500 hover:text-brand-800 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>
                        <input type="text" x-model="tagInput"
                               @keydown.enter.prevent="addTag()"
                               @keydown.comma.prevent="addTag()"
                               :disabled="tags.length >= 15"
                               :placeholder="tags.length === 0 ? 'Escribe y presiona Enter...' : (tags.length >= 15 ? 'Máximo 15 etiquetas' : 'Agregar otra...')"
                               class="outline-none text-sm text-gray-700 placeholder-gray-400 w-full bg-transparent">
                    </div>
                    <input type="hidden" name="tags" :value="tagsStr">
                    <p class="text-xs text-gray-400 mt-1"><span x-text="tags.length"></span>/15 etiquetas</p>
                </div>

            </div>
        </div>

        <!-- Contacto -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2.5">
                <span class="w-6 h-6 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">3</span>
                <h2 class="font-bold text-gray-900">Contacto</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">WhatsApp</label>
                        <input type="tel" name="whatsapp" class="form-input"
                               value="<?= e($provider['whatsapp'] ?? '') ?>" placeholder="+504 9999 9999">
                    </div>
                    <div>
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="phone" class="form-input"
                               value="<?= e($provider['phone'] ?? '') ?>" placeholder="+504 2222 2222">
                    </div>
                </div>
                <div>
                    <label class="form-label">Sitio web</label>
                    <input type="url" name="website" class="form-input"
                           value="<?= e($provider['website'] ?? '') ?>" placeholder="https://misitioweb.com">
                </div>
            </div>
        </div>

        <!-- Redes sociales -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2.5">
                <span class="w-6 h-6 bg-gray-300 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">4</span>
                <h2 class="font-bold text-gray-900">Redes sociales</h2>
            </div>
            <div class="p-6">
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Instagram</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm select-none">@</span>
                            <input type="text" name="instagram" class="form-input pl-7"
                                   value="<?= e($provider['instagram'] ?? '') ?>" placeholder="usuario">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Facebook</label>
                        <input type="text" name="facebook" class="form-input"
                               value="<?= e($provider['facebook'] ?? '') ?>" placeholder="pagina-o-usuario">
                    </div>
                    <div>
                        <label class="form-label">LinkedIn</label>
                        <input type="text" name="linkedin" class="form-input"
                               value="<?= e($provider['linkedin'] ?? '') ?>" placeholder="in/usuario">
                    </div>
                </div>
            </div>
        </div>

        <!-- Guardar -->
        <div class="flex gap-3 pb-6">
            <button type="submit" class="flex-1 bg-brand-700 hover:bg-brand-800 text-white font-bold py-3.5 rounded-xl text-base transition-colors">
                Guardar cambios
            </button>
            <a href="/admin/providers.php" class="px-6 py-3.5 border border-gray-200 hover:bg-gray-50 text-gray-600 font-medium rounded-xl transition-colors text-base text-center">
                Cancelar
            </a>
        </div>

    </form>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
