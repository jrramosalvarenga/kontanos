<?php
// ONE-TIME MIGRATION SCRIPT — DELETE AFTER USE
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';

$results = [];

$migrations = [
    "ALTER TABLE provider_profiles ADD COLUMN IF NOT EXISTS profile_type VARCHAR(20) NOT NULL DEFAULT 'personal'",
    "ALTER TABLE provider_profiles ADD COLUMN IF NOT EXISTS business_name VARCHAR(200)",
];

foreach ($migrations as $sql) {
    try {
        DB::query($sql, []);
        $results[] = ['ok' => true, 'sql' => $sql];
    } catch (Exception $e) {
        $results[] = ['ok' => false, 'sql' => $sql, 'error' => $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Migration</title>
<style>body{font-family:monospace;padding:2rem;background:#f9fafb}
.ok{color:#15803d;background:#f0fdf4;border:1px solid #bbf7d0;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:.75rem}
.err{color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:.75rem}
pre{margin:.25rem 0 0;font-size:.8rem;opacity:.7}</style></head>
<body>
<h2>Migration Results</h2>
<?php foreach ($results as $r): ?>
<div class="<?= $r['ok'] ? 'ok' : 'err' ?>">
    <?= $r['ok'] ? '✅ OK' : '❌ ERROR: ' . htmlspecialchars($r['error']) ?>
    <pre><?= htmlspecialchars($r['sql']) ?></pre>
</div>
<?php endforeach; ?>
<p style="color:#6b7280;margin-top:1.5rem;font-size:.875rem">
    ⚠️ <strong>Delete this file now:</strong> <code>run-migration.php</code>
</p>
</body></html>
