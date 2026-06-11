<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Eliminación de Datos - ' . APP_NAME;
$pageDescription = 'Instrucciones para solicitar la eliminación de tus datos personales y de tu cuenta en ' . APP_NAME . '.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Instrucciones para la Eliminación de Datos</h1>
        <p class="text-sm text-gray-500 mb-8">Última actualización: 11 de junio de 2026</p>

        <div class="prose prose-gray max-w-none space-y-6 text-gray-700 leading-relaxed">
            <p>
                En <?= e(APP_NAME) ?> respetamos tu derecho a controlar tus datos personales. Si deseas
                eliminar tu cuenta y los datos asociados a ella (incluyendo datos obtenidos a través
                de inicio de sesión con Facebook o Google), sigue estos pasos.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">¿Cómo solicitar la eliminación de tus datos?</h2>
            <ol class="list-decimal pl-6 space-y-2">
                <li>
                    Envía un correo electrónico a
                    <a href="mailto:hola@kontactanos.com" class="text-brand-600 hover:underline">hola@kontactanos.com</a>
                    con el asunto <strong>"Eliminación de datos"</strong>.
                </li>
                <li>
                    Incluye en el mensaje el correo electrónico o nombre de usuario asociado a tu cuenta
                    en <?= e(APP_NAME) ?>, para que podamos identificarla.
                </li>
                <li>
                    Si tu cuenta fue creada o vinculada mediante Facebook o Google, indícalo en el
                    correo para que también revoquemos esa conexión.
                </li>
            </ol>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">¿Qué datos se eliminan?</h2>
            <p>Al procesar tu solicitud, eliminaremos o anonimizaremos:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li>Tu información de cuenta (nombre, correo electrónico, contraseña).</li>
                <li>Tu perfil de proveedor o cliente, incluyendo servicios publicados.</li>
                <li>Reseñas, mensajes de contacto y otra actividad asociada a tu cuenta.</li>
                <li>Cualquier dato obtenido mediante inicio de sesión social (Facebook/Google).</li>
            </ul>
            <p>
                Conservaremos únicamente los datos que debamos retener por obligación legal
                (por ejemplo, registros fiscales o contables) durante el plazo legalmente exigido.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">Plazo de respuesta</h2>
            <p>
                Procesaremos tu solicitud y eliminaremos tus datos en un plazo máximo de
                <strong>90 días</strong> a partir de la confirmación de tu identidad. Te enviaremos
                una confirmación por correo electrónico una vez completado el proceso.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">Más información</h2>
            <p>
                Para más detalles sobre cómo tratamos tus datos personales, consulta nuestra
                <a href="/privacy.php" class="text-brand-600 hover:underline">Política de Privacidad</a>.
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
