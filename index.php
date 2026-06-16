<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Kontactanos - Directorio de profesionales y servicios locales';
$pageDescription = 'Encuentra plomeros, electricistas, médicos, diseñadores y más servicios en tu área. El directorio de profesionales locales más completo.';

$categories = getCategories();
$featured   = getFeaturedProviders(8);
$homeBannerAds = getActiveAds('home_banner');

$cityOptions = array_map(fn($l) => [
    'slug'  => $l['slug'],
    'label' => $l['city'] . ', ' . $l['country'],
], getLocations());
$cityOptionsJson = json_encode($cityOptions, JSON_UNESCAPED_UNICODE);

$popularTerms = ['Plomero', 'Electricista', 'Diseñador', 'Contador', 'Médico', 'Abogado', 'Mecánico'];

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="relative bg-brand-950 overflow-hidden">
    <!-- Fondo sutil -->
    <div class="absolute inset-0 opacity-20"
         style="background-image: url('data:image/svg+xml,<svg width=\'40\' height=\'40\' viewBox=\'0 0 40 40\' xmlns=\'http://www.w3.org/2000/svg\'><circle cx=\'20\' cy=\'20\' r=\'1.5\' fill=\'%23ffffff\'/></svg>');"></div>
    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-gray-50 to-transparent"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-28 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 bg-brand-800/60 border border-brand-600/30 rounded-full px-4 py-1.5 text-brand-300 text-xs font-medium mb-6">
            <span class="w-1.5 h-1.5 bg-brand-400 rounded-full animate-pulse"></span>
            Directorio de profesionales locales
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-4">
            Encuentra el profesional<br class="hidden sm:block">
            <span class="text-brand-400">que necesitas</span>
        </h1>
        <p class="text-brand-300 text-lg mb-10 max-w-xl mx-auto">
            Miles de profesionales verificados en tu ciudad, listos para ayudarte.
        </p>

        <!-- Search card -->
        <div class="bg-white rounded-2xl shadow-2xl p-2 max-w-3xl mx-auto"
             x-data="{
                query: '',
                selectedSlug: '',
                open: false,
                locations: <?= e($cityOptionsJson) ?>,
                norm(s) { return (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); },
                get filtered() {
                    if (!this.query) return this.locations.slice(0, 8);
                    const q = this.norm(this.query);
                    return this.locations.filter(l => this.norm(l.label).includes(q)).slice(0, 8);
                },
                select(loc) { this.query = loc.label; this.selectedSlug = loc.slug; this.open = false; }
             }"
             @click.outside="open = false">
            <form action="/search.php" method="GET" class="flex flex-col sm:flex-row gap-2">
                <!-- Qué -->
                <div class="flex items-center gap-3 flex-1 px-4 py-3">
                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q"
                           placeholder="¿Qué servicio necesitas?"
                           class="w-full outline-none text-gray-800 placeholder-gray-400 text-base bg-transparent">
                </div>

                <div class="hidden sm:block w-px bg-gray-200 self-stretch"></div>

                <!-- Dónde -->
                <div class="flex items-center gap-3 flex-1 px-4 py-3 relative">
                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <input type="text" x-model="query" @focus="open = true" @input="selectedSlug = ''"
                           placeholder="¿En qué ciudad?"
                           autocomplete="off"
                           class="w-full outline-none text-gray-800 placeholder-gray-400 text-base bg-transparent">
                    <input type="hidden" name="location" :value="selectedSlug">

                    <div x-show="open && filtered.length" x-cloak
                         class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-100 max-h-56 overflow-y-auto z-30 text-left">
                        <template x-for="loc in filtered" :key="loc.slug">
                            <button type="button" @click="select(loc)"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700 transition-colors"
                                    x-text="loc.label"></button>
                        </template>
                    </div>
                </div>

                <button type="submit"
                        class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-8 py-3 rounded-xl transition-colors sm:rounded-xl text-base whitespace-nowrap">
                    Buscar
                </button>
            </form>
        </div>

        <!-- Búsquedas populares -->
        <div class="mt-5 flex flex-wrap gap-2 justify-center">
            <span class="text-brand-400 text-sm">Popular:</span>
            <?php foreach ($popularTerms as $term): ?>
            <a href="/search.php?q=<?= urlencode($term) ?>"
               class="text-sm text-brand-200 hover:text-white border border-brand-700/60 hover:border-brand-400 rounded-full px-3 py-1 transition-all">
                <?= e($term) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== CATEGORÍAS ===== -->
