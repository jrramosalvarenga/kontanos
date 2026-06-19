<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireProvider();
$user    = currentUser();
$profile = DB::fetch("SELECT * FROM provider_profiles WHERE user_id = $1", [$user['id']]);

if (!$profile) {
    header('Location: /edit-profile.php?info=' . urlencode('Completa tu perfil primero.'));
    exit;
}

$products = getProviderProducts($profile['id'], false);
$services = getProviderServices($profile['id'], false);

$appShell  = true;
$pageTitle = 'Mi Catálogo | Kontactanos';
require_once __DIR__ . '/includes/header.php';

/** Renderiza una fila de catálogo (producto o servicio) en formato app. */
function renderCatalogRow(array $item, string $kind): void {
    $price = formatSimplePrice($item['precio'] !== null ? (float)$item['precio'] : null);
    $img   = $item['imagen'] ?: null;
    $payload = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
    ?>
    <div class="list-row <?= $item['activo'] ? '' : 'inactive' ?>" data-id="<?= (int)$item['id'] ?>" data-kind="<?= e($kind) ?>">
        <?php if ($img): ?>
        <img src="<?= e($img) ?>" alt="" class="list-row__thumb">
        <?php else: ?>
        <div class="list-row__thumb flex items-center justify-center text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <?php endif; ?>
        <div class="list-row__body" @click="openEdit('<?= e($kind) ?>', <?= $payload ?>)">
            <div class="list-row__title"><?= e($item['nombre']) ?></div>
            <div class="list-row__subtitle">
                <?= $price ? e($price) : 'Sin precio' ?>
                <?php if ($kind === 'service' && $item['duracion_min']): ?> · <?= (int)$item['duracion_min'] ?> min<?php endif; ?>
            </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
            <button type="button" class="tap-btn w-8 h-8 text-gray-400" @click="reorder('<?= e($kind) ?>', <?= (int)$item['id'] ?>, -1)" title="Subir">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            </button>
            <button type="button" class="tap-btn w-8 h-8 text-gray-400" @click="reorder('<?= e($kind) ?>', <?= (int)$item['id'] ?>, 1)" title="Bajar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <button type="button" class="app-switch <?= $item['activo'] ? 'on' : '' ?>" @click="toggleActive('<?= e($kind) ?>', <?= (int)$item['id'] ?>, $el)">
                <span class="app-switch__knob"></span>
            </button>
            <button type="button" class="tap-btn w-8 h-8 text-red-400" @click="confirmDeleteId = <?= (int)$item['id'] ?>; confirmDeleteKind = '<?= e($kind) ?>'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
            </button>
        </div>
    </div>
    <?php
}
?>

