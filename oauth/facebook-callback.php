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
    header('Location: /login.php?error=' . urlencode('Autenticación con Facebook cancelada.'));
    exit;
}

if (!hash_equals($_SESSION['oauth_state'] ?? '', $state)) {
    header('Location: /login.php?error=' . urlencode('Estado OAuth inválido. Intenta de nuevo.'));
    exit;
}

$role = $_SESSION['oauth_role'] ?? 'client';
$refCode = $_SESSION['oauth_ref'] ?? '';
unset($_SESSION['oauth_state'], $_SESSION['oauth_role'], $_SESSION['oauth_ref']);

// Exchange code for access token
$tokenUrl = 'https://graph.facebook.com/v19.0/oauth/access_token?' . http_build_query([
    'client_id'     => FACEBOOK_APP_ID,
    'client_secret' => FACEBOOK_APP_SECRET,
    'redirect_uri'  => FACEBOOK_REDIRECT_URI,
    'code'          => $code,
]);
$tokenResponse = httpGet($tokenUrl);

if (!isset($tokenResponse['access_token'])) {
    header('Location: /login.php?error=' . urlencode('Error al obtener token de Facebook.'));
    exit;
}

// Get user profile from Facebook Graph API
$profileUrl = 'https://graph.facebook.com/me?' . http_build_query([
    'fields'       => 'id,name,email,picture.type(large)',
    'access_token' => $tokenResponse['access_token'],
]);
$profile = httpGet($profileUrl);

if (!isset($profile['id'])) {
    header('Location: /login.php?error=' . urlencode('No se pudo obtener el perfil de Facebook.'));
    exit;
}

$facebookId = $profile['id'];
$email      = strtolower(trim($profile['email'] ?? ''));
$name       = $profile['name'] ?? '';
$avatar     = $profile['picture']['data']['url'] ?? '';

if (!$email) {
    $email = 'fb_' . $facebookId . '@facebook.kontanos.local';
}

// Find or create user
$user = DB::fetch("SELECT * FROM users WHERE facebook_id = $1", [$facebookId]);

if (!$user) {
    $user = DB::fetch("SELECT * FROM users WHERE email = $1", [$email]);
}

if ($user) {
    if (!$user['facebook_id']) {
        DB::query("UPDATE users SET facebook_id = $1, updated_at = NOW() WHERE id = $2", [$facebookId, $user['id']]);
    }
    DB::query("UPDATE users SET last_login = NOW() WHERE id = $1", [$user['id']]);
} else {
    // Create new user
    $referrerId = null;
    if ($refCode) {
        $referrer = DB::fetch("SELECT id FROM users WHERE referral_code = $1", [strtoupper($refCode)]);
        $referrerId = $referrer['id'] ?? null;
    }
    $referralCode = generateReferralCode();

    DB::conn()->beginTransaction();
    try {
        $userId = DB::insert(
            "INSERT INTO users (email, facebook_id, role, is_verified, referral_code, referred_by) VALUES ($1, $2, $3, TRUE, $4, $5) RETURNING id",
            [$email, $facebookId, $role, $referralCode, $referrerId]
        );

        if ($role === 'provider' && $name) {
            $slug = slugify($name) . '-' . substr(uniqid(), -4);
            DB::query(
                "INSERT INTO provider_profiles (user_id, slug, full_name, avatar_url) VALUES ($1, $2, $3, $4)",
                [$userId, $slug, $name, $avatar]
            );
        }

        DB::conn()->commit();

        if ($referrerId) {
            awardReferralPoints($userId, $role);
        }

        $user = DB::fetch("SELECT * FROM users WHERE id = $1", [$userId]);
    } catch (Exception $e) {
        DB::conn()->rollBack();
        error_log('Facebook OAuth create user: ' . $e->getMessage());
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
