<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/push.php';

requireProvider();
$user    = currentUser();
$profile = DB::fetch("SELECT id, full_name, slug FROM provider_profiles WHERE user_id = $1", [$user['id']]);

if (!$profile) {
    header('Location: /edit-profile.php');
    exit;
}

$providerId = (int)$profile['id'];

// Handle status update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $contactId = (int)($_POST['contact_id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? '';
    if ($contactId && in_array($newStatus, ['read', 'replied', 'closed', 'pending'])) {
        notifyClientIfReplied($contactId, $newStatus);
        DB::query(
            "UPDATE contact_requests SET status = $1 WHERE id = $2 AND provider_id = $3",
            [$newStatus, $contactId, $providerId]
        );
    }
    $qs = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('Location: /inbox.php' . $qs);
    exit;
}

// Filter
$validTabs = ['pending', 'all', 'closed'];
$tab = in_array($_GET['tab'] ?? '', $validTabs) ? $_GET['tab'] : 'pending';

if ($tab === 'pending') {
    $contacts = DB::fetchAll(
        "SELECT * FROM contact_requests WHERE provider_id = $1 AND status = 'pending' ORDER BY created_at DESC",
        [$providerId]
    );
} elseif ($tab === 'closed') {
    $contacts = DB::fetchAll(
        "SELECT * FROM contact_requests WHERE provider_id = $1 AND status = 'closed' ORDER BY created_at DESC",
        [$providerId]
    );
} else {
    $contacts = DB::fetchAll(
        "SELECT * FROM contact_requests WHERE provider_id = $1 ORDER BY created_at DESC",
        [$providerId]
    );
}

// Counts for tabs
$countPending = (int)(DB::fetch("SELECT COUNT(*) AS c FROM contact_requests WHERE provider_id = $1 AND status = 'pending'", [$providerId])['c'] ?? 0);
$countAll     = (int)(DB::fetch("SELECT COUNT(*) AS c FROM contact_requests WHERE provider_id = $1", [$providerId])['c'] ?? 0);
$countClosed  = (int)(DB::fetch("SELECT COUNT(*) AS c FROM contact_requests WHERE provider_id = $1 AND status = 'closed'", [$providerId])['c'] ?? 0);

$statusLabels = [
    'pending'  => ['label' => 'Nuevo',       'class' => 'bg-green-100 text-green-800'],
    'read'     => ['label' => 'Leído',        'class' => 'bg-gray-100 text-gray-600'],
    'replied'  => ['label' => 'Contestado',   'class' => 'bg-blue-100 text-blue-700'],
    'closed'   => ['label' => 'Archivado',    'class' => 'bg-gray-100 text-gray-500'],
];

