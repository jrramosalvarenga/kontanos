<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$code  = $_GET['code']  ?? '';
$state = $_GET['state'] ?? '';
$error = $_GET['error'] ?? '';

if ($error || !$code) {
    header('Location: /login.php?error=' . urlencode('Autenticación con Google cancelada.'));
    exit;
}

if (!hash_equals($_SESSION['oauth_state'] ?? '', $state)) {
    header('Location: /login.php?error=' . urlencode('Estado OAuth inválido. Intenta de nuevo.'));
    exit;
}

$role = $_SESSION['oauth_role'] ?? 'client';
unset($_SESSION['oauth_state'], $_SESSION['oauth_role']);

// Exchange code for tokens
$tokenResponse = httpPost('https://oauth2.googleapis.com/token', [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
]);

if (!isset($tokenResponse['access_token'])) {
    header('Location: /login.php?error=' . urlencode('Error al obtener token de Google.'));
    exit;
}

// Get user profile from Google
$profile = httpGet(
    'https://www.googleapis.com/oauth2/v3/userinfo',
    ['Authorization: Bearer ' . $tokenResponse['access_token']]
);

if (!isset($profile['sub'])) {
    header('Location: /login.php?error=' . urlencode('No se pudo obtener el perfil de Google.'));
    exit;
}

$googleId = $profile['sub'];
$email    = strtolower(trim($profile['email'] ?? ''));
$name     = $profile['name']    ?? '';
$avatar   = $profile['picture'] ?? '';

// Find or create user
$user = DB::fetch("SELECT * FROM users WHERE google_id = $1", [$googleId]);

if (!$user && $email) {
    $user = DB::fetch("SELECT * FROM users WHERE email = $1", [$email]);
}

if ($user) {
    // Update google_id if not set
    if (!$user['google_id']) {
        DB::query("UPDATE users SET google_id = $1, updated_at = NOW() WHERE id = $2", [$googleId, $user['id']]);
    }
    DB::query("UPDATE users SET last_login = NOW() WHERE id = $1", [$user['id']]);
} else {
    // Create new user
    DB::conn()->beginTransaction();
    try {
        $userId = DB::insert(
            "INSERT INTO users (email, google_id, role, is_verified) VALUES ($1, $2, $3, TRUE) RETURNING id",
            [$email, $googleId, $role]
        );

        if ($role === 'provider' && $name) {
            $slug = slugify($name) . '-' . substr(uniqid(), -4);
            DB::query(
                "INSERT INTO provider_profiles (user_id, slug, full_name, avatar_url) VALUES ($1, $2, $3, $4)",
                [$userId, $slug, $name, $avatar]
            );
        }

        DB::conn()->commit();
        $user = DB::fetch("SELECT * FROM users WHERE id = $1", [$userId]);
    } catch (Exception $e) {
        DB::conn()->rollBack();
        error_log('Google OAuth create user: ' . $e->getMessage());
        header('Location: /login.php?error=' . urlencode('Error al crear la cuenta. Intenta de nuevo.'));
        exit;
    }
}

$_SESSION['user_id']    = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role']  = $user['role'];

$redirect = ($user['role'] === 'provider') ? '/dashboard.php?welcome=1' : '/search.php';
header('Location: ' . $redirect);
exit;

// --- Helpers ---

function httpPost(string $url, array $data): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    return json_decode($body ?: '{}', true) ?: [];
}

function httpGet(string $url, array $headers = []): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => array_merge(['Accept: application/json'], $headers),
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    return json_decode($body ?: '{}', true) ?: [];
}
