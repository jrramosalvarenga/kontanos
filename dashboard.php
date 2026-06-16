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
        $contacts = DB::fetchAll("
            SELECT * FROM contact_requests
            WHERE provider_id = $1
            ORDER BY CASE status WHEN 'pending' THEN 0 ELSE 1 END, created_at DESC
            LIMIT 20
        ", [$profile['id']]);
        $pendingCount = count(array_filter($contacts, fn($c) => $c['status'] === 'pending'));
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

    <?php
    $profileFields = ['avatar_url', 'bio', 'phone', 'whatsapp', 'category_id', 'location_id'];
    $profileFilled = count(array_filter($profileFields, fn($f) => !empty($profile[$f])));
    $profilePct    = round($profileFilled / count($profileFields) * 100);
    $statusLabels  = [
        'pending' => ['txt' => 'Nuevo',      'cls' => 'bg-green-100 text-green-800'],
        'read'    => ['txt' => 'Leído',       'cls' => 'bg-gray-100 text-gray-500'],
        'replied' => ['txt' => 'Contestado',  'cls' => 'bg-blue-100 text-blue-700'],
        'closed'  => ['txt' => 'Archivado',   'cls' => 'bg-gray-100 text-gray-400'],
    ];
    ?>

    <!-- ===== SOLICITUDES DE SERVICIOS ===== -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6"
         x-data="{ tab: '<?= $pendingCount > 0 ? 'new' : 'all' ?>' }">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-900 flex items-center gap-2">
                Solicitudes de servicios
                <?php if ($pendingCount > 0): ?>
                <span class="bg-brand-600 text-white text-xs font-bold rounded-full px-2 py-0.5"><?= $pendingCount ?> nuevas</span>
                <?php endif; ?>
            </h2>
            <a href="/inbox.php" class="text-xs text-brand-600 hover:text-brand-800 font-semibold transition-colors">
                Ver bandeja completa →
            </a>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 px-6 pt-4 pb-3">
            <button @click="tab='new'"
                    :class="tab==='new' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 bg-gray-100'"
                    class="text-xs font-semibold px-4 py-1.5 rounded-lg transition-all">
                Nuevas <?php if ($pendingCount > 0): ?>(<?= $pendingCount ?>)<?php endif; ?>
            </button>
            <button @click="tab='all'"
                    :class="tab==='all' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 bg-gray-100'"
                    class="text-xs font-semibold px-4 py-1.5 rounded-lg transition-all">
                Todas (<?= count($contacts) ?>)
            </button>
        </div>

        <!-- Contacts -->
        <div class="px-6 pb-5">
            <?php if (empty($contacts)): ?>
            <div class="text-center py-10 text-gray-400">
                <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-600 mb-1">Aún no tienes solicitudes</p>
                <p class="text-xs text-gray-400">Comparte tu perfil para empezar a recibir contactos.</p>
                <?php if ($profile): ?>
                <button onclick="copyToClipboard('<?= e(APP_URL . '/p/' . $profile['slug']) ?>', '¡Enlace copiado!')"
                        class="mt-3 text-xs text-brand-600 hover:text-brand-800 font-semibold transition-colors">
                    Copiar enlace de mi perfil →
                </button>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($contacts as $c):
                    $sl    = $statusLabels[$c['status']] ?? $statusLabels['read'];
                    $phone = preg_replace('/\D/', '', $c['requester_phone'] ?? '');
                    $waUrl = $phone ? 'https://wa.me/' . $phone : null;
                    $isPending = $c['status'] === 'pending';
                ?>
                <div x-show="tab === 'all' || <?= $isPending ? 'true' : 'false' ?>"
                     class="rounded-xl border <?= $isPending ? 'border-brand-200 bg-brand-50/40' : 'border-gray-100 bg-gray-50' ?> p-4">
                    <!-- Top row -->
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-9 h-9 rounded-xl <?= $isPending ? 'bg-brand-100 text-brand-700' : 'bg-gray-200 text-gray-600' ?> flex items-center justify-center flex-shrink-0 font-bold text-sm">
                            <?= strtoupper(mb_substr($c['requester_name'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-gray-900 text-sm"><?= e($c['requester_name'] ?? 'Anónimo') ?></span>
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full <?= $sl['cls'] ?>"><?= $sl['txt'] ?></span>
                                <span class="text-xs text-gray-400 ml-auto"><?= timeAgo($c['created_at']) ?></span>
                            </div>
                            <div class="flex gap-3 mt-0.5 flex-wrap">
                                <?php if ($c['requester_email']): ?>
                                <span class="text-xs text-gray-500"><?= e($c['requester_email']) ?></span>
                                <?php endif; ?>
                                <?php if ($c['requester_phone']): ?>
                                <span class="text-xs text-gray-500"><?= e($c['requester_phone']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Message -->
                    <p class="text-sm text-gray-700 leading-relaxed mb-3 pl-12 whitespace-pre-wrap"><?= e($c['message'] ?? '') ?></p>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2 pl-12">
                        <?php if ($c['requester_email']): ?>
                        <a href="mailto:<?= e($c['requester_email']) ?>?subject=<?= urlencode('Re: tu mensaje en Kontactanos') ?>"
                           class="inline-flex items-center gap-1.5 bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Responder
                        </a>
                        <?php endif; ?>
                        <?php if ($waUrl): ?>
                        <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            WhatsApp
                        </a>
                        <?php endif; ?>

                        <div class="ml-auto flex gap-1.5">
                            <?php if ($isPending): ?>
                            <form method="POST" action="/contact-action.php">
                                <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
                                <input type="hidden" name="new_status" value="read">
                                <input type="hidden" name="redirect_to" value="/dashboard.php">
                                <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 bg-white px-2.5 py-1.5 rounded-lg transition-colors">
                                    Marcar leído
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php if (in_array($c['status'], ['pending', 'read'])): ?>
                            <form method="POST" action="/contact-action.php">
                                <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
                                <input type="hidden" name="new_status" value="replied">
                                <input type="hidden" name="redirect_to" value="/dashboard.php">
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 border border-blue-200 hover:border-blue-300 bg-white px-2.5 py-1.5 rounded-lg transition-colors">
                                    Contestado
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php if ($c['status'] !== 'closed'): ?>
                            <form method="POST" action="/contact-action.php">
                                <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
                                <input type="hidden" name="new_status" value="closed">
                                <input type="hidden" name="redirect_to" value="/dashboard.php">
                                <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 hover:border-gray-300 bg-white px-2.5 py-1.5 rounded-lg transition-colors">
                                    Archivar
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== PERFIL + SERVICIOS ===== -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Profile card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 text-sm">Mi Perfil</h2>
                <a href="/edit-profile.php" class="text-xs text-brand-600 hover:underline font-semibold">Editar</a>
            </div>
            <?php if ($profile): ?>
            <div class="flex items-center gap-3 mb-4">
                <img src="<?= e(getAvatar($profile['avatar_url'], $profile['full_name'], '80')) ?>"
                     alt="" class="w-14 h-14 rounded-xl object-cover border-2 border-brand-100 flex-shrink-0">
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-900 text-sm truncate"><?= e($profile['full_name']) ?></h3>
                    <?php if ($profile['tagline']): ?>
                    <p class="text-xs text-gray-500 line-clamp-2"><?= e($profile['tagline']) ?></p>
                    <?php endif; ?>
                    <?php if ($profile['city']): ?>
                    <p class="text-xs text-gray-400 mt-0.5"><?= e($profile['city'] . ', ' . $profile['country']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mb-4">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Perfil completado</span>
                    <span class="font-semibold text-brand-600"><?= $profilePct ?>%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="bg-brand-500 h-1.5 rounded-full" style="width:<?= $profilePct ?>%"></div>
                </div>
                <?php if ($profilePct < 100): ?>
                <a href="/edit-profile.php" class="text-xs text-brand-600 hover:underline mt-1 inline-block">Completar perfil →</a>
                <?php endif; ?>
            </div>
            <div class="flex gap-2">
                <a href="/p/<?= e($profile['slug']) ?>" target="_blank"
                   class="flex-1 text-center text-xs py-2 bg-brand-50 text-brand-700 rounded-xl font-semibold hover:bg-brand-100 transition-colors">Ver perfil</a>
                <button onclick="copyToClipboard('<?= e(APP_URL . '/p/' . $profile['slug']) ?>', '¡Enlace copiado!')"
                        class="flex-1 text-center text-xs py-2 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors">Copiar enlace</button>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <p class="text-gray-500 text-sm mb-3">Aún no tienes perfil público.</p>
                <a href="/edit-profile.php" class="btn-primary text-sm">Crear perfil</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Services -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 text-sm">Mis Servicios</h2>
                <a href="/create-service.php" class="btn-primary text-xs py-1.5 px-3">+ Agregar</a>
            </div>
            <?php if (empty($services)): ?>
            <div class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">
                <p class="text-sm">No tienes servicios publicados.</p>
                <a href="/create-service.php" class="text-brand-600 text-sm font-semibold hover:underline mt-1 inline-block">Publicar primer servicio →</a>
            </div>
            <?php else: ?>
            <div class="grid sm:grid-cols-2 gap-3">
                <?php foreach ($services as $svc): ?>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <h3 class="font-semibold text-gray-900 text-sm mb-1"><?= e($svc['title']) ?></h3>
                    <?php if ($svc['description']): ?>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-1"><?= e($svc['description']) ?></p>
                    <?php endif; ?>
                    <?php $p = formatPrice($svc['price_from'], $svc['price_to'], $svc['price_type'], $svc['currency']); ?>
                    <?php if ($p): ?><span class="text-brand-700 text-xs font-bold"><?= e($p) ?></span><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
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
