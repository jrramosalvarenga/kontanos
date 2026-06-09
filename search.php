<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$q        = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$location = trim($_GET['location'] ?? '');

$filters = [];
if ($q)        $filters['q']        = $q;
if ($category) $filters['category'] = $category;
if ($location) $filters['location'] = $location;

$results    = searchProviders($filters);
$categories = getCategories();

$activeCategory = $category ? DB::fetch("SELECT * FROM categories WHERE slug = $1", [$category]) : null;
$activeLocation = $location ? DB::fetch("SELECT * FROM locations WHERE slug = $1", [$location]) : null;

// Pre-load locations for cascade selector
$allLocations = DB::fetchAll("SELECT id, country, state, city, slug FROM locations WHERE is_active = TRUE ORDER BY country, city");
$locationsByCountry = [];
foreach ($allLocations as $loc) {
    $locationsByCountry[$loc['country']][] = ['slug' => $loc['slug'], 'city' => $loc['city'], 'state' => $loc['state']];
}
uksort($locationsByCountry, function($a, $b) {
    if ($a === 'Honduras') return -1;
    if ($b === 'Honduras') return 1;
    return strcmp($a, $b);
});
$locationsJson = json_encode($locationsByCountry, JSON_UNESCAPED_UNICODE);
$currentCountry = $activeLocation ? $activeLocation['country'] : '';

$pageTitle = 'Buscar Servicios y Profesionales' . ($q ? " - \"$q\"" : '') . ' | Kontactanos';
$pageDescription = 'Encuentra los mejores profesionales y servicios cerca de ti. Plomeros, electricistas, diseñadores, médicos y más.';

require_once __DIR__ . '/includes/header.php';

// Inline renderProviderCard (needed on this page too)
function renderProviderCard(array $pro): string {
    $avatar = getAvatar($pro['avatar_url'] ?? null, $pro['full_name'], '200');
    $stars  = renderStars((float)($pro['rating_avg'] ?? 0));
    $loc    = trim(($pro['city'] ?? '') . ', ' . ($pro['country'] ?? ''), ', ');
    ob_start();
    ?>
    <a href="/p/<?= e($pro['slug']) ?>" class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
        <div class="relative h-32 bg-gradient-to-br from-brand-800 to-brand-600 overflow-hidden">
            <?php if ($pro['cover_url']): ?>
                <img src="<?= e($pro['cover_url']) ?>" alt="" class="w-full h-full object-cover opacity-60">
            <?php endif; ?>
            <?php if ($pro['is_featured']): ?>
            <span class="absolute top-2 right-2 bg-amber-400 text-amber-900 text-xs font-bold px-2 py-0.5 rounded-full">Destacado</span>
            <?php endif; ?>
            <?php if ($pro['is_verified']): ?>
            <span class="absolute top-2 left-2 bg-white/20 backdrop-blur-sm text-white text-xs px-2 py-0.5 rounded-full">✓ Verificado</span>
            <?php endif; ?>
        </div>
        <div class="flex flex-col flex-1 p-4 -mt-6 relative">
            <img src="<?= e($avatar) ?>" alt="<?= e($pro['full_name']) ?>"
                 class="w-14 h-14 rounded-xl object-cover border-4 border-white shadow-md mb-3">
            <h3 class="font-bold text-gray-900 text-sm leading-tight mb-1 group-hover:text-brand-700 transition-colors"><?= e($pro['full_name']) ?></h3>
            <?php if ($pro['tagline']): ?>
            <p class="text-gray-500 text-xs mb-2 line-clamp-2"><?= e($pro['tagline']) ?></p>
            <?php endif; ?>
            <div class="flex items-center gap-1 mb-2"><?= $stars ?>
                <?php if ($pro['rating_count'] > 0): ?><span class="text-xs text-gray-400">(<?= (int)$pro['rating_count'] ?>)</span><?php endif; ?>
            </div>
            <div class="mt-auto flex items-center justify-between">
                <span class="flex items-center gap-1 text-xs text-gray-500">
                    <svg class="w-3 h-3 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    <?= e($loc ?: 'N/A') ?>
                </span>
                <?php if ($pro['category_name']): ?>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background-color: <?= e($pro['category_color'] ?? '#15803d') ?>20; color: <?= e($pro['category_color'] ?? '#15803d') ?>">
                    <?= e($pro['category_name']) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </a>
    <?php
    return ob_get_clean();
}
?>

