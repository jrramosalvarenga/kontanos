<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$errors  = [];
$redirect = $_GET['redirect'] ?? '/dashboard.php';

if ($_GET['error'] ?? '') {
    $errors[] = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $result = login($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($result['success']) {
        $safe = filter_var($redirect, FILTER_VALIDATE_URL) ? '/dashboard.php' : $redirect;
        header('Location: ' . $safe);
        exit;
    }
    $errors[] = $result['message'];
}

$pageTitle = 'Iniciar Sesión | Kontactanos';
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="/" class="inline-block mb-4">
                <img src="/assets/brand/kontanos-logo-color.svg" alt="Kontactanos" class="h-12 mx-auto">
            </a>
            <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Bienvenido de nuevo</h1>
            <p class="text-gray-500 text-sm">Accede a tu cuenta de Kontactanos</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <?= e($errors[0]) ?>
            </div>
            <?php endif; ?>

            <!-- Google OAuth -->
            <?php if (GOOGLE_CLIENT_ID): ?>
            <a href="/oauth/google.php?role=provider"
               class="flex items-center justify-center gap-3 w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors mb-3">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continuar con Google
            </a>
            <?php endif; ?>

            <!-- Facebook Login -->
            <?php if (FACEBOOK_APP_ID): ?>
            <a href="/oauth/facebook.php?role=provider"
               class="flex items-center justify-center gap-3 w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors mb-5">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#1877F2">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Continuar con Facebook
            </a>
            <?php endif; ?>

            <?php if (GOOGLE_CLIENT_ID || FACEBOOK_APP_ID): ?>
            <div class="flex items-center gap-3 mb-5">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium">o con email</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
            <?php endif; ?>

            <form method="POST" action="/login.php" class="space-y-5">
                <input type="hidden" name="_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required
                           value="<?= e($_POST['email'] ?? '') ?>"
                           placeholder="tu@email.com" autofocus>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="form-label mb-0">Contraseña</label>
                        <a href="/forgot-password.php" class="text-xs text-brand-600 hover:underline">¿Olvidaste tu contraseña?</a>
                    </div>
                    <input type="password" name="password" class="form-input" required
                           placeholder="Tu contraseña">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" name="remember" class="accent-brand-600">
                    <label for="remember" class="text-sm text-gray-600">Mantener sesión iniciada</label>
                </div>

                <button type="submit" class="btn-primary w-full py-3.5 text-base">
                    Iniciar Sesión
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <p class="text-center text-sm text-gray-500">
                    ¿No tienes cuenta?
                    <a href="/register.php" class="text-brand-600 font-semibold hover:underline">Regístrate gratis</a>
                </p>
            </div>
        </div>

        <!-- Demo credentials -->
        <div class="mt-4 bg-brand-50 border border-brand-200 rounded-xl p-4 text-center">
            <p class="text-xs text-brand-700 font-semibold mb-1">Cuenta de demostración</p>
            <p class="text-xs text-brand-600">demo@kontactanos.com · password: <strong>password</strong></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