<div x-data="catalogPanel()" x-cloak>

    <!-- ===== APP HEADER ===== -->
    <header class="app-header">
        <a href="/dashboard.php" class="app-header__back">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <span class="app-header__title">Mi Catálogo</span>
        <a href="/p/<?= e($profile['slug']) ?>" target="_blank" class="text-xs font-semibold text-brand-600 flex-shrink-0">Ver perfil</a>
    </header>

    <div class="max-w-lg mx-auto px-4 pt-4 app-content-with-tabbar">

        <!-- Tabs Productos / Servicios -->
        <div class="flex gap-1 mb-4 bg-gray-100 rounded-xl p-1">
            <button type="button" @click="tab = 'product'"
                    :class="tab === 'product' ? 'bg-white shadow-sm text-brand-700' : 'text-gray-500'"
                    class="flex-1 text-sm font-semibold py-2 rounded-lg transition-all tap-btn">
                Productos (<?= count($products) ?>)
            </button>
            <button type="button" @click="tab = 'service'"
                    :class="tab === 'service' ? 'bg-white shadow-sm text-brand-700' : 'text-gray-500'"
                    class="flex-1 text-sm font-semibold py-2 rounded-lg transition-all tap-btn">
                Servicios (<?= count($services) ?>)
            </button>
        </div>

        <!-- Lista de productos -->
        <div x-show="tab === 'product'" x-transition.opacity class="space-y-2">
            <?php if (empty($products)): ?>
            <div class="text-center py-10 text-gray-400 border-2 border-dashed border-gray-200 rounded-2xl">
                <p class="text-sm font-medium">Aún no tienes productos.</p>
                <p class="text-xs mt-1">Toca el botón + para agregar el primero.</p>
            </div>
            <?php else: foreach ($products as $p): renderCatalogRow($p, 'product'); endforeach; endif; ?>
        </div>

        <!-- Lista de servicios -->
        <div x-show="tab === 'service'" x-transition.opacity class="space-y-2">
            <?php if (empty($services)): ?>
            <div class="text-center py-10 text-gray-400 border-2 border-dashed border-gray-200 rounded-2xl">
                <p class="text-sm font-medium">Aún no tienes servicios.</p>
                <p class="text-xs mt-1">Toca el botón + para agregar el primero.</p>
            </div>
            <?php else: foreach ($services as $s): renderCatalogRow($s, 'service'); endforeach; endif; ?>
        </div>
    </div>

    <!-- ===== FAB ===== -->
    <button type="button" class="fab" @click="openCreate(tab)" title="Agregar">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    </button>

    <!-- ===== BOTTOM TAB BAR ===== -->
    <nav class="app-tabbar">
        <a href="/dashboard.php" class="app-tabbar__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/></svg>
            <span>Inicio</span>
        </a>
        <a href="/catalog.php" class="app-tabbar__item active">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
            <span>Catálogo</span>
        </a>
        <a href="/edit-profile.php" class="app-tabbar__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Perfil</span>
        </a>
    </nav>

    <!-- ===== BOTTOM SHEET: crear / editar ===== -->
    <div x-show="sheetOpen" x-transition.opacity class="sheet-overlay" @click="closeSheet()" style="display:none"></div>
    <div x-show="sheetOpen"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="sheet" style="display:none" @click.stop>
        <div class="sheet__handle"></div>
        <h3 class="font-bold text-gray-900 mb-4 text-base" x-text="editingId ? (kind === 'product' ? 'Editar producto' : 'Editar servicio') : (kind === 'product' ? 'Nuevo producto' : 'Nuevo servicio')"></h3>

        <form @submit.prevent="submitForm()" class="space-y-4">
            <div class="flex justify-center">
                <label class="relative w-24 h-24 rounded-2xl bg-gray-100 border-2 border-dashed border-gray-200 overflow-hidden flex items-center justify-center cursor-pointer">
                    <img x-show="preview" :src="preview" class="w-full h-full object-cover">
                    <svg x-show="!preview" class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" @change="onImageChange($event)">
                </label>
            </div>

            <div>
                <label class="form-label">Nombre *</label>
                <input type="text" x-model="form.nombre" class="form-input" required maxlength="200"
                       :placeholder="kind === 'product' ? 'Ej: Pastel de chocolate' : 'Ej: Corte de cabello'">
            </div>

            <div>
                <label class="form-label">Descripción</label>
                <textarea x-model="form.descripcion" class="form-input" rows="3" maxlength="500" placeholder="Detalles, qué incluye, etc."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Precio (opcional)</label>
                    <input type="number" x-model="form.precio" class="form-input" min="0" step="0.01" placeholder="0.00">
                </div>
                <div x-show="kind === 'service'">
                    <label class="form-label">Duración (min)</label>
                    <input type="number" x-model="form.duracion_min" class="form-input" min="0" step="5" placeholder="60">
                </div>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit" class="btn-primary flex-1 py-3" :disabled="saving">
                    <span x-text="saving ? 'Guardando...' : 'Guardar'"></span>
                </button>
                <button type="button" class="px-5 py-3 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 tap-btn" @click="closeSheet()">Cancelar</button>
            </div>
        </form>
    </div>

    <!-- ===== BOTTOM SHEET: confirmar eliminación ===== -->
    <div x-show="confirmDeleteId !== null" x-transition.opacity class="sheet-overlay" @click="confirmDeleteId = null" style="display:none"></div>
    <div x-show="confirmDeleteId !== null"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="sheet" style="display:none" @click.stop>
        <div class="sheet__handle"></div>
        <h3 class="font-bold text-gray-900 mb-1 text-base">¿Eliminar este elemento?</h3>
        <p class="text-sm text-gray-500 mb-5">Esta acción no se puede deshacer.</p>
        <div class="flex gap-3">
            <button type="button" class="flex-1 py-3 rounded-xl bg-red-600 text-white text-sm font-semibold tap-btn" @click="deleteItem()">Eliminar</button>
            <button type="button" class="px-5 py-3 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 tap-btn" @click="confirmDeleteId = null">Cancelar</button>
        </div>
    </div>
</div>

