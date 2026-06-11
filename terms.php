<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Términos de Uso - ' . APP_NAME;
$pageDescription = 'Términos y Condiciones del Servicio de ' . APP_NAME . '.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Términos y Condiciones de Uso — <?= e(APP_NAME) ?></h1>
        <p class="text-sm text-gray-500 mb-8">Última actualización: 11 de junio de 2026</p>

        <div class="prose prose-gray max-w-none space-y-6 text-gray-700 leading-relaxed">
            <p>
                Estos Términos y Condiciones ("Términos") regulan el acceso y uso de
                <?= e(APP_DOMAIN) ?> (la "Plataforma"), operada por <?= e(APP_NAME) ?> ("nosotros",
                "la Plataforma"). Al crear una cuenta o utilizar nuestros servicios, aceptas
                quedar vinculado por estos Términos. Si no estás de acuerdo, no utilices la Plataforma.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">1. Descripción del servicio</h2>
            <p>
                <?= e(APP_NAME) ?> es un directorio y plataforma de conexión que permite a usuarios
                ("Clientes") encontrar y contactar a personas o negocios que ofrecen servicios o
                productos ("Proveedores") en su localidad. No somos parte de los acuerdos,
                contratos o transacciones que se celebren entre Clientes y Proveedores.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">2. Cuentas de usuario</h2>
            <ul class="list-disc pl-6 space-y-1">
                <li>Debes tener al menos 18 años para registrarte y usar la Plataforma.</li>
                <li>Eres responsable de mantener la confidencialidad de tu contraseña y de toda actividad realizada desde tu cuenta.</li>
                <li>La información que proporciones (datos de registro, perfil, servicios) debe ser veraz, exacta y estar actualizada.</li>
                <li>Nos reservamos el derecho de suspender o eliminar cuentas que infrinjan estos Términos o que proporcionen información falsa.</li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">3. Perfiles y publicación de servicios</h2>
            <p>
                Los Proveedores son los únicos responsables de la exactitud, legalidad y calidad de
                la información, descripciones, precios e imágenes que publican en sus perfiles y
                servicios. <?= e(APP_NAME) ?> no garantiza la disponibilidad, calidad ni resultado
                de los servicios ofrecidos por los Proveedores.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">4. Conducta del usuario</h2>
            <p>Al usar la Plataforma, te comprometes a no:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li>Publicar contenido falso, engañoso, difamatorio, ofensivo o ilegal.</li>
                <li>Suplantar la identidad de otra persona o empresa.</li>
                <li>Usar la Plataforma para enviar spam, fraude o contenido malicioso.</li>
                <li>Intentar acceder sin autorización a cuentas, sistemas o datos de otros usuarios.</li>
                <li>Recopilar datos de otros usuarios con fines distintos a los previstos por la Plataforma.</li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">5. Reseñas y contenido generado por usuarios</h2>
            <p>
                Las reseñas y calificaciones deben reflejar experiencias reales y honestas. Nos
                reservamos el derecho de eliminar contenido que viole estos Términos o que
                consideremos inapropiado, sin que ello implique obligación de revisar todo el
                contenido publicado.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">6. Propiedad intelectual</h2>
            <p>
                La marca, el logotipo, el diseño y los elementos propios de <?= e(APP_NAME) ?> son
                propiedad de la Plataforma y no pueden ser usados sin autorización. Conservas los
                derechos sobre el contenido que publicas, pero nos otorgas una licencia para
                mostrarlo dentro de la Plataforma con el fin de prestar el servicio.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">7. Enlaces e inicio de sesión con terceros</h2>
            <p>
                La Plataforma permite iniciar sesión mediante servicios de terceros (como Google o
                Facebook). El uso de dichos servicios está sujeto también a los términos y
                políticas de privacidad de esos proveedores.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">8. Limitación de responsabilidad</h2>
            <p>
                La Plataforma se proporciona "tal cual" y "según disponibilidad". En la medida
                permitida por la ley, <?= e(APP_NAME) ?> no será responsable por daños indirectos,
                incidentales o consecuentes derivados del uso de la Plataforma o de las relaciones
                entre Clientes y Proveedores.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">9. Suspensión y terminación</h2>
            <p>
                Podemos suspender o cancelar tu acceso a la Plataforma, en cualquier momento y sin
                previo aviso, si incumples estos Términos o si consideramos que tu conducta pone en
                riesgo a otros usuarios o a la Plataforma.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">10. Cambios a estos Términos</h2>
            <p>
                Podemos actualizar estos Términos ocasionalmente. Publicaremos la versión
                actualizada en esta página indicando la fecha de la última modificación. El uso
                continuado de la Plataforma después de un cambio implica la aceptación de los
                nuevos Términos.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">11. Ley aplicable</h2>
            <p>
                Estos Términos se rigen por las leyes de Honduras. Cualquier disputa relacionada
                con estos Términos se someterá a los tribunales competentes de San Pedro Sula,
                Honduras.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">12. Contacto</h2>
            <p>
                Si tienes preguntas sobre estos Términos, escríbenos a:
                <a href="mailto:hola@kontactanos.com" class="text-brand-600 hover:underline">hola@kontactanos.com</a>.
            </p>
            <p>
                Para más información sobre el tratamiento de tus datos personales, consulta
                nuestra <a href="/privacy.php" class="text-brand-600 hover:underline">Política de Privacidad</a>.
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