<section class="py-14 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900">Explora por categoría</h2>
                <p class="text-gray-500 text-sm mt-0.5">Encuentra el servicio que necesitas</p>
            </div>
            <a href="/search.php" class="hidden sm:inline text-sm text-brand-600 hover:text-brand-800 font-semibold transition-colors">
                Ver todos →
            </a>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 sm:gap-4">
            <?php foreach ($categories as $cat): ?>
            <a href="/search.php?category=<?= e($cat['slug']) ?>"
               class="group flex flex-col items-center text-center p-4 sm:p-5 rounded-2xl bg-white border border-gray-100 hover:border-brand-200 hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform"
                     style="background-color: <?= e($cat['color']) ?>18;">
                    <?= getCategoryIconSvg($cat['icon'], $cat['color']) ?>
                </div>
                <span class="text-xs font-semibold text-gray-700 group-hover:text-brand-700 leading-tight">
                    <?= e($cat['name']) ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-6 sm:hidden">
            <a href="/search.php" class="text-sm text-brand-600 font-semibold hover:text-brand-800">Ver todos los servicios →</a>
        </div>
    </div>
</section>

<!-- ===== AD BANNER ===== -->
<?php if (!empty($homeBannerAds)): ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
    <?= renderAdBanner($homeBannerAds[0]) ?>
</div>
<?php endif; ?>

<!-- ===== PROFESIONALES DESTACADOS ===== -->
<?php if (!empty($featured)): ?>
<section class="py-14 bg-white border-t border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900">Profesionales recomendados</h2>
                <p class="text-gray-500 text-sm mt-0.5">Verificados y con reseñas de clientes</p>
            </div>
            <a href="/search.php" class="hidden sm:inline text-sm text-brand-600 hover:text-brand-800 font-semibold transition-colors">
                Ver todos →
            </a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($featured as $pro): ?>
            <?= renderProviderCard($pro) ?>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-8">
            <a href="/search.php"
               class="inline-flex items-center gap-2 border border-gray-200 hover:border-brand-300 hover:bg-brand-50 text-gray-700 hover:text-brand-700 font-semibold px-8 py-3 rounded-xl transition-all text-sm">
                Ver todos los profesionales
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== CTA REGISTRO ===== -->
<section class="py-14 bg-gray-50 border-t border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-4">
            <!-- Para proveedores -->
            <div class="bg-brand-700 rounded-2xl p-8 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 bg-brand-600 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-white mb-2">¿Ofreces un servicio?</h3>
                    <p class="text-brand-200 text-sm leading-relaxed mb-6">
                        Crea tu perfil gratis, compártelo en tus redes y empieza a recibir clientes hoy mismo. Sin comisiones.
                    </p>
                    <ul class="space-y-2 mb-6">
                        <?php foreach (['Perfil con URL única compartible', 'Contacto directo con clientes', 'Visible en búsquedas locales'] as $b): ?>
                        <li class="flex items-center gap-2 text-brand-200 text-sm">
                            <svg class="w-4 h-4 text-brand-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <?= e($b) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="/register.php?role=provider"
                   class="block text-center bg-white text-brand-700 hover:bg-brand-50 font-bold px-6 py-3 rounded-xl transition-colors text-sm">
                    Publicar mi servicio gratis →
                </a>
            </div>

            <!-- Para clientes -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 bg-brand-50 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-gray-900 mb-2">¿Necesitas un profesional?</h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-6">
                        Busca entre miles de profesionales en tu ciudad. Lee reseñas, compara perfiles y contáctalos directamente.
                    </p>
                    <ul class="space-y-2 mb-6">
                        <?php foreach (['Perfiles con reseñas verificadas', 'Búsqueda por ciudad y categoría', 'Contacto directo por WhatsApp o email'] as $b): ?>
                        <li class="flex items-center gap-2 text-gray-500 text-sm">
                            <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <?= e($b) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="/search.php"
                   class="block text-center bg-brand-700 hover:bg-brand-800 text-white font-bold px-6 py-3 rounded-xl transition-colors text-sm">
                    Buscar profesionales →
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
function getCategoryIconSvg(string $icon, string $color): string {
    $icons = [
        'home'             => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'computer-desktop' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
        'heart'            => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
        'academic-cap'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>',
        'scale'            => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>',
        'musical-note'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>',
        'sparkles'         => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
        'truck'            => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1h8zm0 0l1-5h4l2 5m-7 0h7"/>',
        'wrench'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'paint-brush'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
        'cake'             => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"/>',
        'paw'              => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
    ];
    $path = $icons[$icon] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>';
    return '<svg class="w-6 h-6" fill="none" stroke="' . $color . '" viewBox="0 0 24 24">' . $path . '</svg>';
}
?>
