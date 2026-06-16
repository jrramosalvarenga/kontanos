<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Kontactanos - Conecta con profesionales cerca de ti';
$pageDescription = 'Publica tu servicio gratis o encuentra el profesional que necesitas en tu ciudad.';

$categories = getCategories();

$cityOptions = array_map(fn($l) => [
    'slug'  => $l['slug'],
    'label' => $l['city'] . ', ' . $l['country'],
], getLocations());
$cityOptionsJson = json_encode($cityOptions, JSON_UNESCAPED_UNICODE);

$homeBannerAds = getActiveAds('home_banner');

require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO SPLIT -->
<section class="lg:min-h-[calc(100vh-112px)] grid lg:grid-cols-5">

    <!-- LEFT: REGISTER (primary) -->
    <div class="lg:col-span-3 bg-brand-950 flex items-center justify-center px-8 py-20 lg:py-0">
        <div class="max-w-lg w-full">
            <div class="inline-flex items-center gap-2 bg-brand-800/50 border border-brand-700/40 rounded-full px-3 py-1.5 text-brand-300 text-xs font-medium mb-8">
                <span class="w-1.5 h-1.5 bg-brand-400 rounded-full animate-pulse"></span>
                Gratis · Sin comisiones · En minutos
            </div>

            <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight mb-5">
                Publica tu servicio.<br>
                <span class="text-brand-400">Consigue más clientes.</span>
            </h1>

            <p class="text-brand-300 text-lg mb-8 leading-relaxed">
                Crea tu perfil profesional, compártelo en WhatsApp e Instagram y empieza a recibir contactos hoy mismo.
            </p>

            <div class="space-y-3 mb-10">
                <?php foreach ([
                    'URL única para compartir en redes sociales',
                    'Contacto directo — sin intermediarios ni comisiones',
                    'Visible para miles de personas en tu ciudad',
                ] as $benefit): ?>
                <div class="flex items-center gap-3 text-brand-200 text-sm">
                    <div class="w-5 h-5 bg-brand-600/30 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <?= e($benefit) ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="/register.php?role=provider"
                   class="flex-1 sm:flex-none bg-brand-500 hover:bg-brand-400 text-white font-bold text-base px-8 py-4 rounded-xl transition-colors text-center">
                    Crear mi perfil gratis →
                </a>
                <a href="/login.php"
                   class="flex-1 sm:flex-none border border-brand-800 hover:border-brand-600 text-brand-400 hover:text-brand-200 font-medium text-base px-8 py-4 rounded-xl transition-colors text-center">
                    Ya tengo cuenta
                </a>
            </div>

            <p class="text-brand-700 text-xs mt-5">
                +10,000 profesionales ya tienen su perfil
            </p>
        </div>
    </div>

    <!-- RIGHT: SEARCH -->
    <div class="lg:col-span-2 bg-white flex items-center justify-center px-8 py-16 lg:py-0 border-t lg:border-t-0 lg:border-l border-gray-100">
        <div class="max-w-sm w-full">
            <h2 class="text-2xl font-bold text-gray-900 mb-1">¿Buscas un servicio?</h2>
            <p class="text-gray-500 text-sm mb-7">Encuentra profesionales cerca de ti</p>

            <form action="/search.php" method="GET" class="space-y-3 mb-8">
                <div class="flex items-center gap-3 border border-gray-200 rounded-xl px-4 py-3 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-100 transition-all bg-gray-50">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" placeholder="Ej: plomero, diseñador, médico..."
                           class="w-full outline-none text-gray-800 placeholder-gray-400 text-sm bg-transparent">
                </div>

                <div class="relative"
                     x-data="{
                        query: '',
                        selectedSlug: '',
                        open: false,
                        locations: <?= e($cityOptionsJson) ?>,
                        norm(s) { return (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); },
                        get filtered() {
                            if (!this.query) return this.locations.slice(0, 6);
                            const q = this.norm(this.query);
                            return this.locations.filter(l => this.norm(l.label).includes(q)).slice(0, 6);
                        },
                        select(loc) { this.query = loc.label; this.selectedSlug = loc.slug; this.open = false; }
                     }"
                     @click.outside="open = false">
                    <div class="flex items-center gap-3 border border-gray-200 rounded-xl px-4 py-3 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-100 transition-all bg-gray-50">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <input type="text" x-model="query" @focus="open = true" @input="selectedSlug = ''"
                               placeholder="¿En qué ciudad?" autocomplete="off"
                               class="w-full outline-none text-gray-800 placeholder-gray-400 text-sm bg-transparent">
                        <input type="hidden" name="location" :value="selectedSlug">
                    </div>
                    <div x-show="open && filtered.length" x-cloak
                         class="absolute top-full left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-gray-100 max-h-48 overflow-y-auto z-20">
                        <template x-for="loc in filtered" :key="loc.slug">
                            <button type="button" @click="select(loc)"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700 transition-colors"
                                    x-text="loc.label"></button>
                        </template>
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold py-3 rounded-xl transition-colors">
                    Buscar profesionales
                </button>
            </form>

            <!-- Quick categories -->
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-3">Categorías</p>
                <div class="flex flex-wrap gap-2">
                    <?php foreach (array_slice($categories, 0, 8) as $cat): ?>
                    <a href="/search.php?category=<?= e($cat['slug']) ?>"
                       class="text-xs text-gray-600 hover:text-brand-700 border border-gray-200 hover:border-brand-300 hover:bg-brand-50 bg-gray-50 rounded-full px-3 py-1.5 transition-all">
                        <?= e($cat['name']) ?>
                    </a>
                    <?php endforeach; ?>
                    <a href="/search.php"
                       class="text-xs text-brand-600 hover:text-brand-800 font-semibold px-3 py-1.5 transition-colors">
                        Ver todos →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES GRID -->
<section class="py-14 bg-gray-50 border-t border-gray-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-7">
            <h2 class="text-lg font-bold text-gray-900">Todos los servicios</h2>
            <a href="/search.php" class="text-sm text-brand-600 hover:text-brand-800 font-medium transition-colors">Ver todos →</a>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            <?php foreach ($categories as $cat): ?>
            <a href="/search.php?category=<?= e($cat['slug']) ?>"
               class="group flex flex-col items-center text-center p-4 rounded-xl border border-gray-100 bg-white hover:border-brand-200 hover:shadow-md transition-all cursor-pointer">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-2.5 group-hover:scale-110 transition-transform"
                     style="background-color: <?= e($cat['color']) ?>18;">
                    <?= getCategoryIconSvg($cat['icon'], $cat['color']) ?>
                </div>
                <span class="text-xs font-medium text-gray-700 group-hover:text-brand-700 leading-tight">
                    <?= e($cat['name']) ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($homeBannerAds)): ?>
<section class="py-8 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <?= renderAdBanner($homeBannerAds[0]) ?>
    </div>
</section>
<?php endif; ?>

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
    return '<svg class="w-5 h-5" fill="none" stroke="' . $color . '" viewBox="0 0 24 24">' . $path . '</svg>';
}
?>