$pageTitle = 'Bandeja de solicitudes | Kontactanos';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="/dashboard.php" class="text-sm text-brand-600 hover:text-brand-800 flex items-center gap-1 mb-1 transition-colors">
                ← Volver al panel
            </a>
            <h1 class="text-2xl font-extrabold text-gray-900 flex items-center gap-3">
                Bandeja de solicitudes
                <?php if ($countPending > 0): ?>
                <span class="bg-brand-600 text-white text-sm font-bold rounded-full px-2.5 py-0.5"><?= $countPending ?></span>
                <?php endif; ?>
            </h1>
        </div>
        <?php if ($profile): ?>
        <a href="/p/<?= e($profile['slug']) ?>" target="_blank"
           class="text-sm text-brand-700 border border-brand-200 hover:bg-brand-50 px-4 py-2 rounded-xl transition-colors font-medium hidden sm:inline-flex items-center gap-1.5">
            Ver perfil
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
        <?php endif; ?>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 mb-6">
        <?php
        $tabs = [
            'pending' => ['label' => 'Nuevos', 'count' => $countPending],
            'all'     => ['label' => 'Todos',  'count' => $countAll],
            'closed'  => ['label' => 'Archivados', 'count' => $countClosed],
        ];
        foreach ($tabs as $key => $t):
            $active = $tab === $key;
        ?>
        <a href="/inbox.php?tab=<?= $key ?>"
           class="flex-1 text-center text-sm font-semibold px-4 py-2 rounded-lg transition-all <?= $active ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">
            <?= e($t['label']) ?>
            <?php if ($t['count'] > 0): ?>
            <span class="ml-1 <?= $active ? 'text-brand-600' : 'text-gray-400' ?> font-bold"><?= $t['count'] ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Contact list -->
    <?php if (empty($contacts)): ?>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>
        <?php if ($tab === 'pending'): ?>
        <p class="text-gray-700 font-semibold mb-1">Sin mensajes nuevos</p>
        <p class="text-gray-400 text-sm">Cuando alguien te contacte desde tu perfil, aparecerá aquí.</p>
        <?php elseif ($tab === 'closed'): ?>
        <p class="text-gray-700 font-semibold mb-1">Sin mensajes archivados</p>
        <p class="text-gray-400 text-sm">Los mensajes que archives aparecerán aquí.</p>
        <?php else: ?>
        <p class="text-gray-700 font-semibold mb-1">Sin mensajes todavía</p>
        <p class="text-gray-400 text-sm">Comparte tu perfil para empezar a recibir solicitudes.</p>
        <a href="javascript:void(0)" onclick="copyToClipboard('<?= e(APP_URL . '/p/' . $profile['slug']) ?>', '¡Enlace copiado!')"
           class="mt-4 inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
            Copiar enlace de mi perfil
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($contacts as $c):
            $sl    = $statusLabels[$c['status']] ?? $statusLabels['read'];
            $phone = preg_replace('/\D/', '', $c['requester_phone'] ?? '');
            $waUrl = $phone ? 'https://wa.me/' . $phone : null;
        ?>
        <div class="bg-white rounded-2xl border <?= $c['status'] === 'pending' ? 'border-brand-200 shadow-md' : 'border-gray-100 shadow-sm' ?> p-5">
            <!-- Top row -->
            <div class="flex items-start gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0 font-bold text-gray-600 text-sm">
                    <?= strtoupper(mb_substr($c['requester_name'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-gray-900 text-sm"><?= e($c['requester_name'] ?? 'Anónimo') ?></span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $sl['class'] ?>"><?= $sl['label'] ?></span>
                        <span class="text-xs text-gray-400 ml-auto"><?= timeAgo($c['created_at']) ?></span>
                    </div>
                    <div class="flex flex-wrap gap-3 mt-0.5">
                        <?php if ($c['requester_email']): ?>
                        <a href="mailto:<?= e($c['requester_email']) ?>"
                           class="text-xs text-brand-600 hover:text-brand-800 transition-colors truncate">
                            <?= e($c['requester_email']) ?>
                        </a>
                        <?php endif; ?>
                        <?php if ($c['requester_phone']): ?>
                        <span class="text-xs text-gray-500"><?= e($c['requester_phone']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Message -->
            <div class="bg-gray-50 rounded-xl px-4 py-3 mb-4">
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap"><?= e($c['message'] ?? '') ?></p>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-2">
                <!-- Reply buttons -->
                <?php if ($c['requester_email']): ?>
                <a href="mailto:<?= e($c['requester_email']) ?>?subject=<?= urlencode('Re: tu mensaje en Kontactanos') ?>"
                   class="inline-flex items-center gap-1.5 bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Responder por email
                </a>
                <?php endif; ?>

                <?php if ($waUrl): ?>
                <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    WhatsApp
                </a>
                <?php endif; ?>

                <!-- Status actions -->
                <div class="ml-auto flex gap-1.5">
                    <?php if ($c['status'] === 'pending'): ?>
                    <form method="POST">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
                        <input type="hidden" name="new_status" value="read">
                        <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-2.5 py-1.5 rounded-lg transition-colors">
                            Marcar leído
                        </button>
                    </form>
                    <?php endif; ?>

                    <?php if (in_array($c['status'], ['pending', 'read'])): ?>
                    <form method="POST">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
                        <input type="hidden" name="new_status" value="replied">
                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 border border-blue-200 hover:border-blue-300 px-2.5 py-1.5 rounded-lg transition-colors">
                            Contestado
                        </button>
                    </form>
                    <?php endif; ?>

                    <?php if ($c['status'] !== 'closed'): ?>
                    <form method="POST">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
                        <input type="hidden" name="new_status" value="closed">
                        <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 hover:border-gray-300 px-2.5 py-1.5 rounded-lg transition-colors">
                            Archivar
                        </button>
                    </form>
                    <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
                        <input type="hidden" name="new_status" value="pending">
                        <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 hover:border-gray-300 px-2.5 py-1.5 rounded-lg transition-colors">
                            Desarchivar
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
