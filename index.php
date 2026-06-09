<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Kontactanos - Conecta con profesionales cerca de ti';
$pageDescription = 'Encuentra plomeros, electricistas, médicos, diseñadores y más servicios en tu área. La plataforma que conecta personas con los mejores profesionales locales.';

$categories = getCategories();
$featured   = getFeaturedProviders(8);

// Stats (en producción vendrían de la DB)
$stats = [
    ['value' => '10,000+', 'label' => 'Profesionales activos'],
    ['value' => '50,000+', 'label' => 'Conexiones realizadas'],
    ['value' => '120+',    'label' => 'Ciudades cubiertas'],
    ['value' => '4.9★',   'label' => 'Valoración promedio'],
];

$testimonials = [
    [
        'name'   => 'María González',
        'role'   => 'Emprendedora',
        'city'   => 'Caracas',
        'text'   => 'Encontré un diseñador gráfico increíble en menos de 10 minutos. El perfil era tan completo que sabía exactamente qué esperar. ¡Totalmente recomendado!',
        'rating' => 5,
        'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&h=80&fit=crop&crop=face',
    ],
    [
        'name'   => 'Carlos Mendoza',
        'role'   => 'Electricista certificado',
        'city'   => 'Valencia',
        'text'   => 'Desde que creé mi perfil en Kontactanos mis clientes se triplicaron. El perfil que puedes compartir en WhatsApp e Instagram es una herramienta brutal para conseguir trabajo.',
        'rating' => 5,
        'avatar' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=80&h=80&fit=crop&crop=face',
    ],
    [
        'name'   => 'Luisa Fernández',
        'role'   => 'Abogada',
        'city'   => 'Maracaibo',
        'text'   => 'La plataforma es muy intuitiva. Mis clientes pueden ver mis servicios, reseñas y contactarme directamente. Es como tener una web profesional pero sin complicaciones.',
        'rating' => 5,
        'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=80&h=80&fit=crop&crop=face',
    ],
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO SECTION ===== -->
<section class="relative overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 pt-16 pb-24">
    <!-- Background pattern -->
    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,<svg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'><g fill=\'none\' fill-rule=\'evenodd\'><g fill=\'%23ffffff\' fill-opacity=\'0.4\'><path d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/></g></g></svg>');"></div>

    <!-- Floating elements -->
    <div class="absolute top-10 right-10 w-64 h-64 bg-brand-600 rounded-full opacity-10 blur-3xl animate-pulse-slow"></div>
    <div class="absolute bottom-10 left-10 w-48 h-48 bg-brand-400 rounded-full opacity-10 blur-3xl animate-pulse-slow" style="animation-delay:1.5s"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left: Copy + Search -->
            <div class="text-center lg:text-left">
                <div class="inline-flex items-center gap-2 bg-brand-800/50 border border-brand-600/30 rounded-full px-4 py-2 text-brand-300 text-sm font-medium mb-6">
                    <span class="w-2 h-2 bg-brand-400 rounded-full animate-pulse"></span>
                    +10,000 profesionales verificados
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                    Encuentra el profesional
                    <span class="text-brand-400 relative">
                        perfecto
                        <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 200 8" fill="none">
                            <path d="M2 6C50 2 150 2 198 6" stroke="#4ade80" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </span>
                    cerca de ti
                </h1>

                <p class="text-brand-200 text-lg lg:text-xl mb-8 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    Conectamos personas que necesitan servicios con los mejores profesionales de su ciudad. Rápido, seguro y sin intermediarios.
                </p>

                <!-- Search form -->
                <div class="bg-white rounded-2xl shadow-2xl p-2 max-w-2xl mx-auto lg:mx-0">
                    <form action="/search.php" method="GET" class="flex flex-col sm:flex-row gap-2">
                        <div class="flex items-center gap-3 flex-1 px-3 py-2">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" name="q" placeholder="¿Qué servicio necesitas? Ej: plomero, diseñador..."
                                   class="w-full outline-none text-gray-800 placeholder-gray-400 text-sm sm:text-base">
                        </div>
                        <div class="hidden sm:block w-px bg-gray-200 self-stretch mx-1"></div>
                        <div class="flex items-center gap-3 flex-1 px-3 py-2">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <select name="location" class="w-full outline-none text-gray-600 text-sm sm:text-base bg-transparent">
                                <option value="">Todas las ciudades</option>
                                <?php foreach (getLocations() as $loc): ?>
                                    <option value="<?= e($loc['slug']) ?>"><?= e($loc['city'] . ', ' . $loc['country']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary sm:px-8 py-3 w-full sm:w-auto rounded-xl text-sm sm:text-base font-semibold whitespace-nowrap">
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Quick links -->
                <div class="mt-4 flex flex-wrap gap-2 justify-center lg:justify-start">
                    <span class="text-brand-400 text-sm">Popular:</span>
                    <?php foreach (['Plomero', 'Electricista', 'Diseñador', 'Contador', 'Médico'] as $term): ?>
                        <a href="/search.php?q=<?= urlencode($term) ?>"
                           class="text-sm text-brand-200 hover:text-white border border-brand-700 hover:border-brand-400 rounded-full px-3 py-1 transition-all">
                            <?= e($term) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Visual -->
            <div class="hidden lg:block relative">
                <div class="relative">
                    <!-- Main card -->
                    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=600&h=400&q=80"
                             alt="Profesionales" class="w-full h-56 object-cover">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=48&h=48&fit=crop&crop=face"
                                     alt="Carlos" class="w-12 h-12 rounded-full object-cover border-2 border-brand-200">
                                <div>
                                    <p class="font-bold text-gray-800">Carlos Rodríguez</p>
                                    <p class="text-sm text-gray-500">⚡ Electricista Certificado · Caracas</p>
                                </div>
                                <span class="ml-auto bg-brand-100 text-brand-700 text-xs font-semibold px-3 py-1 rounded-full">Verificado ✓</span>
                            </div>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                    <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-sm text-gray-600 font-medium">5.0 (127 reseñas)</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Instalaciones eléctricas residenciales y comerciales. 15 años de experiencia.</p>
                            <div class="flex gap-2">
                                <button class="flex-1 bg-brand-700 text-white py-2 rounded-xl text-sm font-semibold hover:bg-brand-800 transition-colors">Contactar</button>
                                <button class="px-4 bg-brand-50 text-brand-700 py-2 rounded-xl text-sm font-semibold hover:bg-brand-100 transition-colors">Ver perfil</button>
                            </div>
                        </div>
                    </div>

                    <!-- Floating badge 1 -->
                    <div class="absolute -top-4 -right-4 bg-white rounded-2xl shadow-lg px-4 py-3 flex items-center gap-2">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Conexión exitosa</p>
                            <p class="text-sm font-bold text-gray-800">+50K realizadas</p>
                        </div>
                    </div>

                    <!-- Floating badge 2 -->
                    <div class="absolute -bottom-4 -left-4 bg-brand-700 text-white rounded-2xl shadow-lg px-4 py-3">
                        <p class="text-xs text-brand-200">Disponible ahora</p>
                        <p class="text-sm font-bold">Responde en &lt;1h</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wave bottom -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="w-full h-12 fill-gray-50" style="display:block">
            <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z"/>
        </svg>
    </div>
</section>

<!-- ===== STATS ===== -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php foreach ($stats as $stat): ?>
            <div class="text-center">
                <div class="text-3xl lg:text-4xl font-extrabold text-brand-700 mb-1"><?= e($stat['value']) ?></div>
                <div class="text-sm text-gray-500"><?= e($stat['label']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== CATEGORIES ===== -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-brand-600 font-semibold text-sm uppercase tracking-wider">Explora por categoría</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mt-2 mb-4">¿Qué servicio necesitas hoy?</h2>
            <p class="text-gray-500 text-lg max-w-2xl mx-auto">Más de 12 categorías con miles de profesionales listos para ayudarte</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($categories as $cat): ?>
            <a href="/search.php?category=<?= e($cat['slug']) ?>"
               class="group flex flex-col items-center text-center p-5 rounded-2xl border border-gray-100 hover:border-brand-200 hover:shadow-lg hover:-translate-y-1 transition-all bg-white cursor-pointer">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3 transition-transform group-hover:scale-110"
                     style="background-color: <?= e($cat['color']) ?>20;">
                    <?= getCategoryIconSvg($cat['icon'], $cat['color']) ?>
                </div>
                <span class="text-sm font-semibold text-gray-700 group-hover:text-brand-700 leading-tight">
                    <?= e($cat['name']) ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-8">
            <a href="/search.php" class="btn-outline">Ver todos los servicios →</a>
        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section id="como-funciona" class="py-20 bg-gradient-to-br from-brand-950 to-brand-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5" style="background-image: url('data:image/svg+xml,<svg width=\'30\' height=\'30\' viewBox=\'0 0 30 30\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M0 0h30v30H0z\' fill=\'none\'/><circle cx=\'15\' cy=\'15\' r=\'2\' fill=\'%23ffffff\'/></svg>');"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-brand-300 font-semibold text-sm uppercase tracking-wider">Fácil y rápido</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-2 mb-4">¿Cómo funciona Kontactanos?</h2>
            <p class="text-brand-300 text-lg max-w-2xl mx-auto">En tres simples pasos conectas con el profesional ideal para tu necesidad</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 relative">
            <!-- Connecting line (desktop) -->
            <div class="hidden md:block absolute top-16 left-1/6 right-1/6 h-0.5 bg-gradient-to-r from-brand-600 via-brand-400 to-brand-600 opacity-30"></div>

            <?php
            $steps = [
                [
                    'num'  => '01',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
                    'title'=> 'Busca lo que necesitas',
                    'desc' => 'Escribe el servicio que buscas o explora por categorías. Filtra por ciudad para encontrar profesionales cerca de ti.',
                ],
                [
                    'num'  => '02',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
                    'title'=> 'Elige el profesional ideal',
                    'desc' => 'Revisa perfiles detallados, portafolios, reseñas verificadas y tarifas. Compara varios profesionales antes de decidir.',
                ],
                [
                    'num'  => '03',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                    'title'=> 'Contacta directamente',
                    'desc' => 'Escríbele por WhatsApp, llama o envía un mensaje dentro de la plataforma. Sin intermediarios, sin comisiones ocultas.',
                ],
            ];
            foreach ($steps as $step):
            ?>
            <div class="text-center group">
                <div class="relative inline-flex mb-6">
                    <div class="w-20 h-20 bg-brand-600/20 border-2 border-brand-500/30 rounded-3xl flex items-center justify-center group-hover:bg-brand-500/30 transition-colors mx-auto">
                        <svg class="w-9 h-9 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?= $step['icon'] ?>
                        </svg>
                    </div>
                    <span class="absolute -top-2 -right-2 w-8 h-8 bg-brand-400 text-brand-900 rounded-full flex items-center justify-center text-xs font-black">
                        <?= $step['num'] ?>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3"><?= e($step['title']) ?></h3>
                <p class="text-brand-300 leading-relaxed"><?= e($step['desc']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- CTA for providers -->
        <div class="mt-16 bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 text-center">
            <h3 class="text-2xl font-bold text-white mb-2">¿Ofreces un servicio o producto?</h3>
            <p class="text-brand-300 mb-6">Crea tu perfil gratis, compártelo en redes sociales y empieza a recibir clientes hoy mismo.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/register.php?role=provider" class="btn-primary text-base px-8 py-3">
                    Crear mi perfil gratis →
                </a>
                <a href="/search.php" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl px-8 py-3 font-semibold transition-colors text-base">
                    Explorar servicios
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ===== FEATURED PROVIDERS ===== -->
<?php if (!empty($featured)): ?>
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
            <div>
                <span class="text-brand-600 font-semibold text-sm uppercase tracking-wider">Destacados</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mt-2">Profesionales recomendados</h2>
            </div>
            <a href="/search.php" class="hidden sm:inline-flex text-brand-600 hover:text-brand-800 font-semibold text-sm items-center gap-1 transition-colors">
                Ver todos →
            </a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($featured as $pro): ?>
            <?= renderProviderCard($pro) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== SHARE FEATURE ===== -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-brand-600 font-semibold text-sm uppercase tracking-wider">Perfiles compartibles</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mt-2 mb-4">Tu perfil, tu carta de presentación</h2>
                <p class="text-gray-600 text-lg mb-6 leading-relaxed">
                    Cada profesional tiene una URL única que puede compartir en WhatsApp, Instagram, Facebook y cualquier red social. Cuando alguien hace clic, llega directo a tu perfil en Kontactanos.
                </p>
                <ul class="space-y-4 mb-8">
                    <?php foreach ([
                        ['icon' => '🔗', 'text' => 'URL personalizada: kontactanos.com/p/tu-nombre'],
                        ['icon' => '📱', 'text' => 'Diseñado para verse perfecto en móviles y redes'],
                        ['icon' => '📊', 'text' => 'Ve cuántas personas visitan tu perfil cada día'],
                        ['icon' => '⭐', 'text' => 'Muestra tus reseñas y portafolio de trabajos'],
                    ] as $item): ?>
                    <li class="flex items-start gap-3">
                        <span class="text-2xl flex-shrink-0"><?= $item['icon'] ?></span>
                        <span class="text-gray-700"><?= e($item['text']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="/register.php?role=provider" class="btn-primary text-base px-8 py-3">
                    Crear mi perfil ahora
                </a>
            </div>

            <!-- Share preview mockup -->
            <div class="relative">
                <div class="bg-gray-100 rounded-3xl p-6">
                    <!-- Phone frame -->
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-xs mx-auto">
                        <!-- Profile header -->
                        <div class="bg-gradient-to-br from-brand-700 to-brand-900 px-6 pt-8 pb-16 relative">
                            <div class="absolute top-4 right-4 flex gap-1">
                                <div class="w-2 h-2 bg-white/30 rounded-full"></div>
                                <div class="w-2 h-2 bg-white/30 rounded-full"></div>
                                <div class="w-2 h-2 bg-white/30 rounded-full"></div>
                            </div>
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=80&h=80&fit=crop&crop=face"
                                 alt="" class="w-20 h-20 rounded-full border-4 border-white shadow-lg mx-auto">
                        </div>
                        <div class="px-6 -mt-10 pb-6">
                            <div class="bg-white rounded-2xl p-4 shadow-lg mb-4">
                                <h3 class="text-lg font-bold text-gray-900 text-center">Ana Martínez</h3>
                                <p class="text-brand-600 text-sm text-center mb-2">Diseñadora Gráfica & UX · Caracas</p>
                                <div class="flex justify-center gap-1 mb-3">
                                    <?php for ($i=0;$i<5;$i++): ?><svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg><?php endfor; ?>
                                    <span class="text-xs text-gray-500 ml-1">4.9 (94)</span>
                                </div>
                                <div class="flex gap-2">
                                    <div class="flex-1 bg-brand-700 text-white text-xs py-2 rounded-lg text-center font-semibold">Contactar</div>
                                    <div class="bg-gray-100 text-gray-600 text-xs py-2 px-3 rounded-lg text-center font-semibold">Portfolio</div>
                                </div>
                            </div>
                            <!-- Social share buttons -->
                            <div class="text-center">
                                <p class="text-xs text-gray-400 mb-2">Compartir perfil</p>
                                <div class="flex justify-center gap-2">
                                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </div>
                                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </div>
                                    <div class="w-8 h-8 bg-pink-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0z"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- URL badge -->
                    <div class="mt-4 bg-white rounded-xl px-4 py-3 flex items-center gap-3 shadow-sm">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <span class="text-sm text-gray-600 font-mono">kontactanos.com/p/<strong class="text-brand-700">ana-martinez</strong></span>
                        <button class="ml-auto text-xs text-brand-600 font-semibold hover:text-brand-800">Copiar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-brand-600 font-semibold text-sm uppercase tracking-wider">Lo que dicen</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mt-2 mb-4">Miles de historias de éxito</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach ($testimonials as $t): ?>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-1 mb-4">
                    <?php for ($i=0;$i<$t['rating'];$i++): ?>
                    <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <?php endfor; ?>
                </div>
                <p class="text-gray-700 mb-6 leading-relaxed italic">"<?= e($t['text']) ?>"</p>
                <div class="flex items-center gap-3">
                    <img src="<?= e($t['avatar']) ?>" alt="<?= e($t['name']) ?>" class="w-11 h-11 rounded-full object-cover">
                    <div>
                        <p class="font-semibold text-gray-900 text-sm"><?= e($t['name']) ?></p>
                        <p class="text-gray-500 text-xs"><?= e($t['role']) ?> · <?= e($t['city']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== CTA FINAL ===== -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-gradient-to-br from-brand-700 to-brand-900 rounded-3xl p-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-brand-600 rounded-full opacity-20 -translate-y-1/3 translate-x-1/3"></div>
            <div class="relative">
                <img src="/assets/brand/kontanos-icono.svg" alt="" class="w-16 h-16 mx-auto mb-6 opacity-80">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">
                    ¿Listo para conectar?
                </h2>
                <p class="text-brand-200 text-lg mb-8 max-w-xl mx-auto">
                    Únete a miles de profesionales y clientes que ya se conectan a través de Kontactanos
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/register.php?role=provider" class="bg-white text-brand-700 hover:bg-brand-50 px-8 py-4 rounded-xl font-bold text-base transition-colors shadow-lg">
                        Publicar mi servicio gratis
                    </a>
                    <a href="/search.php" class="bg-brand-600/30 hover:bg-brand-600/50 border border-brand-400/30 text-white px-8 py-4 rounded-xl font-semibold text-base transition-colors">
                        Buscar un profesional
                    </a>
                </div>
                <p class="text-brand-400 text-sm mt-4">Sin comisiones · Sin tarjeta de crédito · Siempre gratis</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
// Helper function para renderizar ícono de categoría
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
    $path = $icons[$icon] ?? $icons['briefcase'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>';
    return '<svg class="w-7 h-7" fill="none" stroke="' . $color . '" viewBox="0 0 24 24">' . $path . '</svg>';
}

function renderProviderCard(array $pro): string {
    $avatar = getAvatar($pro['avatar_url'] ?? null, $pro['full_name'], '200');
    $stars  = renderStars((float)($pro['rating_avg'] ?? 0));
    $loc    = trim(($pro['city'] ?? '') . ', ' . ($pro['country'] ?? ''), ', ');
    $price  = '';
    ob_start();
    ?>
    <a href="/p/<?= e($pro['slug']) ?>" class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
        <div class="relative h-40 bg-gradient-to-br from-brand-800 to-brand-600 overflow-hidden">
            <?php if ($pro['cover_url']): ?>
                <img src="<?= e($pro['cover_url']) ?>" alt="" class="w-full h-full object-cover opacity-60">
            <?php else: ?>
                <div class="absolute inset-0 opacity-20" style="background-image: url('https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=400&h=160&q=50'); background-size:cover;"></div>
            <?php endif; ?>
            <?php if ($pro['is_featured']): ?>
            <span class="absolute top-3 right-3 bg-amber-400 text-amber-900 text-xs font-bold px-2 py-0.5 rounded-full">Destacado</span>
            <?php endif; ?>
            <?php if ($pro['is_verified']): ?>
            <span class="absolute top-3 left-3 bg-white/20 backdrop-blur-sm text-white text-xs font-medium px-2 py-0.5 rounded-full border border-white/30">✓ Verificado</span>
            <?php endif; ?>
        </div>
        <div class="flex flex-col flex-1 p-5 -mt-8 relative">
            <img src="<?= e($avatar) ?>" alt="<?= e($pro['full_name']) ?>"
                 class="w-16 h-16 rounded-2xl object-cover border-4 border-white shadow-md mb-3">
            <h3 class="font-bold text-gray-900 text-base leading-tight mb-1 group-hover:text-brand-700 transition-colors"><?= e($pro['full_name']) ?></h3>
            <?php if ($pro['tagline']): ?>
            <p class="text-gray-500 text-xs mb-2 line-clamp-2"><?= e($pro['tagline']) ?></p>
            <?php endif; ?>
            <div class="flex items-center gap-2 mb-3">
                <?= $stars ?>
                <?php if ($pro['rating_count'] > 0): ?>
                <span class="text-xs text-gray-400">(<?= (int)$pro['rating_count'] ?>)</span>
                <?php endif; ?>
            </div>
            <div class="mt-auto flex items-center justify-between">
                <div class="flex items-center gap-1 text-xs text-gray-500">
                    <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    <?= e($loc ?: 'N/A') ?>
                </div>
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
