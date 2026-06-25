<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();
$user  = currentUser();
$stats = getReferralStats($user['id']);
$net   = getReferralNetwork($user['id']);
$ranks = getRanks();
$link  = APP_URL . '/register.php?ref=' . $stats['referral_code'];

$waText = urlencode(
    '¡Te invito a unirte a Kontactanos! Es la plataforma para encontrar o publicar servicios profesionales en tu área, totalmente gratis. Regístrate aquí: ' . $link
);

$pageTitle = 'Invita y sube de rango | Kontactanos';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero: tu rango actual -->
<div class="bg-gradient-to-br from-brand-900 to-brand-700 text-white px-4 pt-10 pb-20">
    <div class="max-w-2xl mx-auto text-center">

        <!-- Current rank badge -->
        <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 rounded-full px-4 py-2 text-sm font-semibold mb-4">
            <span class="text-xl"><?= e($stats['rank']['badge_icon']) ?></span>
            Rango actual: <span class="text-white font-extrabold"><?= e($stats['rank']['name']) ?></span>
        </div>

        <h1 class="text-3xl sm:text-4xl font-extrabold mb-3 leading-tight">
            Invita y sube de categoría
        </h1>
        <p class="text-brand-200 text-base leading-relaxed mb-8 max-w-md mx-auto">
            Cada persona que se una con tu enlace te da puntos. Más puntos = mayor rango = mejor posición en búsquedas.
        </p>

        <!-- Progress to next rank -->
        <?php if ($stats['next_rank']): ?>
        <?php
        $start = (int)$stats['rank']['min_points'];
        $end   = (int)$stats['next_rank']['min_points'];
        $pct   = $end > $start ? min(100, round((($stats['points'] - $start) / ($end - $start)) * 100)) : 100;
        ?>
        <div class="bg-white/10 rounded-2xl p-5 mb-6 text-left max-w-sm mx-auto">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-lg"><?= e($stats['rank']['badge_icon']) ?></span>
                    <span class="text-sm font-semibold"><?= e($stats['rank']['name']) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold"><?= e($stats['next_rank']['badge_icon']) ?></span>
                    <span class="text-sm font-semibold"><?= e($stats['next_rank']['name']) ?></span>
                </div>
            </div>
            <div class="w-full bg-white/20 rounded-full h-3 mb-2">
                <div class="h-3 rounded-full bg-amber-400 transition-all" style="width:<?= $pct ?>%"></div>
            </div>
            <div class="flex items-center justify-between text-xs text-brand-300">
                <span><?= number_format($stats['points']) ?> pts</span>
                <span class="text-amber-300 font-semibold">
                    Faltan <?= number_format($stats['points_to_next']) ?> pts para <?= e($stats['next_rank']['name']) ?>
                </span>
                <span><?= number_format($end) ?> pts</span>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-amber-400/20 border border-amber-400/30 rounded-2xl p-4 mb-6 inline-block">
            <p class="text-amber-300 font-extrabold text-lg">👑 ¡Rango máximo alcanzado!</p>
            <p class="text-brand-200 text-sm"><?= number_format($stats['points']) ?> puntos totales</p>
        </div>
        <?php endif; ?>

        <!-- Counters -->
        <div class="grid grid-cols-3 gap-3 max-w-sm mx-auto">
            <div class="bg-white/10 rounded-xl p-3 text-center">
                <div class="text-2xl font-extrabold"><?= number_format($stats['points']) ?></div>
                <div class="text-xs text-brand-300">Puntos</div>
            </div>
            <div class="bg-white/10 rounded-xl p-3 text-center">
                <div class="text-2xl font-extrabold"><?= $stats['direct_count'] ?></div>
                <div class="text-xs text-brand-300">Invitados</div>
            </div>
            <div class="bg-white/10 rounded-xl p-3 text-center">
                <div class="text-2xl font-extrabold"><?= $stats['indirect_count'] ?></div>
                <div class="text-xs text-brand-300">Nivel 2</div>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-50 -mt-6 rounded-t-3xl pt-8 pb-20 px-4">
    <div class="max-w-2xl mx-auto space-y-8">

        <!-- Share card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-extrabold text-gray-900 text-lg mb-1">Tu enlace de invitación</h2>
            <p class="text-sm text-gray-500 mb-4">Compártelo y gana puntos automáticamente cuando alguien se registre.</p>

            <div class="flex gap-2 mb-5">
                <input type="text" readonly id="invite-link" value="<?= e($link) ?>"
                       class="form-input text-sm flex-1 bg-gray-50 font-mono" onclick="this.select()">
                <button onclick="copyLink()"
                        class="btn-primary text-sm px-4 flex-shrink-0 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    Copiar
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="https://wa.me/?text=<?= $waText ?>"
                   target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#1ebe5c] text-white font-semibold py-3 rounded-xl transition-colors text-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($link) ?>"
                   target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 bg-[#1877F2] hover:bg-[#1464d0] text-white font-semibold py-3 rounded-xl transition-colors text-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    Facebook
                </a>
                <button onclick="nativeShare()"
                        class="flex items-center justify-center gap-2 bg-gray-800 hover:bg-gray-900 text-white font-semibold py-3 rounded-xl transition-colors text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    Más opciones
                </button>
            </div>
        </div>

        <!-- How it works: pyramid -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-extrabold text-gray-900 text-lg mb-1">Cómo funciona la pirámide</h2>
            <p class="text-sm text-gray-500 mb-5">Ganas puntos en dos niveles — no solo por tus invitados directos.</p>

            <div class="space-y-3">
                <!-- Me -->
                <div class="flex items-center gap-3 bg-brand-50 border border-brand-200 rounded-xl p-4">
                    <div class="w-10 h-10 bg-brand-700 rounded-full flex items-center justify-center text-white font-extrabold flex-shrink-0">Tú</div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 text-sm">Tu cuenta</p>
                        <p class="text-xs text-gray-500">Compartes tu enlace con amigos y colegas</p>
                    </div>
                </div>

                <div class="flex justify-center"><div class="w-px h-4 bg-gray-300"></div></div>

                <!-- Level 1 -->
                <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0">N1</div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 text-sm">Tus invitados directos</p>
                        <p class="text-xs text-gray-500">Se registran con tu enlace</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-sm font-extrabold text-blue-700">+10 pts</div>
                        <div class="text-[10px] text-gray-400">por profesional</div>
                        <div class="text-sm font-extrabold text-blue-600 mt-0.5">+2 pts</div>
                        <div class="text-[10px] text-gray-400">por cliente</div>
                    </div>
                </div>

                <div class="flex justify-center"><div class="w-px h-4 bg-gray-300"></div></div>

                <!-- Level 2 -->
                <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="w-10 h-10 bg-amber-500 rounded-full flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0">N2</div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 text-sm">Los invitados de tus invitados</p>
                        <p class="text-xs text-gray-500">Ganas también cuando ellos refieren a otros</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-sm font-extrabold text-amber-700">+3 pts</div>
                        <div class="text-[10px] text-gray-400">por profesional</div>
                        <div class="text-sm font-extrabold text-amber-600 mt-0.5">+1 pt</div>
                        <div class="text-[10px] text-gray-400">por cliente</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rank table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-extrabold text-gray-900 text-lg mb-1">Rangos y beneficios</h2>
            <p class="text-sm text-gray-500 mb-5">Mayor rango = mejor posición en búsquedas y más visibilidad.</p>

            <div class="space-y-2">
                <?php foreach ($ranks as $r):
                    $isCurrent = (int)$r['id'] === (int)$stats['rank']['rank_id'];
                    $isReached = $stats['points'] >= (int)$r['min_points'];
                ?>
                <div class="flex items-center gap-4 rounded-xl p-3.5 border transition-all <?= $isCurrent ? 'border-brand-400 bg-brand-50 ring-1 ring-brand-300' : ($isReached ? 'border-gray-200 bg-gray-50' : 'border-dashed border-gray-200') ?>">
                    <div class="text-2xl flex-shrink-0"><?= e($r['badge_icon']) ?></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900 text-sm"><?= e($r['name']) ?></span>
                            <?php if ($isCurrent): ?>
                            <span class="text-[10px] bg-brand-600 text-white font-bold px-2 py-0.5 rounded-full">Tu rango</span>
                            <?php elseif ($isReached): ?>
                            <span class="text-[10px] text-green-600 font-semibold">✓ Alcanzado</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-400"><?= number_format($r['min_points']) ?> puntos mínimos</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <?php if ((int)$r['search_boost'] > 0): ?>
                        <div class="text-xs font-bold" style="color:<?= e($r['badge_color']) ?>">
                            +<?= (int)$r['search_boost'] ?>% visibilidad
                        </div>
                        <?php else: ?>
                        <div class="text-xs text-gray-400">Base</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- My network -->
        <?php if (!empty($net)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-extrabold text-gray-900 text-lg mb-4">Tu red (<?= count($net) ?>)</h2>
            <div class="space-y-2">
                <?php foreach ($net as $ref): ?>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-lg flex-shrink-0 bg-white border border-gray-200">
                        <?= $ref['role'] === 'provider' ? '🛠️' : '🔍' ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate"><?= e($ref['display_name']) ?></p>
                        <p class="text-xs text-gray-400">
                            <?= $ref['role'] === 'provider' ? 'Profesional' : 'Cliente' ?> · <?= timeAgo($ref['created_at']) ?>
                        </p>
                    </div>
                    <?php if ((int)$ref['referral_count'] > 0): ?>
                    <span class="text-xs font-bold text-brand-600 flex-shrink-0">
                        +<?= (int)$ref['referral_count'] ?> referidos
                    </span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl border border-dashed border-gray-200 p-8 text-center">
            <div class="text-4xl mb-3">👥</div>
            <p class="font-semibold text-gray-700 mb-1">Tu red está vacía</p>
            <p class="text-sm text-gray-400 mb-4">Comparte tu enlace y empieza a ganar puntos hoy mismo.</p>
            <button onclick="copyLink()" class="btn-primary text-sm px-6">Copiar mi enlace</button>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
const inviteLink = <?= json_encode($link) ?>;

function copyLink() {
    navigator.clipboard.writeText(inviteLink).then(() => {
        showToast('¡Enlace copiado! Compártelo para sumar puntos.');
    });
}

function nativeShare() {
    const data = {
        title: '¡Únete a Kontactanos!',
        text: '¡Te invito a Kontactanos! Publica o encuentra servicios profesionales gratis en tu área.',
        url: inviteLink
    };
    if (navigator.share) {
        navigator.share(data).catch(() => {});
    } else {
        copyLink();
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
