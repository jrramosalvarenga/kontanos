<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => COOKIE_SECURE,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function login(string $email, string $password): array {
    $user = DB::fetch("SELECT * FROM users WHERE email = $1 AND is_active = TRUE", [strtolower(trim($email))]);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Email o contraseña incorrectos.'];
    }
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    DB::query("UPDATE users SET last_login = NOW() WHERE id = $1", [$user['id']]);
    return ['success' => true, 'user' => $user];
}

function register(array $data): array {
    $email = strtolower(trim($data['email']));
    $existing = DB::fetch("SELECT id FROM users WHERE email = $1", [$email]);
    if ($existing) {
        return ['success' => false, 'message' => 'Este email ya está registrado.'];
    }
    if (strlen($data['password']) < 8) {
        return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
    }
    $hash = password_hash($data['password'], PASSWORD_BCRYPT);
    $role = $data['role'] ?? 'client';

    $referrerId = null;
    if (!empty($data['ref_code'])) {
        $referrer = DB::fetch("SELECT id FROM users WHERE referral_code = $1", [strtoupper(trim($data['ref_code']))]);
        $referrerId = $referrer['id'] ?? null;
    }
    $referralCode = generateReferralCode();

    DB::conn()->beginTransaction();
    try {
        $userId = DB::insert(
            "INSERT INTO users (email, password_hash, role, is_verified, referral_code, referred_by) VALUES ($1, $2, $3, $4, $5, $6) RETURNING id",
            [$email, $hash, $role, TRUE, $referralCode, $referrerId]
        );

        if ($role === 'provider') {
            $slug = slugify($data['full_name']) . '-' . substr(uniqid(), -4);
            DB::query("
                INSERT INTO provider_profiles (user_id, slug, full_name, tagline, category_id, location_id, phone, whatsapp)
                VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
            ", [
                $userId,
                $slug,
                $data['full_name'],
                $data['tagline'] ?? null,
                $data['category_id'] ?? null,
                $data['location_id'] ?? null,
                $data['phone'] ?? null,
                $data['whatsapp'] ?? null,
            ]);
        }

        DB::conn()->commit();

        if ($referrerId) {
            awardReferralPoints($userId, $role);
        }

        $_SESSION['user_id']    = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role']  = $role;
        return ['success' => true, 'user_id' => $userId];
    } catch (Exception $e) {
        DB::conn()->rollBack();
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'Error al registrar. Intenta de nuevo.'];
    }
}

function logout(): void {
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    header('Location: /');
    exit;
}

function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireProvider(): void {
    requireLogin();
    if ($_SESSION['user_role'] !== 'provider') {
        header('Location: /dashboard.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: /dashboard.php');
        exit;
    }
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }
}
