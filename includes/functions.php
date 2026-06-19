<?php
require_once __DIR__ . '/db.php';

// Umbral para el badge/boost "Responde rápido": >=5 solicitudes, >=70% respondidas, promedio <24h
const RESPONSE_STATS_JOIN = "
    LEFT JOIN (
        SELECT
            provider_id,
            COUNT(*) AS total_requests,
            COUNT(*) FILTER (WHERE status IN ('replied', 'closed')) AS responded_requests,
            AVG(EXTRACT(EPOCH FROM (replied_at - created_at)) / 3600) FILTER (WHERE replied_at IS NOT NULL) AS avg_response_hours
        FROM contact_requests
        GROUP BY provider_id
    ) rs ON rs.provider_id = pp.id";

const RESPONSE_STATS_SELECT = "
    rs.total_requests, rs.responded_requests, rs.avg_response_hours,
    (
        COALESCE(rs.total_requests, 0) >= 5
        AND rs.responded_requests::float / rs.total_requests >= 0.7
        AND rs.avg_response_hours < 24
    ) AS fast_responder";

function saveAvatarUpload(array $file, int $userId): ?string {
    $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($mimeToExt[$mime])) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null;

    // ImgBB (external host — works on read-only filesystems like Render)
    $apiKey = getenv('IMGBB_API_KEY');
    if ($apiKey) {
        $ch = curl_init('https://api.imgbb.com/1/upload');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'key'   => $apiKey,
                'image' => base64_encode(file_get_contents($file['tmp_name'])),
                'name'  => 'avatar_u' . $userId,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($resp, true);
        return $data['data']['url'] ?? null;
    }

    // Local fallback (development only — will silently fail on read-only hosts)
    $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
    if (!is_writable($uploadDir)) return null;
    foreach (glob($uploadDir . 'u' . $userId . '_*') ?: [] as $old) @unlink($old);
    $filename = 'u' . $userId . '_' . time() . '.' . $mimeToExt[$mime];
    if (!@move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) return null;
    return '/assets/uploads/avatars/' . $filename;
}

function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return $text;
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function e(?string $str): string {
    return h($str ?? '');
}

function httpPost(string $url, array $data): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    return json_decode($body ?: '{}', true) ?: [];
}

function httpGet(string $url, array $headers = []): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => array_merge(['Accept: application/json'], $headers),
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    return json_decode($body ?: '{}', true) ?: [];
}

function getCategories(): array {
    return DB::fetchAll("SELECT * FROM categories WHERE is_active = TRUE ORDER BY sort_order");
}

function getLocations(): array {
    return DB::fetchAll("SELECT * FROM locations WHERE is_active = TRUE ORDER BY country, state, city");
}

