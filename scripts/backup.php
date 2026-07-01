<?php
/**
 * Script de backup automático de base de datos (HU-23)
 * 
 * Uso:
 *   php scripts/backup.php
 * 
 * Cron job recomendado (diario a las 3:00 AM):
 *   0 3 * * * cd /ruta/al/proyecto && php scripts/backup.php >> logs/backup.log 2>&1
 */

// Configuración
$backupDir = __DIR__ . '/../backups';
$retencionDias = 7;
$dbHost = 'localhost';
$dbName = 'zooki_db';
$dbUser = 'root';
$dbPass = '';

// Crear directorio de backups si no existe
if (!is_dir($backupDir)) {
    if (!mkdir($backupDir, 0755, true)) {
        error_log("[BACKUP ERROR] No se pudo crear el directorio: $backupDir");
        exit(1);
    }
}

$fecha = date('Y-m-d_H-i-s');
$archivoSql = "$backupDir/{$dbName}_{$fecha}.sql";
$archivoGz = "$archivoSql.gz";

// 1. Ejecutar mysqldump
$command = sprintf(
    'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s 2>&1',
    escapeshellarg($dbHost),
    escapeshellarg($dbUser),
    escapeshellarg($dbPass),
    escapeshellarg($dbName),
    escapeshellarg($archivoSql)
);

exec($command, $output, $returnCode);

if ($returnCode !== 0) {
    $errorMsg = implode("\n", $output);
    error_log("[BACKUP ERROR] mysqldump falló: $errorMsg");
    if (file_exists($archivoSql)) {
        unlink($archivoSql);
    }
    exit(1);
}

// 2. Verificar que el dump no esté vacío o corrupto
$tamano = filesize($archivoSql);
if ($tamano === false || $tamano < 1024) {
    error_log("[BACKUP ERROR] El archivo SQL generado está vacío o es muy pequeño ($tamano bytes)");
    unlink($archivoSql);
    exit(1);
}

// Validar que contenga al menos una sentencia CREATE TABLE
$contenidoMuestra = file_get_contents($archivoSql, false, null, 0, 5000);
if (strpos($contenidoMuestra, 'CREATE TABLE') === false) {
    error_log("[BACKUP ERROR] El archivo SQL no contiene sentencias CREATE TABLE. Posiblemente corrupto.");
    unlink($archivoSql);
    exit(1);
}

// 3. Comprimir con gzip
$commandGz = sprintf('gzip -f %s 2>&1', escapeshellarg($archivoSql));
exec($commandGz, $outputGz, $returnCodeGz);

if ($returnCodeGz !== 0 || !file_exists($archivoGz)) {
    error_log("[BACKUP ERROR] Falló la compresión gzip. Se conserva el archivo SQL sin comprimir.");
    $archivoFinal = $archivoSql; // Conservar sin comprimir si gzip falla
} else {
    $archivoFinal = $archivoGz;
    $tamanoGz = filesize($archivoGz);
    $ratio = round((1 - ($tamanoGz / $tamano)) * 100, 1);
}

// 4. Rotación: eliminar backups con más de 7 días
$eliminados = 0;
foreach (glob("$backupDir/{$dbName}_*.sql*") as $archivo) {
    $edadDias = (time() - filemtime($archivo)) / 86400;
    if ($edadDias > $retencionDias) {
        if (unlink($archivo)) {
            $eliminados++;
        }
    }
}

// 5. Registrar resultado
$tamanoFinal = filesize($archivoFinal);
$msg = sprintf(
    "[BACKUP OK] %s | Archivo: %s | Tamaño: %s | SQL original: %s | Eliminados antiguos: %d",
    date('Y-m-d H:i:s'),
    basename($archivoFinal),
    formatoBytes($tamanoFinal),
    formatoBytes($tamano),
    $eliminados
);
error_log($msg);
echo $msg . PHP_EOL;

// 6. Guardar también en un log específico de backups
$logFile = dirname($backupDir) . '/logs/backup.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
file_put_contents($logFile, $msg . PHP_EOL, FILE_APPEND | LOCK_EX);

exit(0);

function formatoBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= 1024 ** $pow;
    return round($bytes, $precision) . ' ' . $units[$pow];
}
