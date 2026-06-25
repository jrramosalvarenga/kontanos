<?php
// Standalone — no includes to prevent any stray HTML injection
$dbUrl = getenv('DATABASE_URL');
if ($dbUrl) {
    $p   = parse_url($dbUrl);
    $dsn = 'pgsql:host=' . $p['host'] . ';port=' . ($p['port'] ?? 5432) . ';dbname=' . ltrim($p['path'], '/');
    $pdo = new PDO($dsn, $p['user'], $p['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} else {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=kontactanos', 'postgres', 'Olanchano.3', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
}

$appUrl = rtrim(getenv('APP_URL') ?: 'https://kontanos.com', '/');
$now    = date('Y-m-d');

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('X-Robots-Tag: noindex');

$providers  = $pdo->query("SELECT pp.slug, pp.updated_at FROM provider_profiles pp JOIN users u ON u.id = pp.user_id WHERE u.is_active = TRUE AND pp.slug IS NOT NULL ORDER BY pp.updated_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT slug FROM categories WHERE slug IS NOT NULL ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$cities     = $pdo->query("SELECT DISTINCT l.city FROM locations l JOIN provider_profiles pp ON pp.location_id = l.id JOIN users u ON u.id = pp.user_id WHERE u.is_active = TRUE ORDER BY l.city")->fetchAll(PDO::FETCH_ASSOC);

$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ([
    ['/', '1.0', 'daily'],
    ['/search.php', '0.9', 'daily'],
    ['/leaderboard.php', '0.8', 'daily'],
    ['/register.php', '0.7', 'monthly'],
    ['/login.php', '0.5', 'monthly'],
] as [$url, $pri, $freq]) {
    $xml .= "  <url><loc>{$appUrl}" . htmlspecialchars($url, ENT_XML1) . "</loc><lastmod>{$now}</lastmod><changefreq>{$freq}</changefreq><priority>{$pri}</priority></url>\n";
}

foreach ($categories as $cat) {
    $xml .= "  <url><loc>{$appUrl}/search.php?category=" . urlencode($cat['slug']) . "</loc><lastmod>{$now}</lastmod><changefreq>daily</changefreq><priority>0.8</priority></url>\n";
}

foreach ($cities as $c) {
    $xml .= "  <url><loc>{$appUrl}/search.php?city=" . urlencode($c['city']) . "</loc><lastmod>{$now}</lastmod><changefreq>daily</changefreq><priority>0.8</priority></url>\n";
}

foreach ($providers as $p) {
    $mod  = $p['updated_at'] ? date('Y-m-d', strtotime($p['updated_at'])) : $now;
    $xml .= "  <url><loc>{$appUrl}/p/" . urlencode($p['slug']) . "</loc><lastmod>{$mod}</lastmod><changefreq>weekly</changefreq><priority>0.9</priority></url>\n";
}

$xml .= '</urlset>';

echo $xml;
