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

// Profile completion
$tagsArray = array_values(array_filter(array_map('trim', explode(',', $tagsStr))));
$tagsJson  = json_encode($tagsArray, JSON_UNESCAPED_UNICODE);

$completionFields = [
    ['label' => 'Nombre',      'done' => !empty($profile['full_name'])],
    ['label' => 'Tagline',     'done' => !empty($profile['tagline'])],
    ['label' => 'Descripción', 'done' => !empty($profile['bio'])],
    ['label' => 'Foto',        'done' => !empty($profile['avatar_url'])],
    ['label' => 'Categoría',   'done' => !empty($profile['category_id'])],
    ['label' => 'Ciudad',      'done' => !empty($profile['location_id'])],
    ['label' => 'Contacto',    'done' => !empty($profile['whatsapp']) || !empty($profile['phone'])],
    ['label' => 'Etiquetas',   'done' => !empty($tagsStr)],
];
$completionDone = count(array_filter($completionFields, fn($i) => $i['done']));
$completionPct  = (int)($completionDone / count($completionFields) * 100);
$pendingFields  = array_values(array_filter($completionFields, fn($i) => !$i['done']));

$pageTitle = 'Editar Perfil | Kontactanos';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="/dashboard.php" class="text-sm text-brand-600 hover:text-brand-800 flex items-center gap-1 mb-1 transition-colors">
                ← Volver al panel
            </a>
            <h1 class="text-2xl font-extrabold text-gray-900">Mi perfil público</h1>
        </div>
        <?php if ($profile): ?>
        <a href="/p/<?= e($profile['slug']) ?>" target="_blank"
           class="flex items-center gap-1.5 text-sm text-brand-700 border border-brand-200 hover:bg-brand-50 px-4 py-2 rounded-xl transition-colors font-medium">
            Ver perfil
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
        <?php endif; ?>
    </div>

    <!-- Completion bar -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700">
                <?php if ($completionPct === 100): ?>
                    ¡Perfil completo!
                <?php else: ?>
                    Perfil <?= $completionPct ?>% completo
                <?php endif; ?>
            </span>
            <span class="text-xs text-gray-400"><?= $completionDone ?>/<?= count($completionFields) ?> campos</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2 mb-3">
            <div class="h-2 rounded-full transition-all <?= $completionPct === 100 ? 'bg-brand-500' : 'bg-brand-400' ?>"
                 style="width: <?= $completionPct ?>%"></div>
        </div>
        <?php if (!empty($pendingFields)): ?>
        <p class="text-xs text-gray-500">
            Faltan:
            <?php foreach ($pendingFields as $i => $f): ?>
                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 rounded px-1.5 py-0.5 mr-1 mb-1">
                    <?= e($f['label']) ?>
                </span>
            <?php endforeach; ?>
        </p>
        <?php endif; ?>
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

    <form method="POST" action="/edit-profile.php" class="space-y-5"
          x-data="{
              locations: <?= e($locationsJson) ?>,
              selectedCountry: '<?= addslashes($currentCountry) ?>',
              selectedCity: <?= $currentLocationId ?: 'null' ?>,
              get countries() { return Object.keys(this.locations); },
              get cities() { return this.selectedCountry ? (this.locations[this.selectedCountry] || []) : []; },
              onCountryChange() { this.selectedCity = null; },

              avatarUrl: '<?= addslashes(e($profile['avatar_url'] ?? '')) ?>',

              tagInput: '',
              tags: <?= $tagsJson ?>,
              addTag() {
                  const t = this.tagInput.trim().replace(/,/g, '');
                  if (t && !this.tags.includes(t) && this.tags.length < 15) {
                      this.tags.push(t);
                  }
                  this.tagInput = '';
              },
              removeTag(i) { this.tags.splice(i, 1); },
              get tagsStr() { return this.tags.join(','); },

              bioLen: <?= mb_strlen($profile['bio'] ?? '') ?>,
              taglineLen: <?= mb_strlen($profile['tagline'] ?? '') ?>
          }">
        <input type="hidden" name="_token" value="<?= csrfToken() ?>">

        <!-- 1. Tu presentación -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">1</span>
                    <h2 class="font-bold text-gray-900">Tu presentación</h2>
                </div>
                <p class="text-xs text-gray-400 mt-0.5 ml-8.5">Lo primero que ven tus clientes</p>
            </div>
            <div class="p-6 space-y-5">

                <!-- Avatar -->
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-2xl bg-gray-100 border-2 border-gray-200 overflow-hidden flex items-center justify-center">
                            <template x-if="avatarUrl">
                                <img :src="avatarUrl" alt="Preview" class="w-full h-full object-cover"
                                     @error="avatarUrl = ''">
                            </template>
                            <template x-if="!avatarUrl">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </template>
                        </div>
                        <p class="text-xs text-gray-400 text-center mt-1.5">Vista previa</p>
                    </div>
                    <div class="flex-1">
                        <label class="form-label">Foto de perfil</label>
                        <input type="url" name="avatar_url" class="form-input"
                               x-model="avatarUrl"
                               placeholder="https://ejemplo.com/foto.jpg">
                        <p class="text-xs text-gray-400 mt-1.5 leading-relaxed">
                            Pega una URL de imagen. Sube tu foto a
                            <a href="https://imgur.com" target="_blank" class="text-brand-600 hover:underline">imgur.com</a>
                            y copia el enlace directo.
                        </p>
                    </div>
                </div>

                <!-- Nombre -->
                <div>
                    <label class="form-label">Nombre completo o nombre del negocio <span class="text-red-400">*</span></label>
                    <input type="text" name="full_name" class="form-input" required
                           value="<?= e($profile['full_name'] ?? '') ?>"
                           placeholder="Ej: Carlos Rodríguez o Electricidad Rápida">
                </div>

                <!-- Tagline -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="form-label mb-0">Frase descriptiva</label>
                        <span class="text-xs" :class="taglineLen > 130 ? 'text-amber-500' : 'text-gray-400'">
                            <span x-text="taglineLen"></span>/150
                        </span>
                    </div>
                    <input type="text" name="tagline" class="form-input"
                           value="<?= e($profile['tagline'] ?? '') ?>"
                           placeholder="Ej: Electricista certificado con 10 años de experiencia en Tegucigalpa"
                           maxlength="150"
                           @input="taglineLen = $event.target.value.length">
                    <p class="text-xs text-gray-400 mt-1">Lo que aparece debajo de tu nombre. Sé específico.</p>
                </div>

                <!-- Bio -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="form-label mb-0">Sobre mí</label>
                        <span class="text-xs" :class="bioLen > 1800 ? 'text-amber-500' : 'text-gray-400'">
                            <span x-text="bioLen"></span>/2000
                        </span>
                    </div>
                    <textarea name="bio" class="form-input" rows="5" maxlength="2000"
                              placeholder="Cuéntales a tus clientes quién eres, qué haces y por qué deberían contratarte. Menciona tu experiencia, especialidades y zona de trabajo."
                              @input="bioLen = $event.target.value.length"><?= e($profile['bio'] ?? '') ?></textarea>
                </div>

            </div>
        </div>

        <!-- 2. Categoría y ubicación -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">2</span>
                    <h2 class="font-bold text-gray-900">Categoría y ubicación</h2>
                </div>
                <p class="text-xs text-gray-400 mt-0.5 ml-8.5">Ayuda a que te encuentren en búsquedas</p>
            </div>
            <div class="p-6 space-y-4">

                <div>
                    <label class="form-label">¿En qué categoría encaja tu servicio?</label>
                    <select name="category_id" class="form-input">
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ($profile['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
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
                               value="<?= (int)($profile['years_experience'] ?? 0) ?>"
                               placeholder="0">
                    </div>
                    <div>
                        <label class="form-label">Tiempo de respuesta típico</label>
                        <select name="response_time" class="form-input">
                            <option value="< 1 hora" <?= ($profile['response_time'] ?? '') === '< 1 hora' ? 'selected' : '' ?>>Menos de 1 hora</option>
                            <option value="< 3 horas" <?= ($profile['response_time'] ?? '') === '< 3 horas' ? 'selected' : '' ?>>Menos de 3 horas</option>
                            <option value="< 24 horas" <?= ($profile['response_time'] ?? '') === '< 24 horas' ? 'selected' : '' ?>>Menos de 24 horas</option>
                            <option value="1-2 días" <?= ($profile['response_time'] ?? '') === '1-2 días' ? 'selected' : '' ?>>1 a 2 días</option>
                        </select>
                    </div>
                </div>

                <!-- Tags chip input -->
                <div>
                    <label class="form-label">Habilidades y especialidades</label>
                    <div class="border border-gray-200 rounded-xl p-3 focus-within:border-brand-400 focus-within:ring-2 focus-within:ring-brand-100 transition-all bg-white min-h-[52px]">
                        <div class="flex flex-wrap gap-1.5 mb-2" x-show="tags.length > 0">
                            <template x-for="(tag, i) in tags" :key="i">
                                <span class="inline-flex items-center gap-1 bg-brand-100 text-brand-800 text-xs font-medium rounded-full px-2.5 py-1">
                                    <span x-text="tag"></span>
                                    <button type="button" @click="removeTag(i)"
                                            class="text-brand-500 hover:text-brand-800 transition-colors leading-none ml-0.5">
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
                               :placeholder="tags.length === 0 ? 'Escribe una habilidad y presiona Enter...' : (tags.length >= 15 ? 'Máximo 15 etiquetas' : 'Agregar otra...')"
                               class="outline-none text-sm text-gray-700 placeholder-gray-400 w-full bg-transparent">
                    </div>
                    <input type="hidden" name="tags" :value="tagsStr">
                    <p class="text-xs text-gray-400 mt-1.5">
                        Escribe y presiona <kbd class="px-1 py-0.5 bg-gray-100 border border-gray-200 rounded text-xs font-mono">Enter</kbd> o <kbd class="px-1 py-0.5 bg-gray-100 border border-gray-200 rounded text-xs font-mono">,</kbd>  para agregar.
                        <span x-text="tags.length" class="font-medium text-gray-500"></span>/15 etiquetas.
                    </p>
                </div>

            </div>
        </div>

        <!-- 3. Contacto -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">3</span>
                    <h2 class="font-bold text-gray-900">Cómo te contactan</h2>
                </div>
                <p class="text-xs text-gray-400 mt-0.5 ml-8.5">Los clientes te escriben directamente</p>
            </div>
            <div class="p-6 space-y-4">

                <!-- WhatsApp primero y destacado -->
                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <label class="block text-sm font-semibold text-green-800 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        WhatsApp — contacto principal
                    </label>
                    <input type="tel" name="whatsapp" class="form-input bg-white"
                           value="<?= e($profile['whatsapp'] ?? '') ?>"
                           placeholder="+504 9999 9999">
                    <p class="text-xs text-green-700 mt-1.5">Es el botón más clickeado en tu perfil. Incluye el código de país.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="phone" class="form-input"
                               value="<?= e($profile['phone'] ?? '') ?>"
                               placeholder="+504 2222 2222">
                    </div>
                    <div>
                        <label class="form-label">Sitio web <span class="text-gray-400 font-normal text-xs">(opcional)</span></label>
                        <input type="url" name="website" class="form-input"
                               value="<?= e($profile['website'] ?? '') ?>"
                               placeholder="https://misitioweb.com">
                    </div>
                </div>

            </div>
        </div>

        <!-- 4. Redes sociales -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 bg-gray-300 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">4</span>
                        <h2 class="font-bold text-gray-900">Redes sociales</h2>
                    </div>
                    <span class="text-xs text-gray-400 bg-gray-100 rounded-full px-2.5 py-1">Opcional</span>
                </div>
                <p class="text-xs text-gray-400 mt-0.5 ml-8.5">Aumenta la confianza mostrando tus perfiles</p>
            </div>
            <div class="p-6">
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Instagram</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm select-none">@</span>
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
        </div>

        <!-- Save -->
        <div class="flex gap-3 pb-4">
            <button type="submit" class="flex-1 bg-brand-700 hover:bg-brand-800 text-white font-bold py-3.5 rounded-xl text-base transition-colors">
                Guardar cambios
            </button>
            <a href="/dashboard.php" class="px-6 py-3.5 border border-gray-200 hover:bg-gray-50 text-gray-600 font-medium rounded-xl transition-colors text-base text-center">
                Cancelar
            </a>
        </div>

    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