<script>
function catalogPanel() {
    return {
        tab: 'product',
        sheetOpen: false,
        kind: 'product',
        editingId: null,
        form: { nombre: '', descripcion: '', precio: '', duracion_min: '' },
        imageFile: null,
        removeImage: false,
        preview: null,
        saving: false,
        confirmDeleteId: null,
        confirmDeleteKind: null,

        openCreate(kind) {
            this.kind = kind;
            this.editingId = null;
            this.form = { nombre: '', descripcion: '', precio: '', duracion_min: '' };
            this.imageFile = null;
            this.removeImage = false;
            this.preview = null;
            this.sheetOpen = true;
        },
        openEdit(kind, item) {
            this.kind = kind;
            this.editingId = item.id;
            this.form = {
                nombre: item.nombre || '',
                descripcion: item.descripcion || '',
                precio: item.precio ?? '',
                duracion_min: item.duracion_min ?? '',
            };
            this.imageFile = null;
            this.removeImage = false;
            this.preview = item.imagen || null;
            this.sheetOpen = true;
        },
        closeSheet() { this.sheetOpen = false; },

        onImageChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.imageFile = file;
            this.removeImage = false;
            const reader = new FileReader();
            reader.onload = ev => { this.preview = ev.target.result; };
            reader.readAsDataURL(file);
        },

        async submitForm() {
            if (!this.form.nombre.trim()) { showToast('El nombre es requerido.', 'error'); return; }
            this.saving = true;
            const fd = new FormData();
            fd.append('_token', '<?= csrfToken() ?>');
            fd.append('kind', this.kind);
            fd.append('action', this.editingId ? 'update' : 'create');
            if (this.editingId) fd.append('id', this.editingId);
            fd.append('nombre', this.form.nombre);
            fd.append('descripcion', this.form.descripcion || '');
            fd.append('precio', this.form.precio || '');
            if (this.kind === 'service') fd.append('duracion_min', this.form.duracion_min || '');
            if (this.imageFile) fd.append('imagen', this.imageFile);

            try {
                const res = await fetch('/api/catalog.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (!data.success) { showToast(data.message || 'No se pudo guardar.', 'error'); this.saving = false; return; }
                window.location.href = '/catalog.php?success=' + encodeURIComponent(this.editingId ? 'Cambios guardados' : 'Agregado al catálogo');
            } catch (err) {
                showToast('Error de conexión.', 'error');
                this.saving = false;
            }
        },

        async toggleActive(kind, id, el) {
            el.classList.toggle('on');
            const row = el.closest('.list-row');
            if (row) row.classList.toggle('inactive');
            const fd = new FormData();
            fd.append('_token', '<?= csrfToken() ?>');
            fd.append('kind', kind);
            fd.append('action', 'toggle');
            fd.append('id', id);
            try {
                const res = await fetch('/api/catalog.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (!data.success) throw new Error(data.message);
            } catch (err) {
                el.classList.toggle('on');
                if (row) row.classList.toggle('inactive');
                showToast('No se pudo actualizar.', 'error');
            }
        },

        async reorder(kind, id, dir) {
            const list = document.querySelectorAll(`.list-row[data-kind="${kind}"]`);
            const row = Array.from(list).find(r => parseInt(r.dataset.id) === id);
            if (!row) return;
            const sibling = dir === -1 ? row.previousElementSibling : row.nextElementSibling;
            if (!sibling || !sibling.classList.contains('list-row')) return;
            if (dir === -1) row.parentNode.insertBefore(row, sibling);
            else row.parentNode.insertBefore(sibling, row);

            const ids = Array.from(document.querySelectorAll(`.list-row[data-kind="${kind}"]`)).map(r => r.dataset.id);
            const fd = new FormData();
            fd.append('_token', '<?= csrfToken() ?>');
            fd.append('kind', kind);
            fd.append('action', 'reorder');
            ids.forEach(i => fd.append('ids[]', i));
            try {
                const res = await fetch('/api/catalog.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (!data.success) throw new Error();
            } catch (err) {
                showToast('No se pudo reordenar.', 'error');
                window.location.reload();
            }
        },

        async deleteItem() {
            const id = this.confirmDeleteId;
            const kind = this.confirmDeleteKind;
            this.confirmDeleteId = null;
            const fd = new FormData();
            fd.append('_token', '<?= csrfToken() ?>');
            fd.append('kind', kind);
            fd.append('action', 'delete');
            fd.append('id', id);
            try {
                const res = await fetch('/api/catalog.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (!data.success) { showToast(data.message || 'No se pudo eliminar.', 'error'); return; }
                window.location.href = '/catalog.php?success=' + encodeURIComponent('Eliminado del catálogo');
            } catch (err) {
                showToast('Error de conexión.', 'error');
            }
        },
    };
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
