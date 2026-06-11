<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Política de Privacidad - ' . APP_NAME;
$pageDescription = 'Política de Privacidad de ' . APP_NAME . ': qué datos recopilamos, cómo los usamos y cuáles son tus derechos.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Política de Privacidad — <?= e(APP_NAME) ?></h1>
        <p class="text-sm text-gray-500 mb-8">Última actualización: 11 de junio de 2026</p>

        <div class="prose prose-gray max-w-none space-y-6 text-gray-700 leading-relaxed">
            <p>
                En <?= e(APP_NAME) ?> ("nosotros", "la Plataforma") respetamos tu privacidad y nos comprometemos
                a proteger los datos personales que nos confías. Esta Política de Privacidad explica qué
                información recopilamos cuando usas <?= e(APP_DOMAIN) ?> y nuestros servicios, cómo la usamos,
                con quién la compartimos y qué derechos tienes sobre ella.
            </p>
            <p>
                Al crear una cuenta o utilizar la Plataforma, aceptas las prácticas descritas en esta política.
                Si no estás de acuerdo, te pedimos que no utilices nuestros servicios.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">1. Responsable del tratamiento</h2>
            <p>El responsable del tratamiento de tus datos personales es:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li><strong>Razón social:</strong> <?= e(APP_NAME) ?></li>
                <li><strong>Domicilio:</strong> San Pedro Sula, Honduras</li>
                <li><strong>Correo de contacto:</strong> <a href="mailto:hola@kontactanos.com" class="text-brand-600 hover:underline">hola@kontactanos.com</a></li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">2. Datos que recopilamos</h2>

            <h3 class="text-lg font-semibold text-gray-900">2.1 Datos que nos proporcionas directamente</h3>
            <ul class="list-disc pl-6 space-y-1">
                <li><strong>Datos de registro:</strong> nombre, apellido, dirección de correo electrónico y contraseña al crear tu cuenta.</li>
                <li><strong>Datos de perfil:</strong> información adicional que decidas agregar a tu cuenta, como nombre de empresa, cargo o número de teléfono.</li>
                <li><strong>Comunicaciones:</strong> el contenido de los mensajes que nos envíes a través de soporte, correo electrónico u otros canales.</li>
            </ul>

            <h3 class="text-lg font-semibold text-gray-900">2.2 Datos que recopilamos automáticamente</h3>
            <ul class="list-disc pl-6 space-y-1">
                <li><strong>Datos técnicos:</strong> dirección IP, tipo de navegador, sistema operativo, idioma y zona horaria.</li>
                <li><strong>Datos de uso:</strong> páginas visitadas, funciones utilizadas, fecha y hora de acceso, y acciones realizadas dentro de la Plataforma.</li>
                <li><strong>Cookies y tecnologías similares:</strong> utilizamos cookies estrictamente necesarias para mantener tu sesión iniciada y garantizar la seguridad de tu cuenta. Consulta la sección 7 para más detalles.</li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">3. Finalidades del tratamiento</h2>
            <p>Utilizamos tus datos personales para:</p>
            <ol class="list-decimal pl-6 space-y-1">
                <li>Crear y administrar tu cuenta de usuario.</li>
                <li>Prestarte los servicios contratados y darles mantenimiento.</li>
                <li>Autenticarte y proteger la seguridad de tu cuenta y de la Plataforma.</li>
                <li>Responder a tus consultas y brindarte soporte técnico.</li>
                <li>Enviarte notificaciones operativas sobre el servicio (cambios, mantenimientos, avisos de seguridad).</li>
                <li>Enviarte comunicaciones comerciales sobre nuevas funciones o servicios, únicamente cuando lo hayas autorizado. Puedes darte de baja en cualquier momento.</li>
                <li>Analizar el uso de la Plataforma para mejorar su funcionamiento y experiencia de usuario.</li>
                <li>Cumplir con obligaciones legales y resolver disputas.</li>
            </ol>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">4. Base legal</h2>
            <p>Tratamos tus datos sobre las siguientes bases:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li><strong>Ejecución del contrato:</strong> para crear tu cuenta y prestarte el servicio.</li>
                <li><strong>Consentimiento:</strong> para el envío de comunicaciones comerciales y el uso de cookies no esenciales, cuando aplique.</li>
                <li><strong>Interés legítimo:</strong> para garantizar la seguridad de la Plataforma y mejorar nuestros servicios.</li>
                <li><strong>Obligación legal:</strong> cuando una ley o autoridad competente lo requiera.</li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">5. Con quién compartimos tus datos</h2>
            <p>No vendemos ni alquilamos tus datos personales. Podemos compartirlos únicamente con:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li><strong>Proveedores de servicios</strong> que nos ayudan a operar la Plataforma (alojamiento web, infraestructura en la nube, envío de correos, procesamiento de pagos cuando aplique), bajo contratos que les obligan a proteger tu información y usarla solo conforme a nuestras instrucciones.</li>
                <li><strong>Autoridades competentes</strong>, cuando exista una obligación legal o un requerimiento judicial válido.</li>
                <li><strong>Sucesores en caso de reestructuración</strong>, fusión o venta de la empresa, en cuyo caso te notificaremos antes de que tus datos queden sujetos a una política distinta.</li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">6. Transferencias internacionales</h2>
            <p>
                Nuestros proveedores de infraestructura pueden estar ubicados fuera de tu país de
                residencia (por ejemplo, en Estados Unidos). Cuando transferimos datos
                internacionalmente, nos aseguramos de que el destinatario ofrezca garantías adecuadas de
                protección, ya sea por contrato o por estar sujeto a marcos de protección de datos
                reconocidos.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">7. Cookies</h2>
            <p>Utilizamos cookies estrictamente necesarias para el funcionamiento de la Plataforma:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li><strong>Cookies de sesión:</strong> mantienen tu sesión iniciada mientras usas el servicio.</li>
                <li><strong>Cookies de seguridad:</strong> ayudan a prevenir accesos no autorizados y fraudes.</li>
            </ul>
            <p>
                Puedes configurar tu navegador para rechazar cookies, pero esto puede impedir que inicies
                sesión o uses funciones esenciales de la Plataforma. Si en el futuro incorporamos cookies de
                analítica o publicidad, actualizaremos esta política y solicitaremos tu consentimiento
                cuando la ley lo exija.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">8. Plazo de conservación</h2>
            <p>
                Conservamos tus datos personales mientras tu cuenta esté activa. Si eliminas tu cuenta,
                eliminaremos o anonimizaremos tus datos en un plazo máximo de 90 días, salvo aquellos
                que debamos conservar por obligación legal (por ejemplo, registros fiscales o contables) o
                para la defensa de reclamaciones, en cuyo caso se conservarán únicamente durante el plazo
                legalmente exigido.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">9. Seguridad</h2>
            <p>
                Aplicamos medidas técnicas y organizativas razonables para proteger tus datos, incluyendo
                cifrado de contraseñas, conexiones seguras (HTTPS), controles de acceso y monitoreo de
                nuestra infraestructura. Ningún sistema es completamente infalible, pero nos
                comprometemos a notificarte sin demora injustificada en caso de una violación de
                seguridad que afecte tus datos personales, conforme a la legislación aplicable.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">10. Tus derechos</h2>
            <p>Tienes derecho a:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li><strong>Acceder</strong> a los datos personales que tenemos sobre ti.</li>
                <li><strong>Rectificar</strong> datos inexactos o incompletos.</li>
                <li><strong>Eliminar</strong> tus datos y tu cuenta.</li>
                <li><strong>Oponerte</strong> o limitar ciertos tratamientos, incluyendo las comunicaciones comerciales.</li>
                <li><strong>Portabilidad:</strong> solicitar una copia de tus datos en un formato estructurado y de uso común.</li>
                <li><strong>Revocar tu consentimiento</strong> en cualquier momento, sin que ello afecte la licitud del tratamiento previo.</li>
            </ul>
            <p>
                Para ejercer cualquiera de estos derechos, escríbenos a
                <a href="mailto:hola@kontactanos.com" class="text-brand-600 hover:underline">hola@kontactanos.com</a>.
                Responderemos en un plazo máximo de 15 días hábiles. Si consideras que no hemos
                atendido adecuadamente tu solicitud, puedes acudir a la autoridad de protección de datos
                de tu país.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">11. Menores de edad</h2>
            <p>
                La Plataforma está dirigida a personas mayores de 18 años. No recopilamos
                intencionalmente datos de menores de edad. Si detectamos que un menor ha creado una
                cuenta, la eliminaremos junto con sus datos. Si eres padre, madre o tutor y crees que un
                menor nos ha proporcionado datos, contáctanos para eliminarlos.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">12. Enlaces a sitios de terceros</h2>
            <p>
                La Plataforma puede contener enlaces a sitios web de terceros. No somos responsables de
                sus prácticas de privacidad, por lo que te recomendamos revisar las políticas de cada sitio
                que visites.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">13. Cambios a esta política</h2>
            <p>
                Podemos actualizar esta Política de Privacidad ocasionalmente. Publicaremos la versión
                actualizada en esta página indicando la fecha de la última modificación. Si los cambios son
                significativos, te lo notificaremos por correo electrónico o mediante un aviso destacado en
                la Plataforma antes de que entren en vigor.
            </p>

            <h2 class="text-xl font-semibold text-gray-900 pt-4">14. Contacto</h2>
            <p>
                Si tienes preguntas, comentarios o solicitudes relacionadas con esta política o con el
                tratamiento de tus datos personales, escríbenos a:
            </p>
            <p>
                <a href="mailto:hola@kontactanos.com" class="text-brand-600 hover:underline">hola@kontactanos.com</a><br>
                <?= e(APP_NAME) ?> — San Pedro Sula, Honduras
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
