<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireProvider();
$user    = currentUser();
$profile = DB::fetch("SELECT * FROM provider_profiles WHERE user_id = $1", [$user['id']]);

if (!$profile) {
    header('Location: /edit-profile.php');
    exit;
}

$items = DB::fetchAll(
    "SELECT * FROM portfolio_items WHERE provider_id = $1 ORDER BY sort_order, created_at DESC",
    [$profile['id']]
);

$pageTitle = 'Mi Portafolio | Kontactanos';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="/dashboard.php" class="text-sm text-brand-600 hover:text-brand-800 flex items-center gap-1 mb-1 transition-colors">
                ← Volver al panel
            </a>
            <h1 class="text-2xl font-extrabold text-gray-900">Fotos de mi trabajo</h1>
            <p class="text-sm text-gray-500 mt-0.5">Muestra tu trabajo a tus futuros clientes · máx. 20 fotos</p>
        </div>
        <?php if ($profile): ?>
        <a href="/p/<?= e($profile['slug']) ?>#portfolio" target="_blank"
           class="hidden sm:flex items-center gap-1.5 text-sm text-brand-700 border border-brand-200 hover:bg-brand-50 px-4 py-2 rounded-xl transition-colors font-medium">
            Ver en mi perfil
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
        <?php endif; ?>
    </div>

    <!-- Add photo form -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6"
         x-data="{ previewUrl: '', title: '', desc: '' }">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2.5">
            <span class="w-7 h-7 bg-brand-600 text-white rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </span>
            <h2 class="font-bold text-gray-900">Agregar foto</h2>
        </div>
        <form method="POST" action="/portfolio-action.php" class="p-6 space-y-4">
            <input type="hidden" name="_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="add">

            <div class="flex gap-4 items-start">
                <!-- Preview box -->
                <div class="flex-shrink-0 w-24 h-24 rounded-xl bg-gray-100 border-2 border-dashed border-gray-200 overflow-hidden flex items-center justify-center">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" class="w-full h-full object-cover" @error="previewUrl = ''">
                    </template>
                    <template x-if="!previewUrl">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </template>
                </div>
                <div class="flex-1 space-y-3">
                    <div>
                        <label class="form-label">URL de la imagen <span class="text-red-400">*</span></label>
                        <input type="url" name="image_url" class="form-input" required
                               x-model="previewUrl"
                               placeholder="https://ejemplo.com/foto.jpg">
                        <p class="text-xs text-gray-400 mt-1">
                            Sube tu foto a <a href="https://imgur.com" target="_blank" class="text-brand-600 hover:underline">imgur.com</a>,
                            <a href="https://postimages.org" target="_blank" class="text-brand-600 hover:underline">postimages.org</a>
                            o Google Drive y copia el enlace directo.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Título <span class="text-gray-400 font-normal text-xs">(opcional)</span></label>
                    <input type="text" name="title" class="form-input"
                           x-model="title" placeholder="Ej: Instalación eléctrica residencial" maxlength="200">
                </div>
                <div>
                    <label class="form-label">Descripción <span class="text-gray-400 font-normal text-xs">(opcional)</span></label>
                    <input type="text" name="description" class="form-input"
                           x-model="desc" placeholder="Breve descripción del trabajo" maxlength="300">
                </div>
            </div>

            <button type="submit"
                    :disabled="!previewUrl"
                    class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                Agregar foto al portafolio
            </button>
        </form>
    </div>

    <!-- Current photos -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
            <h2 class="font-bold text-gray-900">Mis fotos (<?= count($items) ?>/20)</h2>
            <?php if (!empty($items)): ?>
            <a href="/p/<?= e($profile['slug']) ?>" target="_blank"
               class="text-xs text-brand-600 hover:underline font-medium">Ver perfil público →</a>
            <?php endif; ?>
        </div>

        <?php if (empty($items)): ?>
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-gray-600 font-medium mb-1">Aún no tienes fotos</p>
            <p class="text-sm text-gray-400">Agrega fotos de tus trabajos para generar más confianza.</p>
        </div>
        <?php else: ?>
        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
            <?php foreach ($items as $item): ?>
            <div class="group relative rounded-xl overflow-hidden aspect-square bg-gray-100 border border-gray-200">
                <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['title'] ?? 'Trabajo') ?>"
                     class="w-full h-full object-cover">

                <!-- Hover overlay -->
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/60 transition-all flex flex-col items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                    <?php if ($item['title']): ?>
                    <p class="text-white text-xs font-semibold text-center px-2 line-clamp-2"><?= e($item['title']) ?></p>
                    <?php endif; ?>
                    <form method="POST" action="/portfolio-action.php"
                          onsubmit="return confirm('¿Eliminar esta foto?')">
                        <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                        <button type="submit"
                                class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
