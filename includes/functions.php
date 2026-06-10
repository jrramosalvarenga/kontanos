<?php
require_once __DIR__ . '/db.php';

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

function getCategories(): array {
    return DB::fetchAll("SELECT * FROM categories WHERE is_active = TRUE ORDER BY sort_order");
}

function getLocations(): array {
    return DB::fetchAll("SELECT * FROM locations WHERE is_active = TRUE ORDER BY country, state, city");
}

function getFeaturedProviders(int $limit = 8): array {
    return DB::fetchAll("
        SELECT pp.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
               l.city, l.state, l.country,
               u.email
        FROM provider_profiles pp
        JOIN users u ON u.id = pp.user_id
        LEFT JOIN categories c ON c.id = pp.category_id
        LEFT JOIN locations l ON l.id = pp.location_id
        WHERE pp.is_featured = TRUE AND u.is_active = TRUE
        ORDER BY pp.rating_avg DESC, pp.profile_views DESC
        LIMIT $1
    ", [$limit]);
}

function getProviderBySlug(string $slug): ?array {
    return DB::fetch("
        SELECT pp.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
               l.city, l.state, l.country,
               u.email
        FROM provider_profiles pp
        JOIN users u ON u.id = pp.user_id
        LEFT JOIN categories c ON c.id = pp.category_id
        LEFT JOIN locations l ON l.id = pp.location_id
        WHERE pp.slug = $1 AND u.is_active = TRUE
    ", [$slug]);
}

function getProviderServices(int $providerId): array {
    return DB::fetchAll("SELECT * FROM services WHERE provider_id = $1 AND is_active = TRUE ORDER BY id", [$providerId]);
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
    } elseif (!empty($filters['country'])) {
        $conditions[] = "l.country = $" . $i;
        $params[] = $filters['country'];
        $i++;
    }

    $where = implode(' AND ', $conditions);
    $order = "pp.is_featured DESC, pp.rating_avg DESC, pp.profile_views DESC";

    return DB::fetchAll("
        SELECT pp.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
               l.city, l.state, l.country
        FROM provider_profiles pp
        JOIN users u ON u.id = pp.user_id
        LEFT JOIN categories c ON c.id = pp.category_id
        LEFT JOIN locations l ON l.id = pp.location_id
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