<!-- Page header -->
<div class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Search form with cascade country→city -->
        <form method="GET" action="/search.php" class="flex flex-col sm:flex-row gap-3 mb-6"
              x-data="{
                  locations: <?= $locationsJson ?>,
                  selectedCountry: '<?= addslashes($currentCountry) ?>',
                  selectedCity: '<?= addslashes($location) ?>',
                  get countries() { return Object.keys(this.locations); },
                  get cities() { return this.selectedCountry ? (this.locations[this.selectedCountry] || []) : []; },
                  onCountryChange() { this.selectedCity = ''; }
              }">
            <div class="flex items-center gap-3 flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus-within:border-brand-400 focus-within:ring-2 focus-within:ring-brand-100 transition-all">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="q" value="<?= e($q) ?>" placeholder="¿Qué servicio necesitas?"
                       class="bg-transparent outline-none w-full text-gray-800 placeholder-gray-400">
            </div>
            <!-- Country filter (no name = not sent as URL param) -->
            <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 focus-within:border-brand-400 transition-all sm:w-44">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                </svg>
                <select class="bg-transparent outline-none w-full text-sm text-gray-700"
                        x-model="selectedCountry"
                        @change="onCountryChange()">
                    <option value="">País</option>
                    <template x-for="c in countries" :key="c">
                        <option :value="c" x-text="c"></option>
                    </template>
                </select>
            </div>
            <!-- City filter -->
            <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 focus-within:border-brand-400 transition-all sm:w-52">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
                <select name="location" class="bg-transparent outline-none w-full text-sm text-gray-700"
                        x-model="selectedCity"
                        :disabled="!selectedCountry">
                    <option value=""><span x-show="!selectedCountry">Ciudad</span><span x-show="selectedCountry">Todas</span></option>
                    <template x-for="city in cities" :key="city.slug">
                        <option :value="city.slug" x-text="city.city"></option>
                    </template>
                </select>
            </div>
            <button type="submit" class="btn-primary px-8 py-3">Buscar</button>
        </form>

        <!-- Category chips -->
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <a href="/search.php<?= $q ? '?q='.urlencode($q) : '' ?>"
               class="filter-chip flex-shrink-0 <?= !$category ? 'active' : '' ?>">
                Todos
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="/search.php?category=<?= e($cat['slug']) ?><?= $q ? '&q='.urlencode($q) : '' ?>"
               class="filter-chip flex-shrink-0 <?= $category === $cat['slug'] ? 'active' : '' ?>">
                <?= e($cat['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Results -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Results header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                <?php if ($q): ?>
                    Resultados para "<span class="text-brand-600"><?= e($q) ?></span>"
                <?php elseif ($activeCategory): ?>
                    <?= e($activeCategory['name']) ?>
                <?php else: ?>
                    Todos los profesionales
                <?php endif; ?>
            </h1>
            <p class="text-gray-500 text-sm mt-0.5">
                <?= count($results) ?> profesional<?= count($results) != 1 ? 'es' : '' ?> encontrado<?= count($results) != 1 ? 's' : '' ?>
                <?= $activeLocation ? 'en ' . e($activeLocation['city']) : '' ?>
            </p>
        </div>

        <!-- Sort -->
        <div class="hidden sm:flex items-center gap-2 text-sm text-gray-600">
            <span>Ordenar:</span>
            <select class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-gray-700 outline-none focus:border-brand-400">
                <option>Mejor valorados</option>
                <option>Más recientes</option>
                <option>Más vistos</option>
            </select>
        </div>
    </div>

    <?php if (empty($results)): ?>
    <!-- Empty state -->
    <div class="text-center py-20">
        <div class="w-20 h-20 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">No encontramos resultados</h3>
        <p class="text-gray-500 mb-6 max-w-md mx-auto">
            No hay profesionales que coincidan con tu búsqueda en este momento.
            Prueba con otros términos o amplía el área de búsqueda.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/search.php" class="btn-outline">Ver todos los profesionales</a>
            <a href="/register.php?role=provider" class="btn-primary">¿Eres profesional? Regístrate</a>
        </div>
    </div>
    <?php else: ?>
    <!-- Grid -->
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        <?php foreach ($results as $pro): ?>
        <?= renderProviderCard($pro) ?>
        <?php endforeach; ?>
    </div>

    <!-- CTA for providers -->
    <div class="mt-12 bg-gradient-to-r from-brand-700 to-brand-600 rounded-2xl p-8 text-center text-white">
        <h3 class="text-xl font-bold mb-2">¿Ofreces este servicio?</h3>
        <p class="text-brand-200 mb-4 text-sm">Crea tu perfil gratis y aparece en los resultados de búsqueda</p>
        <a href="/register.php?role=provider" class="bg-white text-brand-700 hover:bg-brand-50 px-6 py-2.5 rounded-xl font-semibold text-sm transition-colors inline-block">
            Publicar mi servicio gratis →
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
