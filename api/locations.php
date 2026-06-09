<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$country = $_GET['country'] ?? '';

if ($country) {
    $rows = DB::fetchAll(
        "SELECT id, city, state FROM locations WHERE country = $1 AND is_active = TRUE ORDER BY city",
        [$country]
    );
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} else {
    $rows = DB::fetchAll(
        "SELECT DISTINCT country FROM locations WHERE is_active = TRUE ORDER BY country"
    );
    $countries = array_column($rows, 'country');
    echo json_encode($countries, JSON_UNESCAPED_UNICODE);
}
