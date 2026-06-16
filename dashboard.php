<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();
$user = currentUser();

$profile = null;
$services = [];
$reviews = [];
$contacts = [];

if ($user['role'] === 'provider') {
    $profile  = DB::fetch("SELECT pp.*, c.name as category_name, l.city, l.country FROM provider_profiles pp LEFT JOIN categories c ON c.id = pp.category_id LEFT JOIN locations l ON l.id = pp.location_id WHERE pp.user_id = $1", [$user['id']]);
    if ($profile) {
        $services = getProviderServices($profile['id']);
        $reviews  = getProviderReviews($profile['id']);
        $contacts = DB::fetchAll("SELECT * FROM contact_requests WHERE provider_id = $1 ORDER BY created_at DESC LIMIT 10", [$profile['id']]);
    }
}

$welcome = !empty($_GET['welcome']);
$referralStats   = getReferralStats($user['id']);
$referralNetwork = getReferralNetwork($user['id']);
$referralLink    = APP_URL . '/register.php?ref=' . $referralStats['referral_code'];
$pageTitle = 'Mi Panel | Kontactanos';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if ($welcome): ?>
    <div class="bg-brand-50 border border-brand-200 rounded-2xl p-6 mb-8 flex items-center gap-4">
        <div class="w-12 h-12 bg-brand-100 rounded-2xl flex items-center justify-center text-2xl">🎉</div>
        <div>
            <h2 class="font-bold text-brand-800 text-lg">¡Bienvenido a Kontactanos!</h2>
            <p class="text-brand-700 text-sm">Tu perfil fue creado exitosamente. Complétalo para empezar a recibir clientes.</p>
        </div>
        <a href="/edit-profile.php" class="ml-auto btn-primary text-sm">Completar perfil →</a>
    </div>
    <?php endif; ?>

    <!-- Dashboard header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Mi Panel</h1>
            <p class="text-gray-500 text-sm mt-0.5">Hola, <?= e(explode('@', $user['email'])[0]) ?> 👋</p>
        </div>
        <?php if ($user['role'] === 'provider'): ?>
        <div class="flex gap-3">
            <a href="/create-service.php" class="btn-outline text-sm">+ Nuevo servicio</a>
            <a href="/edit-profile.php" class="btn-primary text-sm">Editar perfil</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Referral / network panel -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center gap-6 mb-6">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="font-bold text-gray-900">Mi Red de Referidos</h2>
                    <?= renderRankBadge($referralStats['rank']) ?>
                </div>
                <p class="text-gray-500 text-sm">
                    Invita profesionales y clientes a Kontactanos. Gana puntos por cada persona que se una con tu enlace,
                    y también por las personas que ellos inviten.
                </p>
                <?php if ($referralStats['next_rank']): ?>
                <div class="mt-3">
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                        <span><?= $referralStats['points'] ?> pts</span>
                        <span>Siguiente: <?= renderRankBadge($referralStats['next_rank'], true) ?> a los <?= (int)$referralStats['next_rank']['min_points'] ?> pts</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <?php
                        $rangeStart = (int)$referralStats['rank']['min_points'];
                        $rangeEnd   = (int)$referralStats['next_rank']['min_points'];
                        $pct = $rangeEnd > $rangeStart
                            ? min(100, round((($referralStats['points'] - $rangeStart) / ($rangeEnd - $rangeStart)) * 100))
                            : 100;
                        ?>
                        <div class="bg-brand-500 h-2 rounded-full transition-all" style="width:<?= $pct ?>%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Te faltan <?= $referralStats['points_to_next'] ?> pts para el siguiente rango.</p>
                </div>
                <?php else: ?>
                <p class="text-xs font-semibold text-brand-600 mt-3">¡Has alcanzado el rango máximo! 🎉 <?= $referralStats['points'] ?> pts</p>
                <?php endif; ?>
            </div>

            <div class="lg:w-80 flex-shrink-0">
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="bg-brand-50 rounded-xl p-3 text-center">
                        <div class="text-2xl font-extrabold text-brand-700"><?= $referralStats['direct_count'] ?></div>
                        <div class="text-xs text-gray-500">Referidos directos</div>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-3 text-center">
                        <div class="text-2xl font-extrabold text-amber-600"><?= $referralStats['indirect_count'] ?></div>
                        <div class="text-xs text-gray-500">Referidos de 2do nivel</div>
                    </div>
                </div>
                <label class="form-label text-xs">Tu enlace de invitación</label>
                <div class="flex gap-2">
                    <input type="text" readonly value="<?= e($referralLink) ?>"
                           class="form-input text-xs flex-1 bg-gray-50" onclick="this.select()">
                    <button onclick="copyToClipboard('<?= e($referralLink) ?>', '¡Enlace copiado! Compártelo para sumar puntos.')"
                            class="btn-primary text-xs px-3 flex-shrink-0">Copiar</button>
                </div>
            </div>
        </div>

        <?php if (!empty($referralNetwork)): ?>
        <div class="border-t border-gray-100 pt-4">
            <h3 class="text-sm font-bold text-gray-700 mb-3">Tus referidos directos</h3>
            <div class="space-y-2">
                <?php foreach ($referralNetwork as $ref): ?>
                <div class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50">
                    <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0 text-sm">
                        <?= $ref['role'] === 'provider' ? '🛠️' : '🔍' ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate"><?= e($ref['display_name']) ?></p>
                        <p class="text-xs text-gray-400">
                            <?= $ref['role'] === 'provider' ? 'Profesional' : 'Cliente' ?> · <?= timeAgo($ref['created_at']) ?>
                        </p>
                    </div>
                    <?php if ((int)$ref['referral_count'] > 0): ?>
                    <span class="badge badge-green text-xs flex-shrink-0">+<?= (int)$ref['referral_count'] ?> referidos</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="border-t border-gray-100 pt-4 text-center text-sm text-gray-400">
            Aún no tienes referidos. ¡Comparte tu enlace para empezar a ganar puntos!
        </div>
        <?php endif; ?>
    </div>

    <?php if ($user['role'] === 'provider'): ?>

    <!-- Stats row -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <?php
        $stats = [
            ['label' => 'Visitas al perfil', 'value' => number_format($profile['profile_views'] ?? 0), 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'color' => 'blue'],
            ['label' => 'Reseñas', 'value' => (string)count($reviews), 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color' => 'amber'],
            ['label' => 'Mensajes recibidos', 'value' => (string)count($contacts), 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'color' => 'green'],
            ['label' => 'Calificación', 'value' => $profile['rating_avg'] ? number_format($profile['rating_avg'],1) . '★' : 'Sin reseñas', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'purple'],
        ];
        $colorMap = ['blue' => 'bg-blue-100 text-blue-600', 'amber' => 'bg-amber-100 text-amber-600', 'green' => 'bg-brand-100 text-brand-600', 'purple' => 'bg-purple-100 text-purple-600'];
        foreach ($stats as $s):
        ?>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center <?= $colorMap[$s['color']] ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $s['icon'] ?>"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-gray-900"><?= e($s['value']) ?></div>
            <div class="text-xs text-gray-500 mt-0.5"><?= e($s['label']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Profile card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900">Mi Perfil</h2>
                <a href="/edit-profile.php" class="text-xs text-brand-600 hover:underline font-semibold">Editar</a>
            </div>
            <?php if ($profile): ?>
            <div class="text-center mb-4">
                <img src="<?= e(getAvatar($profile['avatar_url'], $profile['full_name'], '100')) ?>"
                     alt="" class="w-20 h-20 rounded-2xl object-cover mx-auto mb-3 border-4 border-brand-100">
                <h3 class="font-bold text-gray-900"><?= e($profile['full_name']) ?></h3>
                <p class="text-sm text-gray-500"><?= e($profile['tagline'] ?? '') ?></p>
                <div class="flex items-center justify-center gap-2 mt-2 text-xs text-gray-400">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    <?= e(($profile['city'] ?? '') . ', ' . ($profile['country'] ?? '')) ?>
                </div>
            </div>

            <!-- Profile completeness -->
            <?php
            $fields = ['avatar_url', 'bio', 'phone', 'whatsapp', 'category_id', 'location_id'];
            $filled = array_filter($fields, fn($f) => !empty($profile[$f]));
            $pct = round(count($filled) / count($fields) * 100);
            ?>
            <div class="mb-4">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span>Perfil completado</span>
                    <span class="font-semibold text-brand-600"><?= $pct ?>%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-brand-500 h-2 rounded-full transition-all" style="width:<?= $pct ?>%"></div>
                </div>
                <?php if ($pct < 100): ?>
                <p class="text-xs text-gray-400 mt-1">Completa tu perfil para más visibilidad</p>
                <?php endif; ?>
            </div>

            <div class="flex gap-2">
                <a href="/p/<?= e($profile['slug']) ?>" target="_blank"
                   class="flex-1 text-center text-xs py-2 bg-brand-50 text-brand-700 rounded-xl font-semibold hover:bg-brand-100 transition-colors">
                    Ver perfil →
                </a>
                <button onclick="copyToClipboard('<?= e(APP_URL . '/p/' . $profile['slug']) ?>', '¡Enlace copiado! Compártelo donde quieras.')"
                        class="flex-1 text-center text-xs py-2 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                    Copiar enlace
                </button>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <p class="text-gray-500 text-sm mb-4">Aún no tienes perfil público.</p>
                <a href="/edit-profile.php" class="btn-primary text-sm">Crear perfil</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent contacts -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900">Mensajes recientes</h2>
                <a href="/inbox.php" class="text-xs text-brand-600 hover:text-brand-800 font-semibold transition-colors">
                    Ver bandeja completa →
                </a>
            </div>
            <?php if (empty($contacts)): ?>
            <div class="text-center py-8 text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-sm">Aún no has recibido mensajes.</p>
                <p class="text-xs mt-1">Comparte tu perfil para empezar a recibir contactos.</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($contacts as $contact): ?>
                <div class="flex items-start gap-3 p-3 rounded-xl <?= $contact['status'] === 'pending' ? 'bg-brand-50 border border-brand-100' : 'bg-gray-50' ?>">
                    <div class="w-10 h-10 bg-gray-200 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold text-gray-600"><?= strtoupper(substr($contact['requester_name'] ?? 'U', 0, 1)) ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-800 text-sm"><?= e($contact['requester_name'] ?? 'Anónimo') ?></span>
                            <?php if ($contact['status'] === 'pending'): ?>
                            <span class="badge badge-green text-xs">Nuevo</span>
                            <?php endif; ?>
                            <span class="text-xs text-gray-400 ml-auto"><?= timeAgo($contact['created_at']) ?></span>
                        </div>
                        <p class="text-xs text-gray-600 mt-0.5 line-clamp-2"><?= e($contact['message'] ?? '') ?></p>
                        <?php if ($contact['requester_email']): ?>
                        <a href="mailto:<?= e($contact['requester_email']) ?>"
                           class="text-xs text-brand-600 hover:underline mt-0.5 inline-block"><?= e($contact['requester_email']) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Services -->
    <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-gray-900">Mis Servicios</h2>
            <a href="/create-service.php" class="btn-primary text-sm">+ Agregar</a>
        </div>
        <?php if (empty($services)): ?>
        <div class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">
            <p class="text-sm">No tienes servicios publicados.</p>
            <a href="/create-service.php" class="text-brand-600 text-sm font-semibold hover:underline mt-1 inline-block">Publicar primer servicio →</a>
        </div>
        <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($services as $svc): ?>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <h3 class="font-semibold text-gray-900 text-sm mb-1"><?= e($svc['title']) ?></h3>
                <?php if ($svc['description']): ?>
                <p class="text-xs text-gray-500 line-clamp-2 mb-2"><?= e($svc['description']) ?></p>
                <?php endif; ?>
                <?php $p = formatPrice($svc['price_from'], $svc['price_to'], $svc['price_type'], $svc['currency']); ?>
                <?php if ($p): ?>
                <span class="text-brand-700 text-xs font-bold"><?= e($p) ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- Client dashboard -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center">
        <div class="w-16 h-16 bg-brand-100 rounded-3xl flex items-center justify-center mx-auto mb-4 text-3xl">🔍</div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">¿Qué necesitas hoy?</h2>
        <p class="text-gray-500 mb-6">Busca entre miles de profesionales verificados en tu ciudad.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/search.php" class="btn-primary px-8">Explorar profesionales</a>
            <a href="/register.php?role=provider" class="btn-outline px-8">¿Tienes un servicio? Publícalo</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
