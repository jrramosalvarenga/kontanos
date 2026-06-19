<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

function fail(int $code, string $message): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if (!isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'provider') {
    fail(401, 'No autenticado.');
}
if (($_POST['_token'] ?? '') === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['_token'])) {
    fail(403, 'Token CSRF inválido.');
}

$profile = DB::fetch("SELECT id FROM provider_profiles WHERE user_id = $1", [$_SESSION['user_id']]);
if (!$profile) {
    fail(400, 'Completa tu perfil primero.');
}
$providerId = (int)$profile['id'];

$kind = $_POST['kind'] ?? '';
if (!in_array($kind, ['product', 'service'], true)) {
    fail(400, 'Tipo de catálogo inválido.');
}
$table = $kind === 'product' ? 'products' : 'services';

$action = $_POST['action'] ?? '';

/** Confirma que el item pertenece al proveedor en sesión. */
function ownItemOrFail(string $table, int $id, int $providerId): array {
    $row = DB::fetch("SELECT * FROM $table WHERE id = $1", [$id]);
    if (!$row || (int)$row['provider_id'] !== $providerId) {
        fail(404, 'Elemento no encontrado.');
    }
    return $row;
}

switch ($action) {

    case 'create':
    case 'update': {
        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') fail(422, 'El nombre es requerido.');
        if (mb_strlen($nombre) > 200) fail(422, 'El nombre es demasiado largo.');

        $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
        $precioRaw   = trim($_POST['precio'] ?? '');
        $precio      = $precioRaw !== '' ? round((float)$precioRaw, 2) : null;
        if ($precio !== null && $precio < 0) fail(422, 'El precio no puede ser negativo.');

        $duracion = null;
        if ($kind === 'service') {
            $durRaw = trim($_POST['duracion_min'] ?? '');
            $duracion = $durRaw !== '' ? max(0, (int)$durRaw) : null;
        }

        $existing = null;
        $id = (int)($_POST['id'] ?? 0);
        if ($action === 'update') {
            if (!$id) fail(400, 'ID requerido.');
            $existing = ownItemOrFail($table, $id, $providerId);
        }

        $imagen = $existing['imagen'] ?? null;
        if (!empty($_POST['remove_image'])) {
            deleteCatalogUploadIfLocal($imagen);
            $imagen = null;
        }
        if (!empty($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $mime = mime_content_type($_FILES['imagen']['tmp_name']);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
                fail(422, 'Formato de imagen no permitido.');
            }
            if ($_FILES['imagen']['size'] > MAX_FILE_SIZE) {
                fail(422, 'La imagen no puede superar 5MB.');
            }
            $newUrl = saveCatalogUpload($_FILES['imagen'], $kind, $providerId);
            if (!$newUrl) fail(500, 'No se pudo subir la imagen.');
            deleteCatalogUploadIfLocal($imagen);
            $imagen = $newUrl;
        }

        if ($action === 'create') {
            if ($kind === 'service') {
                $newId = DB::insert("
                    INSERT INTO services (provider_id, nombre, descripcion, precio, duracion_min, imagen, activo, orden)
                    VALUES ($1, $2, $3, $4, $5, $6, TRUE,
                        COALESCE((SELECT MAX(orden) + 1 FROM services WHERE provider_id = $1), 0))
                    RETURNING id
                ", [$providerId, $nombre, $descripcion, $precio, $duracion, $imagen]);
            } else {
                $newId = DB::insert("
                    INSERT INTO products (provider_id, nombre, descripcion, precio, imagen, activo, orden)
                    VALUES ($1, $2, $3, $4, $5, TRUE,
                        COALESCE((SELECT MAX(orden) + 1 FROM products WHERE provider_id = $1), 0))
                    RETURNING id
                ", [$providerId, $nombre, $descripcion, $precio, $imagen]);
            }
            $item = DB::fetch("SELECT * FROM $table WHERE id = $1", [$newId]);
            echo json_encode(['success' => true, 'item' => $item]);
            exit;
        }

        if ($kind === 'service') {
            DB::query("UPDATE services SET nombre=$1, descripcion=$2, precio=$3, duracion_min=$4, imagen=$5 WHERE id=$6", [$nombre, $descripcion, $precio, $duracion, $imagen, $id]);
        } else {
            DB::query("UPDATE products SET nombre=$1, descripcion=$2, precio=$3, imagen=$4 WHERE id=$5", [$nombre, $descripcion, $precio, $imagen, $id]);
        }
        $item = DB::fetch("SELECT * FROM $table WHERE id = $1", [$id]);
        echo json_encode(['success' => true, 'item' => $item]);
        exit;
    }

    case 'toggle': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) fail(400, 'ID requerido.');
        $row = ownItemOrFail($table, $id, $providerId);
        $newActivo = !$row['activo'];
        DB::query("UPDATE $table SET activo = $1 WHERE id = $2", [$newActivo, $id]);
        echo json_encode(['success' => true, 'activo' => $newActivo]);
        exit;
    }

    case 'delete': {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) fail(400, 'ID requerido.');
        $row = ownItemOrFail($table, $id, $providerId);
        deleteCatalogUploadIfLocal($row['imagen'] ?? null);
        DB::query("DELETE FROM $table WHERE id = $1", [$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    case 'reorder': {
        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids) || empty($ids)) fail(400, 'Lista de orden vacía.');
        foreach ($ids as $i => $rawId) {
            $id = (int)$rawId;
            $row = DB::fetch("SELECT provider_id FROM $table WHERE id = $1", [$id]);
            if (!$row || (int)$row['provider_id'] !== $providerId) continue;
            DB::query("UPDATE $table SET orden = $1 WHERE id = $2", [$i, $id]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    default:
        fail(400, 'Acción inválida.');
}
