<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '', '/');
$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));

if (!$slug) {
    header('Location: /search.php');
    exit;
}

$provider = getProviderBySlug($slug);
if (!$provider) {
    http_response_code(404);
    $pageTitle = '404 - Perfil no encontrado | Kontactanos';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="min-h-screen flex items-center justify-center text-center py-20 px-4">
        <div>
            <div class="text-8xl mb-4">🔍</div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Perfil no encontrado</h1>
            <p class="text-gray-500 mb-8">El profesional que buscas no existe o fue eliminado.</p>
            <a href="/search.php" class="btn-primary">Explorar profesionales</a>
        </div>
    </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Increment views (throttled by session)
$viewKey = 'viewed_' . $provider['id'];
if (empty($_SESSION[$viewKey])) {
    incrementProfileViews($provider['id']);
    $_SESSION[$viewKey] = time();
}

$services  = getProviderServices($provider['id']);
$reviews   = getProviderReviews($provider['id']);
$portfolio = getProviderPortfolio($provider['id']);

// Get tags
$tags = DB::fetchAll("
    SELECT t.name FROM tags t
    JOIN provider_tags pt ON pt.tag_id = t.id
    WHERE pt.provider_id = $1
", [$provider['id']]);

$avatar    = getAvatar($provider['avatar_url'], $provider['full_name'], '400');
$profileUrl = APP_URL . '/p/' . $provider['slug'];
$loc       = trim(($provider['city'] ?? '') . ', ' . ($provider['country'] ?? ''), ', ');

// OG tags for social sharing
$service         = $provider['tagline'] ?: $provider['category_name'] ?: 'Servicios profesionales';
$pageTitle       = $provider['full_name'] . ' - ' . $service . ' | Kontactanos';
$pageDescription = $service . ($loc ? " en $loc" : '') . '. ' .
    ($provider['bio'] ? mb_substr(strip_tags($provider['bio']), 0, 120) : 'Contáctalo en Kontactanos.');
$pageImage       = $provider['avatar_url'] ?? getAvatar(null, $provider['full_name'], '600');
$pageUrl         = $profileUrl;

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== PROFILE HERO ===== -->
<section class="profile-hero text-white">
    <!-- Cover image -->
    <div class="absolute inset-0 overflow-hidden" style="max-height:280px">
        <?php if ($provider['cover_url']): ?>
            <img src="<?= e($provider['cover_url']) ?>" class="w-full h-full object-cover opacity-30">
        <?php else: ?>
            <img src="https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=1400&h=280&q=60"
                 class="w-full h-full object-cover opacity-20">
        <?php endif; ?>
    </div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-20">
        <div class="flex flex-col sm:flex-row items-start sm:items-end gap-6">
            <!-- Avatar -->
            <div class="relative flex-shrink-0">
                <img src="<?= e($avatar) ?>"
                     alt="<?= e($provider['full_name']) ?>"
                     class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl object-cover border-4 border-white/30 shadow-2xl">
                <?php if ($provider['is_verified']): ?>
                <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-brand-400 rounded-full border-2 border-white flex items-center justify-center shadow">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white">
                        <?= e(!empty($provider['business_name']) ? $provider['business_name'] : $provider['full_name']) ?>
                    </h1>
                    <?php if (!empty($provider['business_name'])): ?>
                    <span class="badge bg-white/10 text-white border border-white/20 text-xs">🏢 Negocio</span>
                    <?php endif; ?>
                    <?php if ($provider['is_featured']): ?>
                    <span class="badge bg-amber-400/20 text-amber-300 border border-amber-400/30 text-xs">⭐ Destacado</span>
                    <?php endif; ?>
                    <?php if (!empty($provider['rank_slug']) && $provider['rank_slug'] !== 'nuevo'): ?>
                    <?= renderRankBadge([
                        'slug'        => $provider['rank_slug'],
                        'name'        => $provider['rank_name'],
                        'badge_icon'  => $provider['rank_icon'],
                        'badge_color' => $provider['rank_color'],
                    ]) ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($provider['business_name'])): ?>
                <p class="text-brand-300 text-sm mb-1">Representado por <?= e($provider['full_name']) ?></p>
                <?php endif; ?>
                <?php if ($provider['tagline']): ?>
                <p class="text-brand-200 text-lg mb-2"><?= e($provider['tagline']) ?></p>
                <?php endif; ?>

                <div class="flex flex-wrap items-center gap-4 text-sm text-brand-300">
                    <?php if ($provider['category_name']): ?>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <?= e($provider['category_name']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($loc): ?>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <?= e($loc) ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($provider['years_experience'] > 0): ?>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?= (int)$provider['years_experience'] ?> años de experiencia
                    </span>
                    <?php endif; ?>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <?= number_format($provider['profile_views']) ?> visitas
                    </span>
                </div>

                <?php if ($provider['rating_avg'] > 0): ?>
                <div class="flex items-center gap-3 mt-3">
                    <?= renderStars((float)$provider['rating_avg']) ?>
                    <span class="text-brand-300 text-sm"><?= number_format($provider['rating_avg'],1) ?> (<?= (int)$provider['rating_count'] ?> reseñas)</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 40" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="w-full h-8 fill-gray-50" style="display:block">
            <path d="M0,20 C360,40 1080,0 1440,20 L1440,40 L0,40 Z"/>
        </svg>
    </div>
</section>

<!-- ===== MAIN CONTENT ===== -->
<div class="bg-gray-50 pt-4 pb-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-3 gap-8">

            <!-- ===== LEFT COLUMN: Main content ===== -->
            <div class="lg:col-span-2 space-y-6">

                <!-- About -->
                <?php if ($provider['bio']): ?>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-brand-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        Sobre mí
                    </h2>
                    <p class="text-gray-700 leading-relaxed whitespace-pre-line"><?= nl2br(e($provider['bio'])) ?></p>
                    <?php if (!empty($tags)): ?>
                    <div class="flex flex-wrap gap-2 mt-4">
                        <?php foreach ($tags as $tag): ?>
                        <span class="badge badge-green"><?= e($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Services -->
                <?php if (!empty($services)): ?>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-brand-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </span>
                        Servicios y precios
                    </h2>
                    <div class="space-y-4">
                        <?php foreach ($services as $svc): ?>
                        <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-brand-50 transition-colors">
                            <div class="w-10 h-10 bg-brand-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900"><?= e($svc['title']) ?></h3>
                                <?php if ($svc['description']): ?>
                                <p class="text-sm text-gray-600 mt-1"><?= e($svc['description']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php $price = formatPrice($svc['price_from'], $svc['price_to'], $svc['price_type'], $svc['currency']); ?>
                            <?php if ($price): ?>
                            <div class="text-right flex-shrink-0">
                                <span class="font-bold text-brand-700 text-sm"><?= e($price) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Portfolio -->
                <?php if (!empty($portfolio)): ?>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-brand-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        Portafolio
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php foreach ($portfolio as $item): ?>
                        <div class="relative group rounded-xl overflow-hidden aspect-square bg-gray-100 cursor-pointer"
                             onclick="document.getElementById('lightbox-<?= $item['id'] ?>').classList.remove('hidden')">
                            <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['title'] ?? 'Trabajo') ?>"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                                <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                            </div>
                        </div>
                        <!-- Lightbox -->
                        <div id="lightbox-<?= $item['id'] ?>" class="hidden fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4"
                             onclick="this.classList.add('hidden')">
                            <img src="<?= e($item['image_url']) ?>" class="max-w-full max-h-full rounded-xl object-contain">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Reviews -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100" id="reviews">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-8 h-8 bg-brand-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </span>
                            Reseñas
                        </h2>
                        <?php if ($provider['rating_avg'] > 0): ?>
                        <div class="text-right">
                            <div class="text-3xl font-black text-gray-900"><?= number_format($provider['rating_avg'],1) ?></div>
                            <div class="text-xs text-gray-400"><?= (int)$provider['rating_count'] ?> reseñas</div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($reviews)): ?>
                    <p class="text-gray-500 text-center py-8">Aún no hay reseñas. ¡Sé el primero!</p>
                    <?php else: ?>
                    <div class="space-y-5">
                        <?php foreach ($reviews as $rev): ?>
                        <div class="flex gap-4 pb-5 border-b border-gray-100 last:border-0 last:pb-0">
                            <img src="<?= e($rev['reviewer_avatar'] ?? getAvatar(null, $rev['reviewer_name'] ?? 'U', '40')) ?>"
                                 alt="" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-semibold text-gray-900 text-sm"><?= e($rev['reviewer_name'] ?? 'Usuario') ?></span>
                                    <span class="text-xs text-gray-400"><?= timeAgo($rev['created_at']) ?></span>
                                </div>
                                <?= renderStars((float)$rev['rating'], false) ?>
                                <?php if ($rev['comment']): ?>
                                <p class="text-gray-700 text-sm mt-2 leading-relaxed"><?= e($rev['comment']) ?></p>
                                <?php endif; ?>
                                <?php if ($rev['reply']): ?>
                                <div class="mt-3 bg-brand-50 border-l-4 border-brand-400 pl-3 py-2 rounded-r-lg">
                                    <p class="text-xs font-semibold text-brand-700 mb-1">Respuesta de <?= e($provider['full_name']) ?>:</p>
                                    <p class="text-sm text-gray-700"><?= e($rev['reply']) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Leave review form -->
                    <div class="mt-6 pt-6 border-t border-gray-100" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="btn-outline w-full text-sm">
                            ✍️ Escribir una reseña
                        </button>
                        <div x-show="open" x-transition class="mt-4">
                            <form action="/review.php" method="POST" class="space-y-4">
                                <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="provider_id" value="<?= (int)$provider['id'] ?>">
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Tu nombre *</label>
                                        <input type="text" name="reviewer_name" class="form-input" required placeholder="María González">
                                    </div>
                                    <div>
                                        <label class="form-label">Tu email *</label>
                                        <input type="email" name="reviewer_email" class="form-input" required placeholder="maria@email.com">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Calificación *</label>
                                    <div class="flex gap-2 mt-1" id="star-rating-review">
                                        <input type="hidden" name="rating" id="rating-val" value="0">
                                        <?php for ($i=1;$i<=5;$i++): ?>
                                        <svg class="w-8 h-8 cursor-pointer text-gray-300 hover:text-amber-400 transition-colors fill-current"
                                             data-value="<?= $i ?>" viewBox="0 0 20 20"
                                             onclick="setRating(<?= $i ?>)">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Tu experiencia *</label>
                                    <textarea name="comment" class="form-input" rows="3" required
                                              placeholder="Cuéntanos cómo fue tu experiencia con <?= e($provider['full_name']) ?>..."
                                              data-maxlength="500"></textarea>
                                </div>
                                <button type="submit" class="btn-primary w-full">Publicar reseña</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== RIGHT COLUMN: Contact sidebar ===== -->
            <div class="space-y-4">
                <!-- Contact card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-20">
                    <h3 class="font-bold text-gray-900 mb-4 text-base">Contactar a <?= e(explode(' ', $provider['full_name'])[0]) ?></h3>

                    <?php if ($provider['response_time']): ?>
                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-5">
                        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Responde <?= e($provider['response_time']) ?>
                    </div>
                    <?php endif; ?>

                    <div class="space-y-3">
                        <?php if ($provider['whatsapp']): ?>
                        <a href="https://wa.me/<?= preg_replace('/\D/','',$provider['whatsapp']) ?>?text=Hola+<?= urlencode($provider['full_name']) ?>,+te+contacto+desde+Kontactanos"
                           target="_blank" rel="noopener"
                           class="share-btn share-btn-whatsapp w-full justify-center gap-2 py-3 text-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Contactar por WhatsApp
                        </a>
                        <?php endif; ?>

                        <?php if ($provider['phone']): ?>
                        <a href="tel:<?= e($provider['phone']) ?>"
                           class="w-full flex items-center justify-center gap-2 py-3 text-sm font-semibold bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Llamar: <?= e($provider['phone']) ?>
                        </a>
                        <?php endif; ?>

                        <!-- Contact form -->
                        <div class="pt-2" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="w-full btn-outline text-sm py-3">
                                ✉️ Enviar mensaje
                            </button>
                            <div x-show="open" x-transition class="mt-3">
                                <form action="/contact.php" method="POST" class="space-y-3">
                                    <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="provider_id" value="<?= (int)$provider['id'] ?>">
                                    <input type="text" name="requester_name" class="form-input text-sm" required placeholder="Tu nombre">
                                    <input type="email" name="requester_email" class="form-input text-sm" required placeholder="Tu email">
                                    <input type="tel" name="requester_phone" class="form-input text-sm" placeholder="Tu teléfono (opcional)">
                                    <textarea name="message" class="form-input text-sm" rows="3" required
                                              placeholder="¿En qué puedo ayudarte?"></textarea>
                                    <button type="submit" class="btn-primary w-full text-sm">Enviar consulta</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Share card -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-3 text-sm">Compartir perfil</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="https://wa.me/?text=<?= urlencode($provider['full_name'] . ' en Kontactanos: ' . $profileUrl) ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="share-btn share-btn-whatsapp text-xs py-2 justify-center">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($profileUrl) ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="share-btn share-btn-facebook text-xs py-2 justify-center">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($profileUrl) ?>&amp;text=<?= urlencode($provider['full_name']) ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="share-btn bg-sky-500 text-white hover:bg-sky-600 text-xs py-2 justify-center border-none">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                            Twitter
                        </a>
                        <button type="button" onclick="copyToClipboard('<?= e($profileUrl) ?>', '¡Enlace copiado! Compártelo donde quieras.')"
                                class="share-btn share-btn-copy text-xs py-2 justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            Copiar enlace
                        </button>
                    </div>

                    <!-- Profile URL display -->
                    <div class="mt-3 bg-gray-50 rounded-xl px-3 py-2 flex items-center gap-2">
                        <span class="text-xs text-gray-500 font-mono truncate"><?= e(APP_DOMAIN . '/p/' . $provider['slug']) ?></span>
                    </div>
                </div>

                <!-- Social links -->
                <?php if ($provider['instagram'] || $provider['facebook'] || $provider['linkedin'] || $provider['website']): ?>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-3 text-sm">Redes sociales</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($provider['website']): ?>
                        <a href="<?= e($provider['website']) ?>" target="_blank" rel="noopener"
                           class="flex items-center gap-1.5 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full transition-colors">
                            🌐 Sitio web
                        </a>
                        <?php endif; ?>
                        <?php if ($provider['instagram']): ?>
                        <a href="https://instagram.com/<?= e($provider['instagram']) ?>" target="_blank" rel="noopener"
                           class="flex items-center gap-1.5 text-xs text-white bg-pink-500 hover:bg-pink-600 px-3 py-1.5 rounded-full transition-colors">
                            📸 Instagram
                        </a>
                        <?php endif; ?>
                        <?php if ($provider['facebook']): ?>
                        <a href="https://facebook.com/<?= e($provider['facebook']) ?>" target="_blank" rel="noopener"
                           class="flex items-center gap-1.5 text-xs text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-full transition-colors">
                            👍 Facebook
                        </a>
                        <?php endif; ?>
                        <?php if ($provider['linkedin']): ?>
                        <a href="https://linkedin.com/in/<?= e($provider['linkedin']) ?>" target="_blank" rel="noopener"
                           class="flex items-center gap-1.5 text-xs text-white bg-sky-600 hover:bg-sky-700 px-3 py-1.5 rounded-full transition-colors">
                            💼 LinkedIn
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
function setRating(val) {
    document.getElementById('rating-val').value = val;
    const stars = document.querySelectorAll('#star-rating-review svg');
    stars.forEach((s, i) => {
        s.classList.toggle('text-amber-400', i < val);
        s.classList.toggle('text-gray-300', i >= val);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
