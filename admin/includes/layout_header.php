<?php
// Requiere que la página haya definido $pageTitle y $activeNav antes de incluir este archivo.
$activeNav = $activeNav ?? '';
$navItems = [
    'dashboard'  => ['label' => 'Dashboard',     'href' => '/admin/index.php',      'icon' => 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'],
    'providers'  => ['label' => 'Profesionales', 'href' => '/admin/providers.php',  'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
    'categories' => ['label' => 'Categorías',    'href' => '/admin/categories.php', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
    'users'      => ['label' => 'Usuarios y Red','href' => '/admin/users.php',      'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4'],
    'contacts'   => ['label' => 'Solicitudes',   'href' => '/admin/contacts.php',   'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
    'ads'        => ['label' => 'Publicidad',    'href' => '/admin/ads.php',        'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2zm0 0V5a2 2 0 012-2h6l2 2h4a2 2 0 012 2v2'],
    'ranks'      => ['label' => 'Rangos',        'href' => '/admin/ranks.php',      'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?> | Kontactanos</title>
    <link rel="icon" type="image/png" href="/assets/brand/kontanos-favicon-512.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        50:'#f0fdf4',100:'#dcfce7',200:'#bbf7d0',300:'#86efac',400:'#4ade80',
                        500:'#22c55e',600:'#16a34a',700:'#15803d',800:'#166534',900:'#14532d',950:'#052e16',
                    }
                },
                fontFamily: { sans: ['"Inter"', 'system-ui', 'sans-serif'] }
            }
        }
    }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="font-sans bg-gray-50 text-gray-800 antialiased">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-brand-900 text-white flex-shrink-0 hidden lg:flex flex-col">
        <div class="p-5 border-b border-white/10">
            <a href="/" class="flex items-center gap-2">
                <img src="/assets/brand/kontanos-logo-blanco.svg" alt="Kontactanos" class="h-8 w-auto">
            </a>
            <p class="text-xs text-brand-300 mt-1">Panel de Administración</p>
        </div>
        <nav class="flex-1 py-4 space-y-1 px-3">
            <?php foreach ($navItems as $key => $item): ?>
            <a href="<?= e($item['href']) ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors <?= $activeNav === $key ? 'bg-brand-700 text-white' : 'text-brand-200 hover:bg-white/5 hover:text-white' ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item['icon'] ?>"/>
                </svg>
                <?= e($item['label']) ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="p-3 border-t border-white/10 space-y-1">
            <a href="/dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-brand-200 hover:bg-white/5 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al sitio
            </a>
            <a href="/logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-300 hover:bg-red-500/10 hover:text-red-200 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 min-w-0">
        <!-- Mobile top bar -->
        <div class="lg:hidden bg-brand-900 text-white p-4 flex items-center justify-between">
            <a href="/admin/index.php" class="font-bold">Kontactanos Admin</a>
            <a href="/dashboard.php" class="text-sm text-brand-200">Volver al sitio</a>
        </div>
        <main class="p-4 sm:p-6 lg:p-8">
            <h1 class="text-2xl font-extrabold text-gray-900 mb-6"><?= e($pageTitle ?? '') ?></h1>
