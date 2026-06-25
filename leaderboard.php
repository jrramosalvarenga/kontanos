<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$currentUser = isLoggedIn() ? currentUser() : null;

// Filters
$filterCity = trim($_GET['city'] ?? '');

// Available cities (only with active providers with points)
$cities = DB::fetchAll("
    SELECT DISTINCT l.city, l.country
    FROM users u
    JOIN provider_profiles pp ON pp.user_id = u.id
    JOIN locations l ON l.id = pp.location_id
    WHERE u.role = 'provider' AND u.points > 0 AND pp.is_active = TRUE
    ORDER BY l.country, l.city
");

// Main leaderboard query
$params = [];
$cityWhere = '';
if ($filterCity) {
    $params[] = $filterCity;
    $cityWhere = "AND l.city = $" . count($params);
}

$leaders = DB::fetchAll("
    SELECT
        u.id,
        u.points,
        pp.full_name,
        pp.business_name,
        pp.slug,
        pp.avatar_url,
        pp.tagline,
        l.city,
        l.country,
        c.name  AS category_name,
        rk.name AS rank_name,
        rk.slug AS rank_slug,
        rk.badge_icon AS rank_icon,
        rk.badge_color AS rank_color,
        (SELECT COUNT(*) FROM users r WHERE r.referred_by = u.id) AS direct_refs
    FROM users u
    JOIN provider_profiles pp ON pp.user_id = u.id
    LEFT JOIN locations l ON l.id = pp.location_id
    LEFT JOIN categories c ON c.id = pp.category_id
    LEFT JOIN ranks rk ON rk.id = u.rank_id
    WHERE u.role = 'provider'
      AND pp.is_active = TRUE
      AND u.points > 0
      $cityWhere
    ORDER BY u.points DESC
    LIMIT 50
", $params);

// Current user's position (providers only)
$myPosition = null;
if ($currentUser && $currentUser['role'] === 'provider') {
    $pos = DB::fetch("
        SELECT COUNT(*) + 1 AS pos
        FROM users u2
        JOIN provider_profiles pp2 ON pp2.user_id = u2.id
        WHERE u2.role = 'provider' AND pp2.is_active = TRUE AND u2.points > (
            SELECT points FROM users WHERE id = $1
        )
    ", [$currentUser['id']]);
    $myPoints = DB::fetch("SELECT points FROM users WHERE id = $1", [$currentUser['id']]);
    $myPosition = ['pos' => (int)$pos['pos'], 'points' => (int)$myPoints['points']];
}

$pageTitle = 'Ranking de Profesionales | Kontactanos';
$pageDescription = 'Los profesionales más activos y recomendados en Kontactanos. ¿Quién está en el top?';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<div class="bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white pt-10 pb-16 px-4">
    <div class="max-w-3xl mx-auto text-center">
        <div class="text-5xl mb-3">🏆</div>
        <h1 class="text-3xl sm:text-4xl font-extrabold mb-2">Ranking de Profesionales</h1>
        <p class="text-brand-200 text-base mb-6">
            Los profesionales que más crecen en Kontactanos. Sube de rango refiriendo colegas y recibiendo solicitudes.
        </p>

        <!-- City filter -->
        <form method="GET" class="flex items-center justify-center gap-2 max-w-sm mx-auto">
            <select name="city" onchange="this.form.submit()"
                    class="flex-1 bg-white/10 border border-white/20 text-white rounded-xl px-4 py-2.5 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-white/30">
                <option value="">Todas las ciudades</option>
                <?php foreach ($cities as $c): ?>
                <option value="<?= e($c['city']) ?>" <?= $filterCity === $c['city'] ? 'selected' : '' ?>>
                    <?= e($c['city']) ?>, <?= e($c['country']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($filterCity): ?>
            <a href="/leaderboard.php" class="text-white/70 hover:text-white text-sm underline whitespace-nowrap">Ver todos</a>
            <?php endif; ?>
        </form>

        <!-- My position pill -->
        <?php if ($myPosition): ?>
        <div class="mt-5 inline-flex items-center gap-2 bg-white/10 border border-white/20 rounded-full px-4 py-2 text-sm">
            <span class="text-brand-300">Tu posición:</span>
            <span class="font-extrabold text-white">#<?= $myPosition['pos'] ?></span>
            <span class="text-brand-300">·</span>
            <span class="font-semibold text-amber-300"><?= number_format($myPosition['points']) ?> pts</span>
            <a href="/dashboard.php" class="ml-1 text-xs underline text-brand-300 hover:text-white">Ver mi perfil →</a>
        </div>
        <?php elseif (!$currentUser): ?>
        <div class="mt-5 text-sm text-brand-300">
            <a href="/register.php?role=provider" class="underline text-white font-semibold">Regístrate gratis</a> y empieza a sumar puntos
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="bg-gray-50 min-h-screen -mt-6 rounded-t-3xl pt-8 pb-20 px-4">
    <div class="max-w-3xl mx-auto">

        <?php if (empty($leaders)): ?>
        <div class="text-center py-20 text-gray-400">
            <div class="text-5xl mb-4">🌱</div>
            <p class="font-semibold text-gray-600">Aún no hay datos para esta ciudad.</p>
            <p class="text-sm mt-1">¡Sé el primero en subir de rango!</p>
            <a href="/register.php?role=provider" class="btn-primary mt-6 inline-block">Crear perfil gratis</a>
        </div>
        <?php else: ?>

        <!-- ── Podium Top 3 ── -->
        <?php if (count($leaders) >= 3): ?>
        <div class="flex items-end justify-center gap-3 mb-10">
            <?php
            $podium = [
                1 => ['pos' => '🥈', 'height' => 'h-28', 'bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'idx' => 1],
                0 => ['pos' => '🥇', 'height' => 'h-36', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'idx' => 0],
                2 => ['pos' => '🥉', 'height' => 'h-24', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'idx' => 2],
            ];
            foreach ([1, 0, 2] as $order):
                $p = $podium[$order];
                $leader = $leaders[$p['idx']];
                $name = !empty($leader['business_name']) ? $leader['business_name'] : $leader['full_name'];
            ?>
            <div class="flex flex-col items-center gap-2 flex-1 max-w-[140px]">
                <!-- Avatar -->
                <a href="/p/<?= e($leader['slug']) ?>" class="block">
                    <div class="relative">
                        <img src="<?= e(getAvatar($leader['avatar_url'], $leader['full_name'], '80')) ?>"
                             alt="<?= e($name) ?>"
                             class="w-14 h-14 rounded-full object-cover border-4 <?= $p['idx'] === 0 ? 'border-amber-400 shadow-lg shadow-amber-100' : 'border-white shadow' ?>">
                        <span class="absolute -bottom-1 -right-1 text-xl leading-none"><?= $p['pos'] ?></span>
                    </div>
                </a>
                <div class="text-center">
                    <p class="font-bold text-gray-900 text-xs leading-tight"><?= e(mb_strimwidth($name, 0, 18, '…')) ?></p>
                    <?php if ($leader['rank_slug'] && $leader['rank_slug'] !== 'nuevo'): ?>
                    <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded-full mt-0.5"
                          style="background:<?= e($leader['rank_color']) ?>20;color:<?= e($leader['rank_color']) ?>">
                        <?= e($leader['rank_icon']) ?> <?= e($leader['rank_name']) ?>
                    </span>
                    <?php endif; ?>
                    <p class="text-xs font-extrabold <?= $p['text'] ?> mt-0.5"><?= number_format($leader['points']) ?> pts</p>
                </div>
                <!-- Podium block -->
                <div class="w-full <?= $p['height'] ?> <?= $p['bg'] ?> rounded-t-xl flex items-center justify-center border border-gray-200 border-b-0">
                    <span class="text-2xl font-black <?= $p['text'] ?> opacity-30"><?= $p['idx'] + 1 ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ── Full list ── -->
        <!-- Rank legend -->
        <div class="flex flex-wrap gap-2 mb-5 justify-center">
            <?php foreach (getRanks() as $r): ?>
            <?php if ($r['slug'] === 'nuevo') continue; ?>
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full border"
                  style="background:<?= e($r['badge_color']) ?>15;color:<?= e($r['badge_color']) ?>;border-color:<?= e($r['badge_color']) ?>30">
                <?= e($r['badge_icon']) ?> <?= e($r['name']) ?> · <?= number_format($r['min_points']) ?>+ pts
            </span>
            <?php endforeach; ?>
        </div>

        <div class="space-y-2">
            <?php foreach ($leaders as $i => $leader):
                $pos  = $i + 1;
                $name = !empty($leader['business_name']) ? $leader['business_name'] : $leader['full_name'];
                $isMe = $currentUser && (int)$currentUser['id'] === (int)$leader['id'];
                $medal = match($pos) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => '' };
            ?>
            <a href="/p/<?= e($leader['slug']) ?>"
               class="flex items-center gap-3 bg-white rounded-2xl px-4 py-3 border transition-all hover:shadow-md hover:border-brand-200 <?= $isMe ? 'border-brand-400 ring-2 ring-brand-100' : 'border-gray-100' ?>">
                <!-- Position -->
                <div class="w-8 text-center flex-shrink-0">
                    <?php if ($medal): ?>
                    <span class="text-xl"><?= $medal ?></span>
                    <?php else: ?>
                    <span class="text-sm font-bold text-gray-400">#<?= $pos ?></span>
                    <?php endif; ?>
                </div>

                <!-- Avatar -->
                <img src="<?= e(getAvatar($leader['avatar_url'], $leader['full_name'], '40')) ?>"
                     alt="" class="w-10 h-10 rounded-full object-cover flex-shrink-0 border border-gray-100">

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-semibold text-gray-900 text-sm truncate"><?= e($name) ?></span>
                        <?php if ($isMe): ?>
                        <span class="text-[10px] bg-brand-100 text-brand-700 font-bold px-1.5 py-0.5 rounded-full">Tú</span>
                        <?php endif; ?>
                        <?php if ($leader['rank_slug'] && $leader['rank_slug'] !== 'nuevo'): ?>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full flex-shrink-0"
                              style="background:<?= e($leader['rank_color']) ?>20;color:<?= e($leader['rank_color']) ?>">
                            <?= e($leader['rank_icon']) ?> <?= e($leader['rank_name']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5 flex-wrap">
                        <?php if ($leader['category_name']): ?>
                        <span><?= e($leader['category_name']) ?></span>
                        <?php endif; ?>
                        <?php if ($leader['city']): ?>
                        <span>· <?= e($leader['city']) ?></span>
                        <?php endif; ?>
                        <?php if ((int)$leader['direct_refs'] > 0): ?>
                        <span class="text-brand-500">· <?= (int)$leader['direct_refs'] ?> referidos</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Points -->
                <div class="text-right flex-shrink-0">
                    <div class="font-extrabold text-brand-700 text-base"><?= number_format($leader['points']) ?></div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wide">pts</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- CTA to join -->
        <?php if (!$currentUser): ?>
        <div class="mt-10 bg-brand-700 rounded-2xl p-6 text-white text-center">
            <p class="font-extrabold text-lg mb-1">¿Quieres aparecer aquí?</p>
            <p class="text-brand-200 text-sm mb-4">Crea tu perfil gratis, refiere colegas y sube al top.</p>
            <a href="/register.php?role=provider" class="inline-block bg-white text-brand-700 font-bold px-6 py-3 rounded-xl hover:bg-brand-50 transition-colors">
                Crear perfil gratis →
            </a>
        </div>
        <?php elseif ($currentUser['role'] === 'provider'): ?>
        <div class="mt-10 bg-white rounded-2xl p-5 border border-brand-200 text-center">
            <p class="font-semibold text-gray-900 mb-1">¿Quieres subir en el ranking?</p>
            <p class="text-sm text-gray-500 mb-3">Refiere colegas con tu link y gana puntos automáticamente.</p>
            <a href="/dashboard.php" class="btn-primary text-sm px-6">Ver mi link de referidos →</a>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
