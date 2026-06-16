<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentUser = isLoggedIn() ? currentUser() : null;
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$notifCount = 0;
if ($currentUser && $currentUser['role'] === 'provider') {
    $notifCount = getPendingContactsCount($currentUser['id']);
}

$pageTitle       = $pageTitle ?? APP_NAME . ' - ' . APP_TAGLINE;
$pageDescription = $pageDescription ?? 'Conecta con los mejores profesionales y servicios cerca de ti. Encuentra plomeros, electricistas, diseñadores, médicos y más en tu área.';
$pageImage       = $pageImage ?? APP_URL . '/assets/brand/kontanos-logo-color.png';
$pageUrl         = $pageUrl ?? APP_URL . $currentPath;
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#15803d">

    <!-- Open Graph / Social Sharing -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($pageUrl) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:image" content="<?= e($pageImage) ?>">
    <meta property="og:site_name" content="<?= APP_NAME ?>">
    <meta property="og:locale" content="es_ES">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($pageImage) ?>">

    <!-- Canonical -->
    <link rel="canonical" href="<?= e($pageUrl) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/brand/kontanos-favicon-512.png">
    <link rel="apple-touch-icon" href="/assets/brand/kontanos-favicon-512.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        50:  '#f0fdf4',
                        100: '#dcfce7',
                        200: '#bbf7d0',
                        300: '#86efac',
                        400: '#4ade80',
                        500: '#22c55e',
                        600: '#16a34a',
                        700: '#15803d',
                        800: '#166534',
                        900: '#14532d',
                        950: '#052e16',
                    }
                },
                fontFamily: {
                    sans: ['"Inter"', 'system-ui', 'sans-serif'],
                },
                animation: {
                    'fade-in': 'fadeIn 0.5s ease-in-out',
                    'slide-up': 'slideUp 0.4s ease-out',
                    'pulse-slow': 'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
                }
            }
        }
    }
    </script>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="font-sans bg-gray-50 text-gray-800 antialiased">

<!-- Announcement bar -->
<div class="bg-brand-700 text-white text-center py-2 px-4 text-sm font-medium">
    🚀 ¡Regístrate gratis y empieza a conectar hoy mismo! &nbsp;
    <a href="/register.php" class="underline font-semibold hover:text-brand-200 transition-colors">Crear perfil →</a>
</div>

<!-- Navbar -->
<nav class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-100" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2 flex-shrink-0">
                <img src="/assets/brand/kontanos-logo-color.svg" alt="Kontactanos" class="h-10 w-auto">
            </a>

            <!-- Desktop nav links -->
            <div class="hidden md:flex items-center gap-6">
                <a href="/search.php" class="text-gray-600 hover:text-brand-700 font-medium transition-colors text-sm">
                    Explorar Servicios
                </a>
                <a href="/search.php?type=providers" class="text-gray-600 hover:text-brand-700 font-medium transition-colors text-sm">
                    Profesionales
                </a>
                <?php if ($currentUser && $currentUser['role'] === 'provider'): ?>
                <a href="/inbox.php" class="relative flex items-center gap-1.5 text-gray-600 hover:text-brand-700 font-medium transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Bandeja
                    <?php if ($notifCount > 0): ?>
                    <span class="absolute -top-1.5 -right-3 bg-red-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center leading-none">
                        <?= min($notifCount, 9) ?><?= $notifCount > 9 ? '+' : '' ?>
                    </span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
            </div>

            <!-- Search bar (desktop) -->
            <form action="/search.php" method="GET" class="hidden lg:flex items-center bg-gray-50 border border-gray-200 rounded-full px-4 py-2 gap-2 w-64 hover:border-brand-400 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-100 transition-all">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="q" placeholder="¿Qué necesitas?" class="bg-transparent text-sm outline-none w-full text-gray-700 placeholder-gray-400">
            </form>

            <!-- Auth buttons -->
            <div class="hidden md:flex items-center gap-3">
                <?php if ($currentUser): ?>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-full hover:bg-gray-100 p-1 pr-3 transition-colors">
                            <img src="<?= e(getAvatar($currentUser['profile_avatar'] ?? null, $currentUser['email'], '40')) ?>"
                                 alt="Avatar" class="w-8 h-8 rounded-full object-cover border-2 border-brand-200">
                            <span class="text-sm font-medium text-gray-700"><?= e(explode('@', $currentUser['email'])[0]) ?></span>
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <a href="/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700">Mi Panel</a>
                            <?php if ($currentUser['role'] === 'provider'): ?>
                            <a href="/my-profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700">Mi Perfil</a>
                            <a href="/create-service.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700">Nuevo Servicio</a>
                            <?php endif; ?>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                            <a href="/admin/" class="block px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700">Admin</a>
                            <?php endif; ?>
                            <hr class="my-1 border-gray-100">
                            <a href="/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Cerrar Sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login.php" class="text-sm font-medium text-gray-600 hover:text-brand-700 transition-colors">
                        Iniciar Sesión
                    </a>
                    <a href="/register.php" class="btn-primary text-sm">
                        Publicar Servicio
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile menu button -->
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="mobileOpen" x-transition class="md:hidden bg-white border-t border-gray-100 px-4 py-4 space-y-3">
        <form action="/search.php" method="GET" class="flex items-center bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="q" placeholder="¿Qué necesitas?" class="bg-transparent text-sm outline-none w-full">
        </form>
        <a href="/search.php" class="block py-2 text-gray-700 font-medium">Explorar Servicios</a>
        <?php if ($currentUser): ?>
            <a href="/dashboard.php" class="block py-2 text-gray-700 font-medium">Mi Panel</a>
            <?php if ($currentUser['role'] === 'provider'): ?>
            <a href="/inbox.php" class="flex items-center justify-between py-2 text-gray-700 font-medium">
                Bandeja de solicitudes
                <?php if ($notifCount > 0): ?>
                <span class="bg-red-500 text-white text-xs font-bold rounded-full px-2 py-0.5"><?= $notifCount ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            <a href="/logout.php" class="block py-2 text-red-600 font-medium">Cerrar Sesión</a>
        <?php else: ?>
            <a href="/login.php" class="block py-2 text-gray-700 font-medium">Iniciar Sesión</a>
            <a href="/register.php" class="btn-primary block text-center">Publicar Servicio</a>
        <?php endif; ?>
    </div>
</nav>