function getFeaturedProviders(int $limit = 8): array {
    return DB::fetchAll("
        SELECT pp.*, c.name as category_name, c.icon as category_icon, c.color as category_color, c.slug as category_slug,
               l.city, l.state, l.country,
               u.email,
               rk.slug as rank_slug, rk.name as rank_name, rk.badge_icon as rank_icon, rk.badge_color as rank_color,
               " . RESPONSE_STATS_SELECT . "
        FROM provider_profiles pp
        JOIN users u ON u.id = pp.user_id
        LEFT JOIN categories c ON c.id = pp.category_id
        LEFT JOIN locations l ON l.id = pp.location_id
        LEFT JOIN ranks rk ON rk.id = u.rank_id
        " . RESPONSE_STATS_JOIN . "
        WHERE pp.is_featured = TRUE AND u.is_active = TRUE
        ORDER BY pp.admin_priority DESC, COALESCE(rk.search_boost, 0) DESC, fast_responder DESC, pp.rating_avg DESC, pp.profile_views DESC
        LIMIT $1
    ", [$limit]);
}

function getProviderBySlug(string $slug): ?array {
    return DB::fetch("
        SELECT pp.*, c.name as category_name, c.icon as category_icon, c.color as category_color, c.slug as category_slug,
               l.city, l.state, l.country,
               u.email,
               rk.slug as rank_slug, rk.name as rank_name, rk.badge_icon as rank_icon, rk.badge_color as rank_color,
               " . RESPONSE_STATS_SELECT . "
        FROM provider_profiles pp
        JOIN users u ON u.id = pp.user_id
        LEFT JOIN categories c ON c.id = pp.category_id
        LEFT JOIN locations l ON l.id = pp.location_id
        LEFT JOIN ranks rk ON rk.id = u.rank_id
        " . RESPONSE_STATS_JOIN . "
        WHERE pp.slug = $1 AND u.is_active = TRUE
    ", [$slug]);
}

function getProviderServices(int $providerId, bool $onlyActive = true): array {
    $sql = "SELECT * FROM services WHERE provider_id = $1" . ($onlyActive ? " AND activo = TRUE" : "") . " ORDER BY orden, id";
    return DB::fetchAll($sql, [$providerId]);
}

function getProviderProducts(int $providerId, bool $onlyActive = true): array {
    $sql = "SELECT * FROM products WHERE provider_id = $1" . ($onlyActive ? " AND activo = TRUE" : "") . " ORDER BY orden, id";
    return DB::fetchAll($sql, [$providerId]);
}

/**
 * Sube una imagen de catálogo (producto o servicio). Misma estrategia que saveAvatarUpload:
 * ImgBB en producción (filesystem de Render es de solo lectura), fallback local en desarrollo.
 */
function saveCatalogUpload(array $file, string $kind, int $providerId): ?string {
    $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($mimeToExt[$mime])) return null;
    if ($file['size'] > MAX_FILE_SIZE) return null;

    $name = $kind . '_p' . $providerId . '_' . time() . '_' . random_int(1000, 9999);

    $apiKey = getenv('IMGBB_API_KEY');
    if ($apiKey) {
        $ch = curl_init('https://api.imgbb.com/1/upload');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'key'   => $apiKey,
                'image' => base64_encode(file_get_contents($file['tmp_name'])),
                'name'  => $name,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($resp, true);
        return $data['data']['url'] ?? null;
    }

    $uploadDir = __DIR__ . '/../assets/uploads/catalog/';
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
    if (!is_writable($uploadDir)) return null;
    $filename = $name . '.' . $mimeToExt[$mime];
    if (!@move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) return null;
    return '/assets/uploads/catalog/' . $filename;
}

/** Elimina la imagen local previa de un item de catálogo (no-op si está en un host externo como ImgBB). */
function deleteCatalogUploadIfLocal(?string $imagePath): void {
    if (!$imagePath || !str_starts_with($imagePath, '/assets/uploads/catalog/')) return;
    $full = __DIR__ . '/..' . $imagePath;
    if (is_file($full)) @unlink($full);
}

function formatSimplePrice(?float $precio): string {
    if ($precio === null) return '';
    return '$' . number_format($precio, 2);
}

function getProviderReviews(int $providerId): array {
    return DB::fetchAll("SELECT * FROM reviews WHERE provider_id = $1 AND is_approved = TRUE ORDER BY created_at DESC", [$providerId]);
}

function getProviderPortfolio(int $providerId): array {
    return DB::fetchAll("SELECT * FROM portfolio_items WHERE provider_id = $1 ORDER BY sort_order, id", [$providerId]);
}

function searchProviders(array $filters): array {
    $conditions = ["u.is_active = TRUE"];
    $params = [];
    $i = 1;

    if (!empty($filters['q'])) {
        $conditions[] = "(pp.full_name ILIKE $" . $i . " OR pp.tagline ILIKE $" . $i . " OR pp.bio ILIKE $" . $i . ")";
        $params[] = '%' . $filters['q'] . '%';
        $i++;
    }
    if (!empty($filters['category'])) {
        $conditions[] = "c.slug = $" . $i;
        $params[] = $filters['category'];
        $i++;
    }
    if (!empty($filters['location'])) {
        $conditions[] = "l.slug = $" . $i;
        $params[] = $filters['location'];
        $i++;
    } else {
        if (!empty($filters['country'])) {
            $conditions[] = "l.country = $" . $i;
            $params[] = $filters['country'];
            $i++;
        }
        if (!empty($filters['region'])) {
            $conditions[] = "l.state = $" . $i;
            $params[] = $filters['region'];
            $i++;
        }
    }

    $where = implode(' AND ', $conditions);
    $order = "pp.admin_priority DESC, COALESCE(rk.search_boost, 0) DESC, fast_responder DESC, pp.is_featured DESC, pp.rating_avg DESC, pp.profile_views DESC";

    return DB::fetchAll("
        SELECT pp.*, c.name as category_name, c.icon as category_icon, c.color as category_color, c.slug as category_slug,
               l.city, l.state, l.country,
               rk.slug as rank_slug, rk.name as rank_name, rk.badge_icon as rank_icon, rk.badge_color as rank_color,
               " . RESPONSE_STATS_SELECT . "
        FROM provider_profiles pp
        JOIN users u ON u.id = pp.user_id
        LEFT JOIN categories c ON c.id = pp.category_id
        LEFT JOIN locations l ON l.id = pp.location_id
        LEFT JOIN ranks rk ON rk.id = u.rank_id
        " . RESPONSE_STATS_JOIN . "
        WHERE $where
        ORDER BY $order
        LIMIT 50
    ", $params);
}

function incrementProfileViews(int $providerId): void {
    DB::query("UPDATE provider_profiles SET profile_views = profile_views + 1 WHERE id = $1", [$providerId]);
}

function renderStars(float $rating, bool $showNum = true): string {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    $html = '<div class="flex items-center gap-1">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        } elseif ($i == $full + 1 && $half) {
            $html .= '<svg class="w-4 h-4 text-amber-400" viewBox="0 0 20 20"><defs><clipPath id="half"><rect x="0" y="0" width="10" height="20"/></clipPath></defs><path class="fill-current" clip-path="url(#half)" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/><path class="fill-gray-200" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" clip-path="url(#right)"/></svg>';
        } else {
            $html .= '<svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        }
    }
    if ($showNum && $rating > 0) {
        $html .= '<span class="text-sm text-gray-600 ml-1">' . number_format($rating, 1) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

function getAvatar(?string $url, string $name, string $size = '100'): string {
    if ($url) return $url;
    $initials = implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(explode(' ', $name), 0, 2)));
    return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&size=$size&background=15803d&color=ffffff&bold=true";
}

function formatPrice(?float $from, ?float $to, string $type, string $currency = 'USD'): string {
    if ($type === 'free_quote') return 'Cotización gratis';
    if ($type === 'negotiable') return 'A convenir';
    if (!$from) return '';
    $sym = $currency === 'USD' ? '$' : $currency . ' ';
    if ($from && $to && $from !== $to) return $sym . number_format($from, 0) . ' - ' . $sym . number_format($to, 0);
    return $sym . number_format($from, 0) . ($type === 'hourly' ? '/hr' : '');
}

function timeAgo(string $datetime): string {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return "hace {$diff->y} " . ($diff->y == 1 ? "año" : "años");
    if ($diff->m > 0) return "hace {$diff->m} " . ($diff->m == 1 ? "mes" : "meses");
    if ($diff->d > 0) return "hace {$diff->d} " . ($diff->d == 1 ? "día" : "días");
    if ($diff->h > 0) return "hace {$diff->h} " . ($diff->h == 1 ? "hora" : "horas");
    return "hace " . max(1, $diff->i) . " min";
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return DB::fetch("SELECT * FROM users WHERE id = $1", [$_SESSION['user_id']]);
}

function getOGImageUrl(array $provider): string {
    return $provider['avatar_url'] ?? getAvatar(null, $provider['full_name'], '400');
}

// ============================================================
// Sistema de referidos / puntos / rangos
// ============================================================

const REFERRAL_POINTS = [
    'provider' => ['l1' => 10, 'l2' => 3],
    'client'   => ['l1' => 2,  'l2' => 1],
];

function generateReferralCode(): string {
    do {
        $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $exists = DB::fetch("SELECT id FROM users WHERE referral_code = $1", [$code]);
    } while ($exists);
    return $code;
}

function getRanks(): array {
    return DB::fetchAll("SELECT * FROM ranks ORDER BY min_points");
}

function getRankById(int $id): ?array {
    return DB::fetch("SELECT * FROM ranks WHERE id = $1", [$id]);
}

function recalculateRank(int $userId): void {
    $user = DB::fetch("SELECT points FROM users WHERE id = $1", [$userId]);
    if (!$user) return;
    $rank = DB::fetch("SELECT id FROM ranks WHERE min_points <= $1 ORDER BY min_points DESC LIMIT 1", [$user['points']]);
    if ($rank) {
        DB::query("UPDATE users SET rank_id = $1 WHERE id = $2", [$rank['id'], $userId]);
    }
}

function addPoints(int $userId, int $points, int $level, int $sourceUserId, string $reason): void {
    DB::query("UPDATE users SET points = points + $1 WHERE id = $2", [$points, $userId]);
    DB::query("
        INSERT INTO point_transactions (user_id, points, level, source_user_id, reason)
        VALUES ($1, $2, $3, $4, $5)
    ", [$userId, $points, $level, $sourceUserId, $reason]);
    recalculateRank($userId);
}

function awardReferralPoints(int $newUserId, string $newUserRole): void {
    $points = REFERRAL_POINTS[$newUserRole] ?? REFERRAL_POINTS['client'];

    $newUser = DB::fetch("SELECT referred_by FROM users WHERE id = $1", [$newUserId]);
    $level1Id = $newUser['referred_by'] ?? null;
    if (!$level1Id) return;

    $roleLabel = $newUserRole === 'provider' ? 'profesional' : 'cliente';
    addPoints($level1Id, $points['l1'], 1, $newUserId, "Referido directo: nuevo $roleLabel");

    $level1User = DB::fetch("SELECT referred_by FROM users WHERE id = $1", [$level1Id]);
    $level2Id = $level1User['referred_by'] ?? null;
    if ($level2Id) {
        addPoints($level2Id, $points['l2'], 2, $newUserId, "Referido de tu referido: nuevo $roleLabel");
    }
}

function renderRankBadge(?array $rank, bool $small = false): string {
    if (!$rank || $rank['slug'] === 'nuevo') return '';
    $size = $small ? 'text-[10px] px-1.5 py-0.5' : 'text-xs px-2 py-0.5';
    return '<span class="inline-flex items-center gap-1 rounded-full font-bold ' . $size . '" '
         . 'style="background-color:' . e($rank['badge_color']) . '20; color:' . e($rank['badge_color']) . '">'
         . e($rank['badge_icon']) . ' ' . e($rank['name'])
         . '</span>';
}

function getReferralStats(int $userId): array {
    $user = DB::fetch("
        SELECT u.points, u.referral_code, r.* , r.id as rank_id
        FROM users u
        JOIN ranks r ON r.id = u.rank_id
        WHERE u.id = $1
    ", [$userId]);

    if (empty($user['referral_code'])) {
        $user['referral_code'] = generateReferralCode();
        DB::query("UPDATE users SET referral_code = $1 WHERE id = $2", [$user['referral_code'], $userId]);
    }

    $nextRank = DB::fetch("
        SELECT * FROM ranks WHERE min_points > $1 ORDER BY min_points ASC LIMIT 1
    ", [$user['points']]);

    $directCount = (int)DB::fetch("SELECT COUNT(*) as c FROM users WHERE referred_by = $1", [$userId])['c'];
    $indirectCount = (int)DB::fetch("
        SELECT COUNT(*) as c FROM users WHERE referred_by IN (SELECT id FROM users WHERE referred_by = $1)
    ", [$userId])['c'];

    return [
        'points'         => (int)$user['points'],
        'referral_code'  => $user['referral_code'],
        'rank'           => $user,
        'next_rank'      => $nextRank,
        'points_to_next' => $nextRank ? max(0, (int)$nextRank['min_points'] - (int)$user['points']) : 0,
        'direct_count'   => $directCount,
        'indirect_count' => $indirectCount,
    ];
}

function getReferralNetwork(int $userId): array {
    $direct = DB::fetchAll("
        SELECT u.id, u.email, u.role, u.created_at,
               COALESCE(pp.full_name, split_part(u.email, '@', 1)) as display_name,
               (SELECT COUNT(*) FROM users u2 WHERE u2.referred_by = u.id) as referral_count
        FROM users u
        LEFT JOIN provider_profiles pp ON pp.user_id = u.id
        WHERE u.referred_by = $1
        ORDER BY u.created_at DESC
    ", [$userId]);
    return $direct;
}

// ============================================================
// Email
// ============================================================

function sendMail(string $to, string $toName, string $subject, string $html): bool {
    $apiKey = getenv('RESEND_API_KEY');

    if ($apiKey) {
        $from = 'Kontactanos <notificaciones@' . APP_DOMAIN . '>';
        $ch   = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'from'    => $from,
                'to'      => ["$toName <$to>"],
                'subject' => $subject,
                'html'    => $html,
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 8,
        ]);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_exec($ch);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    // Fallback PHP mail()
    $from    = 'notificaciones@' . APP_DOMAIN;
    $headers = implode("\r\n", [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Kontactanos <' . $from . '>',
        'Reply-To: ' . $from,
    ]);
    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $headers);
}

function buildContactNotificationEmail(string $providerFirstName, array $req, string $inboxUrl): string {
    $name    = htmlspecialchars($req['requester_name'] ?? 'Anónimo', ENT_QUOTES, 'UTF-8');
    $email   = htmlspecialchars($req['requester_email'] ?? '', ENT_QUOTES, 'UTF-8');
    $phone   = $req['requester_phone']
        ? '<p style="margin:3px 0 0;color:#6b7280;font-size:13px;">📱 ' . htmlspecialchars($req['requester_phone'], ENT_QUOTES, 'UTF-8') . '</p>'
        : '';
    $message = nl2br(htmlspecialchars($req['message'] ?? '', ENT_QUOTES, 'UTF-8'));
    $pName   = htmlspecialchars($providerFirstName, ENT_QUOTES, 'UTF-8');
    $iUrl    = htmlspecialchars($inboxUrl, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:24px 16px;background:#f3f4f6;font-family:ui-sans-serif,system-ui,-apple-system,sans-serif;">
<div style="max-width:520px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08);">
  <div style="background:#15803d;padding:28px 32px;">
    <h1 style="margin:0;color:#fff;font-size:20px;font-weight:700;line-height:1.3;">Tienes un nuevo mensaje</h1>
    <p style="margin:6px 0 0;color:#bbf7d0;font-size:14px;">Hola {$pName}, alguien quiere contactarte a través de Kontactanos</p>
  </div>
  <div style="padding:28px 32px;">
    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:16px 20px;margin-bottom:24px;">
      <p style="margin:0 0 2px;font-weight:700;color:#111827;font-size:15px;">{$name}</p>
      <p style="margin:3px 0 0;color:#6b7280;font-size:13px;">✉️ <a href="mailto:{$email}" style="color:#15803d;text-decoration:none;">{$email}</a></p>
      {$phone}
      <hr style="border:none;border-top:1px solid #e5e7eb;margin:14px 0;">
      <p style="margin:0;color:#374151;font-size:14px;line-height:1.65;">{$message}</p>
    </div>
    <a href="{$iUrl}" style="display:inline-block;background:#15803d;color:#fff;text-decoration:none;padding:13px 26px;border-radius:10px;font-weight:700;font-size:14px;">Ver en mi bandeja →</a>
    <p style="color:#9ca3af;font-size:12px;margin-top:24px;line-height:1.6;">
      Puedes responder directamente a <a href="mailto:{$email}" style="color:#15803d;">{$email}</a> o visitar tu bandeja en Kontactanos.<br>
      <a href="{$iUrl}" style="color:#9ca3af;">{$iUrl}</a>
    </p>
  </div>
</div>
</body>
</html>
HTML;
}

function buildBroadcastEmail(string $title, string $body, ?string $ctaUrl, ?string $ctaLabel): string {
    $t    = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $b    = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
    $cta  = '';
    if ($ctaUrl) {
        $url   = htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($ctaLabel ?: 'Ver más', ENT_QUOTES, 'UTF-8');
        $cta   = "<a href=\"{$url}\" style=\"display:inline-block;background:#15803d;color:#fff;text-decoration:none;padding:13px 26px;border-radius:10px;font-weight:700;font-size:14px;\">{$label} →</a>";
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:24px 16px;background:#f3f4f6;font-family:ui-sans-serif,system-ui,-apple-system,sans-serif;">
<div style="max-width:520px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08);">
  <div style="background:#15803d;padding:28px 32px;">
    <h1 style="margin:0;color:#fff;font-size:20px;font-weight:700;line-height:1.3;">{$t}</h1>
  </div>
  <div style="padding:28px 32px;">
    <p style="margin:0 0 24px;color:#374151;font-size:14px;line-height:1.65;">{$b}</p>
    {$cta}
  </div>
</div>
</body>
</html>
HTML;
}

/**
 * Envía un email a todos los usuarios de un rol (o a todos), opcionalmente con un botón de acción.
 * Devuelve el número de envíos intentados.
 */
function sendEmailBroadcast(string $title, string $body, ?string $ctaUrl, ?string $ctaLabel, ?string $role = null): int {
    $sql = "SELECT email, COALESCE(pp.full_name, split_part(u.email, '@', 1)) AS name
            FROM users u
            LEFT JOIN provider_profiles pp ON pp.user_id = u.id
            WHERE u.is_active = TRUE AND u.email IS NOT NULL AND u.email != ''";
    $params = [];
    if ($role) {
        $sql .= " AND u.role = $1";
        $params[] = $role;
    }

    $users = DB::fetchAll($sql, $params);
    $html  = buildBroadcastEmail($title, $body, $ctaUrl, $ctaLabel);

    foreach ($users as $u) {
        sendMail($u['email'], $u['name'], $title, $html);
    }

    return count($users);
}

function getPendingContactsCount(int $userId): int {
    $row = DB::fetch("
        SELECT COUNT(cr.id) AS cnt
        FROM contact_requests cr
        JOIN provider_profiles pp ON pp.id = cr.provider_id
        WHERE pp.user_id = $1 AND cr.status = 'pending'
    ", [$userId]);
    return (int)($row['cnt'] ?? 0);
}

function getActiveAds(string $position): array {
    return DB::fetchAll("
        SELECT * FROM ads
        WHERE position = $1
          AND is_active = TRUE
          AND (starts_at IS NULL OR starts_at <= NOW())
          AND (ends_at IS NULL OR ends_at >= NOW())
        ORDER BY sort_order, id
    ", [$position]);
}

function renderAdBanner(array $ad): string {
    DB::query("UPDATE ads SET impressions = impressions + 1 WHERE id = $1", [$ad['id']]);

    $img = '<img src="' . e($ad['image_url']) . '" alt="' . e($ad['title']) . '" class="w-full rounded-2xl object-cover">';

    if (!empty($ad['link_url'])) {
        return '<a href="/ads-click.php?id=' . (int)$ad['id'] . '" target="_blank" rel="noopener" class="block mb-6">' . $img . '</a>';
    }

    return '<div class="mb-6">' . $img . '</div>';
}

function renderProviderCard(array $pro): string {
    $avatar = getAvatar($pro['avatar_url'] ?? null, $pro['full_name'], '200');
    $stars  = renderStars((float)($pro['rating_avg'] ?? 0));
    $loc    = trim(($pro['city'] ?? '') . ', ' . ($pro['country'] ?? ''), ', ');

    $rankBadge = '';
    if (!empty($pro['rank_slug']) && $pro['rank_slug'] !== 'nuevo') {
        $rankBadge = renderRankBadge([
            'slug'       => $pro['rank_slug'],
            'name'       => $pro['rank_name'],
            'badge_icon' => $pro['rank_icon'],
            'badge_color'=> $pro['rank_color'],
        ], true);
    }

    ob_start();
    ?>
    <div class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
        <a href="/p/<?= e($pro['slug']) ?>" class="flex flex-col flex-1">
            <?php
            $catColor = $pro['category_color'] ?? '#15803d';
            $catSlug  = $pro['category_slug'] ?? '';
            ?>
            <div class="relative h-32 sm:h-40 overflow-hidden" style="background-color: <?= e($catColor) ?>">
                <?php if (!empty($pro['cover_url'])): ?>
                    <img src="<?= e($pro['cover_url']) ?>" alt="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                <?php else: ?>
                    <img src="<?= e(getCategoryImage($catSlug)) ?>" alt=""
                         class="w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0" style="background: linear-gradient(135deg, <?= e($catColor) ?>cc 0%, <?= e($catColor) ?>77 50%, transparent 100%)"></div>
                <?php endif; ?>
                <?php if ($pro['is_featured']): ?>
                <span class="absolute top-3 right-3 bg-amber-400 text-amber-900 text-xs font-bold px-2 py-0.5 rounded-full">Destacado</span>
                <?php endif; ?>
                <?php if ($pro['is_verified']): ?>
                <span class="absolute top-3 left-3 bg-white/20 backdrop-blur-sm text-white text-xs font-medium px-2 py-0.5 rounded-full border border-white/30">✓ Verificado</span>
                <?php endif; ?>
            </div>
            <div class="flex flex-col flex-1 p-4 sm:p-5 -mt-8 relative">
                <img src="<?= e($avatar) ?>" alt="<?= e($pro['full_name']) ?>"
                     class="w-16 h-16 rounded-2xl object-cover border-4 border-white shadow-md mb-3">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h3 class="font-bold text-gray-900 text-base leading-tight group-hover:text-brand-700 transition-colors"><?= e($pro['full_name']) ?></h3>
                    <?php if ($rankBadge): ?>
                    <div class="flex-shrink-0 mt-0.5"><?= $rankBadge ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($pro['tagline']): ?>
                <p class="text-gray-500 text-xs mb-2 line-clamp-2"><?= e($pro['tagline']) ?></p>
                <?php endif; ?>
                <div class="flex items-center gap-2 mb-3 flex-wrap">
                    <?= $stars ?>
                    <?php if ($pro['rating_count'] > 0): ?>
                    <span class="text-xs text-gray-400">(<?= (int)$pro['rating_count'] ?>)</span>
                    <?php endif; ?>
                    <?php if (!empty($pro['fast_responder'])): ?>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-blue-50 text-blue-700 flex items-center gap-1">
                        ⚡ Responde rápido
                    </span>
                    <?php endif; ?>
                </div>
                <div class="mt-auto flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1 text-xs text-gray-500 min-w-0">
                        <svg class="w-3.5 h-3.5 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span class="truncate"><?= e($loc ?: 'N/A') ?></span>
                    </div>
                    <?php if ($pro['category_name']): ?>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium flex-shrink-0" style="background-color: <?= e($pro['category_color'] ?? '#15803d') ?>20; color: <?= e($pro['category_color'] ?? '#15803d') ?>">
                        <?= e($pro['category_name']) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php if (!empty($pro['whatsapp'])): ?>
        <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $pro['whatsapp'])) ?>" target="_blank" rel="noopener"
           class="flex items-center justify-center gap-1.5 bg-green-50 text-green-700 text-xs font-bold py-2.5 hover:bg-green-100 transition-colors border-t border-gray-100">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0012.04 2zm0 1.67c2.2 0 4.27.86 5.82 2.42a8.183 8.183 0 012.41 5.82c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.188 8.188 0 01-1.26-4.38c0-4.54 3.7-8.23 8.26-8.23z"/></svg>
            Contactar por WhatsApp
        </a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Unsplash category cover images (IDs hardcodeados de fotos libres)
function getCategoryImage(string $slug): string {
    $map = [
        'construccion-hogar'       => 'photo-1503387762-592deb58ef4e',
        'tecnologia-it'            => 'photo-1518770660439-4636190af475',
        'salud-bienestar'          => 'photo-1505751172876-fa1923c5c528',
        'educacion-clases'         => 'photo-1580582932707-520aed937b7b',
        'legal-finanzas'           => 'photo-1450101499163-c8848c66ca85',
        'eventos-entretenimiento'  => 'photo-1492684223066-81342ee5ff30',
        'belleza-cuidado'          => 'photo-1522337360788-8b13dee7a37e',
        'transporte-logistica'     => 'photo-1586528116311-ad8dd3c8310d',
        'reparaciones-mantenimiento' => 'photo-1581091226825-a6a2a5aee158',
        'diseno-creatividad'       => 'photo-1558655146-9f40138edfeb',
        'gastronomia'              => 'photo-1414235077428-338989a2e8c0',
        'mascotas'                 => 'photo-1587300003388-59208cc962cb',
    ];
    $id = $map[$slug] ?? 'photo-1460925895917-afdab827c52f';
    return "https://images.unsplash.com/$id?auto=format&fit=crop&w=600&h=400&q=80";
}
