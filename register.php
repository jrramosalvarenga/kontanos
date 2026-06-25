<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$role        = in_array($_GET['role'] ?? '', ['provider', 'client']) ? $_GET['role'] : 'provider';
$profileType = in_array($_POST['profile_type'] ?? 'personal', ['personal', 'business']) ? ($_POST['profile_type'] ?? 'personal') : 'personal';
$errors      = [];
$refCode     = trim($_POST['ref_code'] ?? $_GET['ref'] ?? '');

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

$allLocations = DB::fetchAll("SELECT id, country, state, city FROM locations WHERE is_active = TRUE ORDER BY country, city");
$locationsByCountry = [];
foreach ($allLocations as $loc) {
    $locationsByCountry[$loc['country']][] = [
        'id'   => (int)$loc['id'],
        'city' => $loc['city'],
        'state'=> $loc['state'],
    ];
}
uksort($locationsByCountry, function($a, $b) {
    if ($a === 'Honduras') return -1;
    if ($b === 'Honduras') return 1;
    return strcmp($a, $b);
});
$locationsJson = json_encode($locationsByCountry, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

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

$appShell  = true;
$pageTitle = 'Crear cuenta gratuita | Kontactanos';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Locations data (read by Alpine.js) -->
<script id="locations-data" type="application/json"><?= $locationsJson ?></script>

<style>
[x-cloak] { display: none !important; }
</style>

<div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ── Left panel (desktop) ── -->
    <div class="hidden lg:flex flex-col justify-between bg-brand-800 text-white w-[400px] xl:w-[440px] flex-shrink-0 p-10">
        <div>
            <a href="/">
                <img src="/assets/brand/kontanos-logo-blanco.svg" alt="Kontactanos" class="h-9 mb-10">
            </a>
            <h2 class="text-3xl font-extrabold leading-tight mb-4">
                Tu próximo cliente<br>te está buscando ahora
            </h2>
            <p class="text-brand-200 mb-8 text-sm leading-relaxed">
                Crea tu perfil gratis en menos de 2 minutos y empieza a recibir solicitudes directas en tu área.
            </p>

            <ul class="space-y-4 mb-10">
                <?php foreach ([
                    ['icon' => '⚡', 'text' => 'Perfil activo en minutos, sin papeleo'],
                    ['icon' => '🎯', 'text' => 'Clientes que buscan exactamente lo que ofreces'],
                    ['icon' => '💬', 'text' => 'Contacto directo por WhatsApp o teléfono'],
                    ['icon' => '🆓', 'text' => '100% gratis · Sin comisiones · Para siempre'],
                ] as $b): ?>
                <li class="flex items-start gap-3">
                    <span class="text-lg leading-none mt-0.5"><?= $b['icon'] ?></span>
                    <span class="text-brand-100 text-sm leading-snug"><?= e($b['text']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="bg-white/10 rounded-2xl p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex -space-x-2">
                        <?php foreach (['#4ade80','#34d399','#6ee7b7','#86efac'] as $color): ?>
                        <div class="w-8 h-8 rounded-full border-2 border-brand-700 flex-shrink-0" style="background:<?= $color ?>"></div>
                        <?php endforeach; ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">+500 profesionales activos</p>
                        <p class="text-xs text-brand-300">en Honduras y Centroamérica</p>
                    </div>
                </div>
                <p class="text-brand-200 text-sm italic leading-relaxed">
                    "En la primera semana ya tenía 3 clientes nuevos. Nunca pensé que fuera tan fácil."
                </p>
                <p class="text-xs text-brand-400 mt-2">— Carlos M., Electricista · Tegucigalpa</p>
            </div>
        </div>

        <p class="text-xs text-brand-400 mt-8">
            ¿Ya tienes cuenta?
            <a href="/login.php" class="text-brand-200 font-semibold hover:text-white underline">Inicia sesión</a>
        </p>
    </div>

    <!-- ── Right panel ── -->
    <div class="flex-1 flex flex-col items-center justify-center py-10 px-4 bg-gray-50">

        <!-- Mobile logo -->
        <a href="/" class="lg:hidden mb-5">
            <img src="/assets/brand/kontanos-logo-color.svg" alt="Kontactanos" class="h-9 mx-auto">
        </a>

        <div class="w-full max-w-[420px]">

            <!-- Role tabs -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-1.5 mb-4 grid grid-cols-2 gap-1.5">
                <a href="/register.php?role=provider"
                   class="rounded-xl py-2.5 text-center font-semibold text-sm transition-all <?= $role === 'provider' ? 'bg-brand-700 text-white shadow' : 'text-gray-500 hover:bg-gray-50' ?>">
                    Ofrecer Servicios
                </a>
                <a href="/register.php?role=client"
                   class="rounded-xl py-2.5 text-center font-semibold text-sm transition-all <?= $role === 'client' ? 'bg-brand-700 text-white shadow' : 'text-gray-500 hover:bg-gray-50' ?>">
                    Buscar Servicios
                </a>
            </div>

            <!-- Alpine component -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                 x-data="registerForm()"
                 x-cloak>

                <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-b border-red-200 text-red-700 px-6 py-3 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <?= e($errors[0]) ?>
                </div>
                <?php endif; ?>

                <?php if ($role === 'provider'): ?>
                <!-- Progress bar -->
                <div class="h-1 bg-gray-100">
                    <div class="h-full bg-brand-500 transition-all duration-500"
                         :style="'width:' + (currentStep === 1 ? '50%' : '100%')"></div>
                </div>
                <?php endif; ?>

                <div class="p-7">

                    <?php if ($role === 'provider'): ?>
                    <!-- Step label -->
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <p class="text-xs font-semibold text-brand-600 uppercase tracking-wide"
                               x-text="currentStep === 1 ? 'Paso 1 de 2' : 'Paso 2 de 2'"></p>
                            <h2 class="text-xl font-extrabold text-gray-900 mt-0.5"
                                x-text="currentStep === 1 ? 'Crea tu acceso' : 'Completa tu perfil'"></h2>
                        </div>
                        <div class="flex gap-1.5">
                            <div class="w-2 h-2 rounded-full transition-colors"
                                 :class="currentStep >= 1 ? 'bg-brand-600' : 'bg-gray-200'"></div>
                            <div class="w-2 h-2 rounded-full transition-colors"
                                 :class="currentStep >= 2 ? 'bg-brand-600' : 'bg-gray-200'"></div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="mb-5">
                        <h2 class="text-xl font-extrabold text-gray-900">Encontrá el profesional ideal</h2>
                        <p class="text-sm text-gray-500 mt-1">Gratis · Rápido · Sin tarjeta</p>
                    </div>
                    <?php endif; ?>

                    <!-- OAuth — solo visible en paso 1 -->
                    <div x-show="currentStep === 1">
                        <?php if (GOOGLE_CLIENT_ID): ?>
                        <a href="/oauth/google.php?role=<?= urlencode($role) ?>&ref=<?= urlencode($refCode) ?>"
                           class="flex items-center gap-3 w-full border-2 border-gray-200 hover:border-brand-400 bg-white rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 transition-all mb-3">
                            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Continuar con Google
                            <span class="ml-auto text-[10px] bg-brand-50 text-brand-700 font-bold px-2 py-0.5 rounded-full">Más rápido</span>
                        </a>
                        <?php endif; ?>
                        <?php if (FACEBOOK_APP_ID): ?>
                        <a href="/oauth/facebook.php?role=<?= urlencode($role) ?>&ref=<?= urlencode($refCode) ?>"
                           class="flex items-center gap-3 w-full bg-[#1877F2] hover:bg-[#1464d0] rounded-xl py-3 px-4 text-sm font-semibold text-white transition-all mb-3">
                            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="white">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Continuar con Facebook
                        </a>
                        <?php endif; ?>
                        <?php if (GOOGLE_CLIENT_ID || FACEBOOK_APP_ID): ?>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex-1 h-px bg-gray-200"></div>
                            <span class="text-xs text-gray-400 font-medium">o con tu email</span>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="/register.php" id="register-form" class="space-y-4">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="role" value="<?= e($role) ?>">
                        <input type="hidden" name="ref_code" value="<?= e($refCode) ?>">
                        <input type="hidden" name="profile_type" :value="profileType">

                        <!-- PASO 1: email + contraseña + nombre -->
                        <div id="step1-fields" x-show="currentStep === 1">
                            <div class="space-y-4">
                                <div>
                                    <label class="form-label">Tu nombre completo *</label>
                                    <input type="text" name="full_name" class="form-input" required
                                           value="<?= e($_POST['full_name'] ?? '') ?>"
                                           placeholder="Ej: Carlos Rodríguez"
                                           autocomplete="name">
                                </div>
                                <div>
                                    <label class="form-label">Correo electrónico *</label>
                                    <input type="email" name="email" class="form-input" required
                                           value="<?= e($_POST['email'] ?? '') ?>"
                                           placeholder="tucorreo@gmail.com"
                                           autocomplete="email">
                                </div>
                                <div>
                                    <label class="form-label">Contraseña *</label>
                                    <input type="password" name="password" class="form-input" required
                                           placeholder="Mínimo 8 caracteres" minlength="8"
                                           autocomplete="new-password">
                                </div>
                            </div>

                            <div class="flex items-center justify-center gap-5 mt-5 text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                    Datos seguros
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Sin spam
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                    2 minutos
                                </span>
                            </div>

                            <?php if ($role === 'provider'): ?>
                            <button type="button" @click="nextStep()"
                                    class="btn-primary w-full py-3.5 text-base mt-5">
                                Continuar →
                            </button>
                            <?php else: ?>
                            <div class="mt-5 space-y-4">
                                <div class="flex items-start gap-2 text-sm text-gray-600">
                                    <input type="checkbox" id="terms" name="terms" required class="mt-0.5 accent-brand-600">
                                    <label for="terms">
                                        Acepto los <a href="/terms.php" class="text-brand-600 hover:underline">Términos de Uso</a>
                                        y la <a href="/privacy.php" class="text-brand-600 hover:underline">Política de Privacidad</a>
                                    </label>
                                </div>
                                <button type="submit" class="btn-primary w-full py-3.5 text-base">
                                    Crear cuenta gratis →
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($role === 'provider'): ?>
                        <!-- PASO 2: detalles del perfil -->
                        <div id="step2-fields" x-show="currentStep === 2" class="space-y-4">

                            <div>
                                <label class="form-label">¿Cómo te registras?</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="profileType = 'personal'"
                                            :class="profileType === 'personal' ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-gray-600 border-gray-200 hover:border-brand-300'"
                                            class="border-2 rounded-xl py-3 px-3 text-sm font-semibold transition-all text-left flex items-center gap-2">
                                        <span class="text-lg">👤</span>
                                        <span>
                                            <span class="block">Independiente</span>
                                            <span class="text-[11px] font-normal opacity-75">Freelancer, técnico…</span>
                                        </span>
                                    </button>
                                    <button type="button" @click="profileType = 'business'"
                                            :class="profileType === 'business' ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-gray-600 border-gray-200 hover:border-brand-300'"
                                            class="border-2 rounded-xl py-3 px-3 text-sm font-semibold transition-all text-left flex items-center gap-2">
                                        <span class="text-lg">🏢</span>
                                        <span>
                                            <span class="block">Negocio</span>
                                            <span class="text-[11px] font-normal opacity-75">Empresa, taller…</span>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div x-show="profileType === 'business'">
                                <label class="form-label">Nombre del negocio *</label>
                                <input type="text" name="business_name" class="form-input"
                                       :required="profileType === 'business'"
                                       value="<?= e($_POST['business_name'] ?? '') ?>"
                                       placeholder="Ej: Taller Mecánico López">
                            </div>

                            <div>
                                <label class="form-label">¿Qué servicio ofreces?</label>
                                <input type="text" name="tagline" class="form-input"
                                       value="<?= e($_POST['tagline'] ?? '') ?>"
                                       placeholder="Ej: Electricista con 10 años de experiencia">
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

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label">País *</label>
                                    <select class="form-input" required x-model="selectedCountry" @change="onCountryChange()">
                                        <option value="">País</option>
                                        <template x-for="c in countries" :key="c">
                                            <option :value="c" x-text="c"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Ciudad *</label>
                                    <select name="location_id" class="form-input" required
                                            x-model="selectedCity" :disabled="!selectedCountry">
                                        <option value="">Ciudad</option>
                                        <template x-for="city in cities" :key="city.id">
                                            <option :value="city.id" x-text="city.city + (city.state ? ', ' + city.state : '')"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" name="phone" class="form-input"
                                           value="<?= e($_POST['phone'] ?? '') ?>"
                                           placeholder="+504 9999-0000">
                                </div>
                                <div>
                                    <label class="form-label">WhatsApp</label>
                                    <input type="tel" name="whatsapp" class="form-input"
                                           value="<?= e($_POST['whatsapp'] ?? '') ?>"
                                           placeholder="+504 9999-0000">
                                </div>
                            </div>

                            <div class="flex items-start gap-2 text-sm text-gray-600">
                                <input type="checkbox" id="terms2" name="terms" required class="mt-0.5 accent-brand-600">
                                <label for="terms2">
                                    Acepto los <a href="/terms.php" class="text-brand-600 hover:underline">Términos de Uso</a>
                                    y la <a href="/privacy.php" class="text-brand-600 hover:underline">Política de Privacidad</a>
                                </label>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" @click="currentStep = 1"
                                        class="flex-shrink-0 px-5 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                                    ← Atrás
                                </button>
                                <button type="submit" class="btn-primary flex-1 py-3 text-base">
                                    Crear mi perfil gratis →
                                </button>
                            </div>

                            <p class="text-center text-xs text-gray-400">
                                Sin tarjeta de crédito · Cancela cuando quieras
                            </p>
                        </div>
                        <?php endif; ?>

                    </form>
                </div>
            </div>

            <p class="text-center text-sm text-gray-500 mt-5 lg:hidden">
                ¿Ya tienes cuenta?
                <a href="/login.php" class="text-brand-600 font-semibold hover:underline">Iniciar sesión</a>
            </p>

            <?php if ($role === 'provider'): ?>
            <div class="mt-5 grid grid-cols-3 gap-3 lg:hidden">
                <?php foreach ([
                    ['icon' => '🆓', 'title' => '100% Gratis', 'desc' => 'Sin comisiones'],
                    ['icon' => '🔗', 'title' => 'URL propia',  'desc' => 'Comparte en redes'],
                    ['icon' => '📊', 'title' => 'Estadísticas','desc' => 'Ve tus visitas'],
                ] as $b): ?>
                <div class="bg-white rounded-xl p-3 text-center border border-gray-100 shadow-sm">
                    <div class="text-xl mb-1"><?= $b['icon'] ?></div>
                    <p class="font-semibold text-gray-800 text-xs"><?= e($b['title']) ?></p>
                    <p class="text-[11px] text-gray-500 mt-0.5"><?= e($b['desc']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('registerForm', () => ({
        currentStep: <?= (!empty($errors) && $role === 'provider') ? 2 : 1 ?>,
        profileType: <?= json_encode($profileType) ?>,
        locations: JSON.parse(document.getElementById('locations-data').textContent),
        selectedCountry: <?= json_encode($selectedCountry) ?>,
        selectedCity: <?= $selectedLocationId ?: 'null' ?>,

        get countries() { return Object.keys(this.locations); },
        get cities() {
            return this.selectedCountry ? (this.locations[this.selectedCountry] || []) : [];
        },
        onCountryChange() { this.selectedCity = null; },

        nextStep() {
            const fields = document.querySelectorAll('#step1-fields [required]');
            for (const f of fields) {
                if (!f.reportValidity()) return;
            }
            this.currentStep = 2;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }));
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
