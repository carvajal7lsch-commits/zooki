<?php
/**
 * Script para enviar recordatorios automáticos de vacunación.
 * Se debe configurar en Cron Job (Linux) o Task Scheduler (Windows).
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/config/EmailService.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$database = new Database();
$db = $database->getConnection();

echo "Iniciando envío de recordatorios...\n";

// ── 1. Recordatorio de citas para mañana ──
$queryCitas = "
    SELECT c.id_cita, c.fecha, c.hora, c.motivo, m.nombre as mascota_nombre,
           u.documento as doc_propietario, u.nombre_completo as prop_nombre, u.email,
           v.nombre_completo as vet_nombre
    FROM citas c
    JOIN mascotas m ON c.id_mascota = m.id_mascota
    JOIN usuarios u ON m.doc_propietario = u.documento
    JOIN usuarios v ON c.doc_veterinario = v.documento
    WHERE c.fecha = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
      AND c.estado IN ('pendiente','confirmada')
      AND u.email IS NOT NULL AND u.email != ''
";
$stmtCitas = $db->prepare($queryCitas);
$stmtCitas->execute();
$citasManana = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);

foreach ($citasManana as $c) {
    $tipo_notificacion = 'recordatorio_cita_24h';

    // Verificar duplicado
    $checkQuery = "SELECT id_notificacion FROM notificaciones
                   WHERE id_entidad = :id_entidad
                   AND tipo_entidad = 'cita'
                   AND tipo_notificacion = :tipo_noti";
    $chkStmt = $db->prepare($checkQuery);
    $chkStmt->execute([':id_entidad' => $c['id_cita'], ':tipo_noti' => $tipo_notificacion]);
    if ($chkStmt->rowCount() > 0) continue;

    $emailService->limpiarDirecciones();
    $fechaStr = date('d/m/Y', strtotime($c['fecha']));
    $horaStr = substr($c['hora'], 0, 5);
    $asunto = "Recordatorio de cita: {$c['mascota_nombre']} - {$fechaStr} {$horaStr}";

    $cuerpo = "
    <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;'>
        <div style='background-color: #0C66E4; padding: 20px; text-align: center; color: white;'>
            <h2>Recordatorio de Cita - Zooki</h2>
        </div>
        <div style='padding: 20px;'>
            <p>Hola <strong>{$c['prop_nombre']}</strong>,</p>
            <p>Te recordamos que tienes una cita programada para <strong>mañana</strong> con tu mascota <strong>{$c['mascota_nombre']}</strong>.</p>
            <div style='background-color: #f8fafc; padding: 15px; border-left: 4px solid #0C66E4; margin: 20px 0;'>
                <p style='margin: 5px 0;'><strong>Fecha:</strong> {$fechaStr}</p>
                <p style='margin: 5px 0;'><strong>Hora:</strong> {$horaStr}</p>
                <p style='margin: 5px 0;'><strong>Motivo:</strong> {$c['motivo']}</p>
                <p style='margin: 5px 0;'><strong>Veterinario:</strong> Dr(a). {$c['vet_nombre']}</p>
            </div>
            <p>Por favor llega puntual. Si necesitas reprogramar, contáctanos con anticipación.</p>
            <br>
            <p>Atentamente,<br><strong>El equipo de Zooki</strong></p>
        </div>
    </div>";

    $enviado = $emailService->enviarCorreoPersonalizado($c['email'], $c['prop_nombre'], $asunto, $cuerpo);
    $estado = $enviado ? 'enviado' : 'error';
    if ($enviado) echo "[CITA] Enviado a {$c['email']} para {$c['mascota_nombre']}\n";
    else echo "[CITA] Error al enviar a {$c['email']}\n";

    $logQuery = "INSERT INTO notificaciones
                 (doc_propietario, tipo_entidad, id_entidad, destinatario_email, tipo_notificacion, asunto, mensaje, estado)
                 VALUES (:doc_propietario, 'cita', :id_entidad, :email, :tipo_noti, :asunto, :mensaje, :estado)";
    $db->prepare($logQuery)->execute([
        ':doc_propietario' => $c['doc_propietario'],
        ':id_entidad' => $c['id_cita'],
        ':email' => $c['email'],
        ':tipo_noti' => $tipo_notificacion,
        ':asunto' => $asunto,
        ':mensaje' => 'Recordatorio cita 24h',
        ':estado' => $estado
    ]);
}

// ── 2. Recordatorio de vacunas y desparasitaciones ──
$query = "
    SELECT 'vacuna' as tipo_entidad, v.id_vacuna as id_entidad, v.nombre_vacuna as nombre_item, v.fecha_proxima_dosis as fecha_proxima, m.nombre as mascota_nombre, u.documento as doc_propietario, u.nombre_completo as prop_nombre, u.email 
    FROM vacunas v
    JOIN mascotas m ON v.id_mascota = m.id_mascota
    JOIN usuarios u ON m.doc_propietario = u.documento
    WHERE u.email IS NOT NULL AND u.email != '' 
    AND (v.fecha_proxima_dosis = DATE_ADD(CURDATE(), INTERVAL 7 DAY) OR v.fecha_proxima_dosis = DATE_ADD(CURDATE(), INTERVAL 1 DAY))
    
    UNION ALL
    
    SELECT 'desparasitacion' as tipo_entidad, d.id_desparasitacion as id_entidad, CONCAT('Desparasitación ', d.tipo, ' (', d.producto, ')') as nombre_item, d.fecha_proxima as fecha_proxima, m.nombre as mascota_nombre, u.documento as doc_propietario, u.nombre_completo as prop_nombre, u.email 
    FROM desparasitaciones d
    JOIN mascotas m ON d.id_mascota = m.id_mascota
    JOIN usuarios u ON m.doc_propietario = u.documento
    WHERE u.email IS NOT NULL AND u.email != '' 
    AND (d.fecha_proxima = DATE_ADD(CURDATE(), INTERVAL 7 DAY) OR d.fecha_proxima = DATE_ADD(CURDATE(), INTERVAL 1 DAY))
";

$stmt = $db->prepare($query);
$stmt->execute();
$recordatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($recordatorios) === 0 && count($citasManana) === 0) {
    echo "No hay recordatorios pendientes para hoy.\n";
    exit;
}

$emailService = new EmailService();

foreach ($recordatorios as $v) {
    $fecha_prox = new DateTime($v['fecha_proxima']);
    $hoy = new DateTime();
    $diferencia = $hoy->diff($fecha_prox)->days;

    $tipo_notificacion = ($diferencia > 3) ? 'recordatorio_7_dias' : 'recordatorio_1_dia';

    // Verificar si YA se envió esta misma notificación para no duplicar
    $checkQuery = "SELECT id_notificacion FROM notificaciones
                   WHERE id_entidad = :id_entidad
                   AND tipo_entidad = :tipo_entidad
                   AND tipo_notificacion = :tipo_noti";
    $chkStmt = $db->prepare($checkQuery);
    $chkStmt->execute([
        ':id_entidad' => $v['id_entidad'],
        ':tipo_entidad' => $v['tipo_entidad'],
        ':tipo_noti' => $tipo_notificacion
    ]);

    if ($chkStmt->rowCount() > 0) {
        continue; // Ya se envió, saltar
    }

    $emailService->limpiarDirecciones();

    $tipo_texto = $v['tipo_entidad'] == 'vacuna' ? 'Vacunación' : 'Desparasitación';
    $asunto = "Recordatorio de $tipo_texto: " . $v['mascota_nombre'];

    // Plantilla HTML
    $cuerpo = "
    <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;'>
        <div style='background-color: #0f172a; padding: 20px; text-align: center; color: white;'>
            <h2>Clínica Veterinaria Zooki 🐾</h2>
        </div>
        <div style='padding: 20px;'>
            <p>Hola <strong>{$v['prop_nombre']}</strong>,</p>
            <p>Te recordamos que el próximo procedimiento médico para tu mascota <strong>{$v['mascota_nombre']}</strong> está programado para pronto.</p>

            <div style='background-color: #f8fafc; padding: 15px; border-left: 4px solid #3b82f6; margin: 20px 0;'>
                <p style='margin: 5px 0;'><strong>Procedimiento:</strong> {$v['nombre_item']}</p>
                <p style='margin: 5px 0;'><strong>Fecha Programada:</strong> " . $fecha_prox->format('d/m/Y') . "</p>
            </div>

            <p>Por favor, comunícate con nosotros para agendar la cita y mantener a {$v['mascota_nombre']} protegido(a).</p>
            <br>
            <p>Atentamente,<br><strong>El equipo de Zooki</strong></p>
        </div>
    </div>";

    // Intentar enviar usando EmailService
    $enviado = $emailService->enviarCorreoPersonalizado($v['email'], $v['prop_nombre'], $asunto, $cuerpo);
    $estado = $enviado ? 'enviado' : 'error';

    if ($enviado) {
        echo "Enviado a {$v['email']} para {$v['mascota_nombre']} ({$v['tipo_entidad']})\n";
    } else {
        echo "Error al enviar a {$v['email']}\n";
    }

    // Registrar en Base de Datos
    $logQuery = "INSERT INTO notificaciones
                 (doc_propietario, tipo_entidad, id_entidad, destinatario_email, tipo_notificacion, asunto, mensaje, estado)
                 VALUES (:doc_propietario, :tipo_entidad, :id_entidad, :email, :tipo_noti, :asunto, :mensaje, :estado)";

    $logStmt = $db->prepare($logQuery);
    $logStmt->execute([
        ':doc_propietario' => $v['doc_propietario'],
        ':tipo_entidad' => $v['tipo_entidad'],
        ':id_entidad' => $v['id_entidad'],
        ':email' => $v['email'],
        ':tipo_noti' => $tipo_notificacion,
        ':asunto' => $asunto,
        ':mensaje' => "Cuerpo del correo guardado",
        ':estado' => $estado
    ]);
}

echo "Proceso finalizado.\n";
?>
