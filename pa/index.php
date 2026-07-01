<?php
// pa/index.php
require_once '../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/Exception.php';
require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';

$mensaje = isset($_SESSION['pa_mensaje']) ? $_SESSION['pa_mensaje'] : "";
$tipo_mensaje = isset($_SESSION['pa_tipo']) ? $_SESSION['pa_tipo'] : "";
unset($_SESSION['pa_mensaje'], $_SESSION['pa_tipo']);

// Obtener solo las fechas futuras disponibles (de hoy en adelante)
$stmt_fechas = $pdo->query("SELECT id, fecha_visita, hora_visita FROM pa_fechas WHERE fecha_visita >= CURDATE() ORDER BY fecha_visita ASC");
$fechas_disponibles = $stmt_fechas->fetchAll();

// Función corregida: Evita el desfase de zona horaria forzando la lectura plana de la fecha
function formatearFechaHumana($fecha_sql) {
    $fecha_objeto = new DateTime($fecha_sql . ' 00:00:00', new DateTimeZone('UTC'));

    $formateador = new IntlDateFormatter(
        'es_ES',
        IntlDateFormatter::FULL,
        IntlDateFormatter::NONE,
        'UTC',
        IntlDateFormatter::GREGORIAN,
        "EEEE, d 'de' MMMM Y"
    );

    return ucfirst($formateador->format($fecha_objeto));
}

