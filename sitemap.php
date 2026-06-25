<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$now = date('Y-m-d');

// Provider profiles
$providers = DB::fetchAll("
    SELECT pp.slug, pp.updated_at
    FROM provider_profiles pp
    JOIN users u ON u.id = pp.user_id
    WHERE u.is_active = TRUE AND pp.slug IS NOT NULL
    ORDER BY pp.updated_at DESC
");

// Categories
$categories = DB::fetchAll("SELECT slug FROM categories WHERE slug IS NOT NULL ORDER BY name");

// Cities
$cities = DB::fetchAll("
    SELECT DISTINCT l.city, l.country
    FROM locations l
    JOIN provider_profiles pp ON pp.location_id = l.id
    JOIN users u ON u.id = pp.user_id
    WHERE u.is_active = TRUE
    ORDER BY l.city
");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static pages
$static = [
    ['/', '1.0', 'daily'],
    ['/search.php', '0.9', 'daily'],
    ['/leaderboard.php', '0.8', 'daily'],
    ['/register.php', '0.7', 'monthly'],
    ['/login.php', '0.5', 'monthly'],
];
foreach ($static as [$url, $pri, $freq]):
echo "  <url>
    <loc>" . APP_URL . e($url) . "</loc>
    <lastmod>{$now}</lastmod>
    <changefreq>{$freq}</changefreq>
    <priority>{$pri}</priority>
  </url>\n";
endforeach;

// Category search pages
foreach ($categories as $cat):
    if (!$cat['slug']) continue;
    echo "  <url>
    <loc>" . APP_URL . "/search.php?category=" . urlencode($cat['slug']) . "</loc>
    <lastmod>{$now}</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>\n";
endforeach;

// City search pages
foreach ($cities as $c):
    echo "  <url>
    <loc>" . APP_URL . "/search.php?city=" . urlencode($c['city']) . "</loc>
    <lastmod>{$now}</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>\n";
endforeach;

// Provider profiles
foreach ($providers as $p):
    $mod = $p['updated_at'] ? date('Y-m-d', strtotime($p['updated_at'])) : $now;
    echo "  <url>
    <loc>" . APP_URL . "/p/" . urlencode($p['slug']) . "</loc>
    <lastmod>{$mod}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>\n";
endforeach;

echo '</urlset>';
