<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$role = in_array($_GET['role'] ?? '', ['provider', 'client']) ? $_GET['role'] : 'provider';
$errors = [];
$refCode = trim($_POST['ref_code'] ?? $_GET['ref'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $result = register($_POST);
    if ($result['success']) {
        $redirect = $_SESSION['user_role'] === 'provider' ? '/dashboard.php?welcome=1' : '/search.php?info=' . urlencode('¡Cuenta creada! Ya puedes buscar profesionales.');
        header('Location: ' . $redirect);
        exit;
    }
    $errors[] = $result['message'];
    $role = $_POST['role'] ?? $role;
}

$categories = getCategories();

// Pre-load all locations as JSON for Alpine.js cascade
$allLocations = DB::fetchAll("SELECT id, country, state, city FROM locations WHERE is_active = TRUE ORDER BY country, city");
$locationsByCountry = [];
foreach ($allLocations as $loc) {
    $locationsByCountry[$loc['country']][] = [
        'id'   => (int)$loc['id'],
        'city' => $loc['city'],
        'state'=> $loc['state'],
    ];
}
// Countries in a nice order: Honduras first, then rest alphabetically
uksort($locationsByCountry, function($a, $b) {
    if ($a === 'Honduras') return -1;
    if ($b === 'Honduras') return 1;
    return strcmp($a, $b);
});
$locationsJson = json_encode($locationsByCountry, JSON_UNESCAPED_UNICODE);

$selectedLocationId = (int)($_POST['location_id'] ?? 0);
$selectedCountry    = '';
if ($selectedLocationId) {
    foreach ($allLocations as $loc) {
        if ((int)$loc['id'] === $selectedLocationId) {
            $selectedCountry = $loc['country'];
            break;
        }
    }
}

$pageTitle = 'Crear cuenta gratuita | Kontactanos';
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <a href="/" class="inline-block mb-6">
                <img src="/assets/brand/kontanos-logo-color.svg" alt="Kontactanos" class="h-12 mx-auto">
            </a>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Crea tu cuenta gratis</h1>
            <p class="text-gray-500">Sin tarjeta de crédito · Sin comisiones · Para siempre gratis</p>
        </div>

        <!-- Role selector -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2 mb-6 grid grid-cols-2 gap-2">
            <a href="/register.php?role=provider"
               class="rounded-xl py-3 text-center font-semibold text-sm transition-all <?= $role === 'provider' ? 'bg-brand-700 text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' ?>">
                🎯 Ofrecer Servicios
            </a>
            <a href="/register.php?role=client"
               class="rounded-xl py-3 text-center font-semibold text-sm transition-all <?= $role === 'client' ? 'bg-brand-700 text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' ?>">
                🔍 Buscar Servicios
            </a>
        </div>

        <!-- Form card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm flex items-start gap-2">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <?= e($errors[0]) ?>
            </div>
            <?php endif; ?>

            <!-- Google OAuth button -->
            <?php if (GOOGLE_CLIENT_ID): ?>
            <a href="/oauth/google.php?role=<?= urlencode($role) ?>&ref=<?= urlencode($refCode) ?>"
               class="flex items-center justify-center gap-3 w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors mb-3">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continuar con Google
            </a>
            <?php endif; ?>

            <!-- Facebook Login -->
            <?php if (FACEBOOK_APP_ID): ?>
            <a href="/oauth/facebook.php?role=<?= urlencode($role) ?>&ref=<?= urlencode($refCode) ?>"
               class="flex items-center justify-center gap-3 w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors mb-5">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#1877F2">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Continuar con Facebook
            </a>
            <?php endif; ?>

            <?php if (GOOGLE_CLIENT_ID || FACEBOOK_APP_ID): ?>
            <div class="flex items-center gap-3 mb-5">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium">o con email</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
            <?php endif; ?>

            <!-- Email/Password form with Alpine.js cascade -->
            <form method="POST" action="/register.php"
                  id="register-form"
                  class="space-y-5"
                  x-data="{
                      locations: <?= e($locationsJson) ?>,
                      selectedCountry: '<?= addslashes($selectedCountry) ?>',
                      selectedCity: <?= $selectedLocationId ?: 'null' ?>,
                      get countries() { return Object.keys(this.locations); },
                      get cities() { return this.selectedCountry ? (this.locations[this.selectedCountry] || []) : []; },
                      onCountryChange() { this.selectedCity = null; }
                  }">
                <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="role" value="<?= e($role) ?>">
                <input type="hidden" name="ref_code" value="<?= e($refCode) ?>">

                <!-- Email & Password -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-input" required
                               value="<?= e($_POST['email'] ?? '') ?>"
                               placeholder="tu@email.com">
                    </div>
                    <div>
                        <label class="form-label">Contraseña *</label>
                        <input type="password" name="password" class="form-input" required
                               placeholder="Mínimo 8 caracteres" minlength="8">
                    </div>
                </div>

                <?php if ($role === 'provider'): ?>
                <!-- Provider fields -->
                <div>
                    <label class="form-label">Nombre completo / Nombre del negocio *</label>
                    <input type="text" name="full_name" class="form-input" required
                           value="<?= e($_POST['full_name'] ?? '') ?>"
                           placeholder="Ej: Carlos Rodríguez o Servicio Eléctrico CR">
                </div>

                <div>
                    <label class="form-label">¿Qué ofreces? <span class="text-gray-400 font-normal">(tagline)</span></label>
                    <input type="text" name="tagline" class="form-input"
                           value="<?= e($_POST['tagline'] ?? '') ?>"
                           placeholder="Ej: Electricista certificado con 10 años de experiencia"
                           data-maxlength="150">
                </div>

                <div>
                    <label class="form-label">Categoría *</label>
                    <select name="category_id" class="form-input" required>
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Cascade: Country → City -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">País *</label>
                        <select class="form-input" required
                                x-model="selectedCountry"
                                @change="onCountryChange()">
                            <option value="">Seleccionar país</option>
                            <template x-for="c in countries" :key="c">
                                <option :value="c" x-text="c"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Ciudad / Municipio *</label>
                        <select name="location_id" class="form-input" required
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
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="phone" class="form-input"
                               value="<?= e($_POST['phone'] ?? '') ?>"
                               placeholder="+504 9999 0000">
                    </div>
                    <div>
                        <label class="form-label">WhatsApp</label>
                        <input type="tel" name="whatsapp" class="form-input"
                               value="<?= e($_POST['whatsapp'] ?? '') ?>"
                               placeholder="+504 9999 0000">
                    </div>
                </div>
                <?php endif; ?>

                <div class="flex items-start gap-2 text-sm text-gray-600">
                    <input type="checkbox" id="terms" name="terms" required class="mt-0.5 accent-brand-600">
                    <label for="terms">
                        Acepto los <a href="/terms.php" class="text-brand-600 hover:underline">Términos de Uso</a>
                        y la <a href="/privacy.php" class="text-brand-600 hover:underline">Política de Privacidad</a>
                    </label>
                </div>

                <button type="submit" class="btn-primary w-full py-3.5 text-base">
                    <?= $role === 'provider' ? 'Crear mi perfil gratuito →' : 'Crear cuenta gratis →' ?>
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                ¿Ya tienes cuenta?
                <a href="/login.php" class="text-brand-600 font-semibold hover:underline">Iniciar sesión</a>
            </p>
        </div>

        <!-- Benefits -->
        <?php if ($role === 'provider'): ?>
        <div class="mt-6 grid sm:grid-cols-3 gap-4">
            <?php foreach ([
                ['icon' => '🆓', 'title' => '100% Gratis', 'desc' => 'Sin comisiones ni suscripciones'],
                ['icon' => '🔗', 'title' => 'URL propia', 'desc' => 'Comparte tu perfil en redes sociales'],
                ['icon' => '📊', 'title' => 'Estadísticas', 'desc' => 'Ve cuántos te visitan cada día'],
            ] as $benefit): ?>
            <div class="bg-white rounded-xl p-4 text-center border border-gray-100 shadow-sm">
                <div class="text-2xl mb-2"><?= $benefit['icon'] ?></div>
                <p class="font-semibold text-gray-800 text-sm"><?= e($benefit['title']) ?></p>
                <p class="text-xs text-gray-500 mt-1"><?= e($benefit['desc']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