function generarCorreoConfirmacionHTML($nombreAcudiente, $nombreAspirante, $fechaVisita) {
    $nombreAcudiente = htmlspecialchars($nombreAcudiente, ENT_QUOTES, 'UTF-8');
    $nombreAspirante = htmlspecialchars($nombreAspirante, ENT_QUOTES, 'UTF-8');
    $fechaVisita = htmlspecialchars($fechaVisita, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación Puertas Abiertas</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:'Segoe UI',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8;padding:40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(11,32,63,0.08);">
                    <tr>
                        <td style="padding:40px 32px 24px;text-align:center;">
                            <img src="https://forms.lf.edu.co/img/logo-color.svg" alt="Liceo Francés de Medellín" width="200" style="display:block;margin:0 auto;max-width:100%;height:auto;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 8px;">
                            <h1 style="margin:0 0 20px;font-size:22px;font-weight:700;color:#0b203f;text-align:center;line-height:1.3;">¡Inscripción confirmada!</h1>
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#4a5568;">Hola, <strong style="color:#0b203f;">{$nombreAcudiente}</strong>,</p>
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#4a5568;">Gracias por inscribirte a Puertas Abiertas del Liceo Francés de Medellín. Queda confirmada tu asistencia.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 32px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;border:1px solid #e8edf2;border-radius:12px;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#718096;text-transform:uppercase;letter-spacing:0.5px;">Aspirante inscrito</p>
                                        <p style="margin:0 0 16px;font-size:16px;font-weight:600;color:#0b203f;">{$nombreAspirante}</p>
                                        <p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#718096;text-transform:uppercase;letter-spacing:0.5px;">Fecha de visita</p>
                                        <p style="margin:0;font-size:16px;font-weight:600;color:#0b203f;">{$fechaVisita}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 32px;">
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#718096;text-align:center;">Te esperamos en las instalaciones del Liceo. Si tienes alguna inquietud, puedes contactarnos a través de nuestros canales oficiales.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#0b203f;padding:24px 32px;text-align:center;">
                            <p style="margin:0;font-size:13px;color:#ffffff;opacity:0.9;">Puertas Abiertas · Liceo Francés de Medellín</p>
                            <p style="margin:8px 0 0;font-size:12px;color:#ffffff;opacity:0.6;">
                                <a href="https://lf.edu.co" style="color:#ffffff;text-decoration:none;">lf.edu.co</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

function enviarCorreoSMTP($destinatario, $nombreDestinatario, $asunto, $cuerpoHTML) {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_USER, 'Puertas Abiertas LF');
    $mail->addAddress($destinatario, $nombreDestinatario);
    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body    = $cuerpoHTML;

    $mail->send();
}

// Función reutilizable para renderizar el listado de países
function obtenerPaisesHTML($selected = 'Colombia') {
    $paises = ["Colombia", "Afganistán", "Albania", "Alemania", "Andorra", "Angola", "Antigua y Barbuda", "Arabia Saudita", "Argelia", "Argentina", "Armenia", "Australia", "Austria", "Azerbaiyán", "Bahamas", "Bangladés", "Barbados", "Baréin", "Bélgica", "Belice", "Benín", "Bielorrusia", "Birmania", "Bolivia", "Bosnia y Herzegovina", "Botsuana", "Brasil", "Brunéi", "Bulgaria", "Burkina Faso", "Burundi", "Bután", "Cabo Verde", "Camboya", "Camerún", "Canadá", "Catar", "Chad", "Chile", "China", "Chipre", "Ciudad del Vaticano", "Comoras", "Corea del Norte", "Corea del Sur", "Costa de Marfil", "Costa Rica", "Croacia", "Cuba", "Dinamarca", "Dominica", "Ecuador", "Egipto", "El Salvador", "Emiratos Árabes Unidos", "Eritrea", "Eslovaquia", "Eslovenia", "España", "Estados Unidos", "Estonia", "Etiopía", "Filipinas", "Finlandia", "Fiyi", "Francia", "Gabón", "Gambia", "Georgia", "Ghana", "Granada", "Grecia", "Guatemala", "Guyana", "Guinea", "Haití", "Honduras", "Hungría", "India", "Indonesia", "Irak", "Irán", "Irlanda", "Islandia", "Islas Marshall", "Islas Salomón", "Israel", "Italia", "Jamaica", "Japón", "Jordania", "Kazajistán", "Kenia", "Kirguistán", "Kiribati", "Kuwait", "Laos", "Lesoto", "Letonia", "Líbano", "Liberia", "Libia", "Liechtenstein", "Lituania", "Luxemburgo", "Madagascar", "Malasia", "Malaui", "Maldivas", "Malli", "Malta", "Marruecos", "Mauricio", "Mauritania", "México", "Micronesia", "Moldavia", "Mónaco", "Mongolia", "Montenegro", "Mozambique", "Namibia", "Nauru", "Nepal", "Nicaragua", "Níger", "Nigeria", "Noruega", "Nueva Zelanda", "Omán", "Países Bajos", "Pakistán", "Palaos", "Panamá", "Papúa Nueva Guinea", "Paraguay", "Perú", "Polonia", "Portugal", "Reino Unido", "República Centroafricana", "República Checa", "República del Congo", "República Dominicana", "Ruanda", "Rumanía", "Rusia", "Samoa", "San Cristóbal y Nieves", "San Marino", "San Vicente y las Granadinas", "Santa Lucía", "Santo Tomé y Príncipe", "Senegal", "Serbia", "Seychelles", "Sierra Leona", "Singapur", "Siria", "Somalia", "Sri Lanka", "Suazilandia", "Sudáfrica", "Sudán", "Suecia", "Suiza", "Surinam", "Tailandia", "Tanzania", "Tayikistán", "Timor Oriental", "Togo", "Tonga", "Trinidad y Tobago", "Túnez", "Turkmenistán", "Turquía", "Tuvalu", "Ucrania", "Uganda", "Uruguay", "Uzbekistán", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Yibuti", "Zambia", "Zimbabue"];
    $html = "";
    foreach ($paises as $pais) {
        $sel = ($pais === $selected) ? "selected" : "";
        $html .= "<option value='$pais' $sel>$pais</option>";
    }
    return $html;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_visita_id = intval($_POST['fecha_visita']);
    $asp_nombres = trim($_POST['asp_nombres']);
    $asp_apellidos = trim($_POST['asp_apellidos']);
    $asp_fecha_nac = $_POST['asp_fecha_nacimiento'];
    $asp_nacionalidad = $_POST['asp_nacionalidad'];
    $asp_escolarizado = $_POST['asp_escolarizado'];

    $asp_institucion = ($asp_escolarizado === 'si') ? trim($_POST['asp_institucion']) : null;
    $asp_grado = ($asp_escolarizado === 'si') ? trim($_POST['asp_grado']) : null;
    $asp_ciudad = ($asp_escolarizado === 'si') ? trim($_POST['asp_ciudad']) : null;

    $acu1_nombres = trim($_POST['acu1_nombres']);
    $acu1_apellidos = trim($_POST['acu1_apellidos']);
    $acu1_celular = trim($_POST['acu1_celular']);
    $acu1_email = filter_var(trim($_POST['acu1_email']), FILTER_VALIDATE_EMAIL);
    $acu1_nacionalidad = $_POST['acu1_nacionalidad'];

    $acu2_nombres = !empty($_POST['acu2_nombres']) ? trim($_POST['acu2_nombres']) : null;
    $acu2_apellidos = !empty($_POST['acu2_apellidos']) ? trim($_POST['acu2_apellidos']) : null;
    $acu2_celular = !empty($_POST['acu2_celular']) ? trim($_POST['acu2_celular']) : null;
    $acu2_email = !empty($_POST['acu2_email']) ? filter_var(trim($_POST['acu2_email']), FILTER_VALIDATE_EMAIL) : null;
    $acu2_nacionalidad = !empty($_POST['acu2_nacionalidad']) ? $_POST['acu2_nacionalidad'] : null;

    $politica = isset($_POST['politica']);

    if (!$fecha_visita_id || !$asp_nombres || !$asp_apellidos || !$asp_fecha_nac || !$asp_nacionalidad || !$acu1_nombres || !$acu1_apellidos || !$acu1_celular || !$acu1_email || !$politica) {
        $_SESSION['pa_mensaje'] = "Todos los campos obligatorios (*) deben ser diligenciados correctamente.";
        $_SESSION['pa_tipo'] = "danger";
        header("Location: /pa");
        exit;
    } else {
        try {
            $sql = "INSERT INTO pa_inscripciones (fecha_visita_id, asp_nombres, asp_apellidos, asp_fecha_nacimiento, asp_nacionalidad, asp_escolarizado, asp_institucion, asp_grado, asp_ciudad, acu1_nombres, acu1_apellidos, acu1_celular, acu1_email, acu1_nacionalidad, acu2_nombres, acu2_apellidos, acu2_celular, acu2_email, acu2_nacionalidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fecha_visita_id, $asp_nombres, $asp_apellidos, $asp_fecha_nac, $asp_nacionalidad, $asp_escolarizado, $asp_institucion, $asp_grado, $asp_ciudad, $acu1_nombres, $acu1_apellidos, $acu1_celular, $acu1_email, $acu1_nacionalidad, $acu2_nombres, $acu2_apellidos, $acu2_celular, $acu2_email, $acu2_nacionalidad]);

            $_SESSION['pa_mensaje'] = "¡Inscripción registrada con éxito! Te esperamos en las instalaciones del Liceo.";
            $_SESSION['pa_tipo'] = "success";

            $stmt_fecha = $pdo->prepare("SELECT fecha_visita FROM pa_fechas WHERE id = ?");
            $stmt_fecha->execute([$fecha_visita_id]);
            $fecha_row = $stmt_fecha->fetch();
            $fecha_visita_texto = $fecha_row ? formatearFechaHumana($fecha_row['fecha_visita']) : 'Fecha por confirmar';

            $nombre_acudiente = "$acu1_nombres $acu1_apellidos";
            $nombre_aspirante = "$asp_nombres $asp_apellidos";

            // Enviar alerta por correo al administrador del Liceo
            try {
                $cuerpoAdmin = "<h3>Nueva solicitud de visita guiada</h3>
                                <p><strong>Aspirante:</strong> $asp_nombres $asp_apellidos</p>
                                <p><strong>Acudiente principal:</strong> $acu1_nombres $acu1_apellidos ($acu1_celular)</p>
                                <p>Revisa los detalles completos ingresando al panel administrativo en https://forms.lf.edu.co/</p>";
                enviarCorreoSMTP(
                    CORREO_VISITAS,
                    'Puertas Abiertas - Liceo Francés',
                    "Nueva Inscripción a Puertas Abiertas: $asp_nombres $asp_apellidos",
                    $cuerpoAdmin
                );
            } catch (\Exception $mailEx) {
                // El catch interno evita que un fallo en el servidor de correos provoque un Error 500
            }

            // Enviar correo de confirmación al acudiente
            try {
                $cuerpoUsuario = generarCorreoConfirmacionHTML($nombre_acudiente, $nombre_aspirante, $fecha_visita_texto);
                enviarCorreoSMTP(
                    $acu1_email,
                    $nombre_acudiente,
                    'Confirmación de inscripción - Puertas Abiertas LF',
                    $cuerpoUsuario
                );
            } catch (\Exception $mailEx) {
                // Si falla el correo al usuario, la inscripción ya quedó guardada
            }

        } catch (\Exception $e) {
            $_SESSION['pa_mensaje'] = "Error interno en el servidor: " . $e->getMessage();
            $_SESSION['pa_tipo'] = "danger";
        }
        header("Location: /pa");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción Puertas Abiertas - Liceo Francés de Medellín</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/style.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <div class="text-center mb-4">
        <img src="/img/logo-color.svg" alt="Liceo Francés" class="img-fluid logo-container">
    </div>

    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card card-pqr p-2 p-md-4">
                <div class="card-body">
                    <div class="card-pqr-header text-center mb-4">
                        <h4>Inscripción Puertas Abiertas</h4>
                        <p class="text-muted small mb-0">Admisiones - Liceo Francés de Medellín</p>
                    </div>

                    <?php if ($mensaje && $tipo_mensaje === 'danger'): ?>
                        <div class="alert alert-danger shadow-sm"><?= $mensaje ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">

                        <h5 class="fw-bold mb-3 mt-2" style="color: #0b203f;">A. Datos del Aspirante</h5>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">Nombres *</label>
                                <input type="text" name="asp_nombres" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos *</label>
                                <input type="text" name="asp_apellidos" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">Fecha de Nacimiento *</label>
                                <input type="date" name="asp_fecha_nacimiento" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nacionalidad *</label>
                                <select name="asp_nacionalidad" class="form-select" required>
                                    <?= obtenerPaisesHTML() ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">¿El aspirante se encuentra escolarizado actualmente? *</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="asp_escolarizado" id="esc_si" value="si" onclick="toggleEscolaridad(true)" required>
                                <label class="form-check-label" for="esc_si">Sí</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="asp_escolarizado" id="esc_no" value="no" onclick="toggleEscolaridad(false)" required>
                                <label class="form-check-label" for="esc_no">No</label>
                            </div>
                        </div>

                        <div id="campos_escolaridad" class="p-3 bg-light rounded-3 mb-4 border" style="display: none;">
                            <div class="row">
                                <div class="col-md-5 mb-3 mb-md-0">
                                    <label class="form-label">Nombre de la Institución *</label>
                                    <input type="text" name="asp_institucion" id="asp_institucion" class="form-control">
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <label class="form-label">Grado *</label>
                                    <input type="text" name="asp_grado" id="asp_grado" class="form-control" placeholder="Ej. Primero">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ciudad *</label>
                                    <input type="text" name="asp_ciudad" id="asp_ciudad" class="form-control">
                                </div>
                            </div>
                        </div>

                        <hr class="opacity-25 my-4">

                        <h5 class="fw-bold mb-3" style="color: #0b203f;">B. Datos de los Padres / Acudientes</h5>

                        <p class="small fw-bold text-muted text-uppercase mb-2">Acudiente 1 (Obligatorio)</p>
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nombres *</label>
                                <input type="text" name="acu1_nombres" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Apellidos *</label>
                                <input type="text" name="acu1_apellidos" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Celular de Contacto *</label>
                                <input type="text" name="acu1_celular" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo Electrónico *</label>
                                <input type="email" name="acu1_email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nacionalidad *</label>
                                <select name="acu1_nacionalidad" class="form-select" required>
                                    <?= obtenerPaisesHTML() ?>
                                </select>
                            </div>
                        </div>

                        <p class="small fw-bold text-muted text-uppercase mb-2">Acudiente 2</p>
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nombres</label>
                                <input type="text" name="acu2_nombres" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="acu2_apellidos" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Celular</label>
                                <input type="text" name="acu2_celular" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="acu2_email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nacionalidad</label>
                                <select name="acu2_nacionalidad" class="form-select">
                                    <option value="">Seleccione país...</option>
                                    <?= obtenerPaisesHTML() ?>
                                </select>
                            </div>
                        </div>

                        <hr class="opacity-25 my-4">

                        <h5 class="fw-bold mb-3" style="color: #0b203f;">C. Agenda de Puertas Abiertas</h5>
                        <div class="mb-4">
                            <label class="form-label">Seleccione la Fecha de Visita Disponible *</label>
                            <select name="fecha_visita" class="form-select" required>
                                <option value="">-- Seleccione una fecha programada por el Liceo --</option>
                                <?php if (count($fechas_disponibles) > 0): ?>
                                    <?php foreach ($fechas_disponibles as $f): ?>
                                        <option value="<?= $f['id'] ?>">
                                            <?= formatearFechaHumana($f['fecha_visita']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay fechas programadas disponibles en este momento.</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" name="politica" class="form-check-input" id="politica" required>
                            <label class="form-check-label text-muted small" for="politica">
                                Autorizo el tratamiento de datos personales de acuerdo con la <a href="https://lf.edu.co/es/politica-de-tratamiento-de-datos-personales/" target="_blank" class="policy-link">política de tratamiento de datos personales *</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-pqr-submit w-100">Registrar Inscripción</button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="https://lf.edu.co" class="btn btn-pqr-back">Volver al sitio web</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paModalExito" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-body text-center p-5">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#198754" class="bi bi-calendar-check-fill" viewBox="0 0 16 16">
                        <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4V.5zM2.545 3h10.91c.3 0 .545.224.545.5v1h-12v-1c0-.276.244-.5.545-.5zM11.584 7.853a.5.5 0 0 1 .708 0l1.5 1.5a.5.5 0 0 1-.708.708l-1.146-1.147-2.646 2.647a.5.5 0 0 1-.708-.708l3-3z"/>
                    </svg>
                </div>
                <h3 class="fw-bold mb-2" style="color: #0b203f;">¡Registro Completado!</h3>
                <p class="text-muted small mb-4"><?= htmlspecialchars($mensaje) ?></p>
                <a href="https://lf.edu.co" class="btn text-white w-100 py-2" style="background-color: #0b203f; border-radius: 8px;">Finalizar y Salir</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleEscolaridad(mostrar) {
    const contenedor = document.getElementById('campos_escolaridad');
    const inst = document.getElementById('asp_institucion');
    const grado = document.getElementById('asp_grado');
    const ciudad = document.getElementById('asp_ciudad');

    if (mostrar) {
        contenedor.style.display = 'block';
        inst.setAttribute('required', 'required');
        grado.setAttribute('required', 'required');
        ciudad.setAttribute('required', 'required');
    } else {
        contenedor.style.display = 'none';
        inst.removeAttribute('required');
        grado.removeAttribute('required');
        ciudad.removeAttribute('required');
        inst.value = ''; grado.value = ''; ciudad.value = '';
    }
}

<?php if ($mensaje && $tipo_mensaje === 'success'): ?>
document.addEventListener("DOMContentLoaded", function() {
    var myModal = new bootstrap.Modal(document.getElementById('paModalExito'));
    myModal.show();
});
<?php endif; ?>
</script>
</body>
</html>
