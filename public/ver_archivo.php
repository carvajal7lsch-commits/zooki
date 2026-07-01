<?php
session_start();
if (!isset($_SESSION['usuario_doc'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Acceso denegado. Debe iniciar sesión.");
}

$file = $_GET['file'] ?? null;

if (!$file) {
    header("HTTP/1.1 404 Not Found");
    exit("Archivo no especificado.");
}

// Limpiar la ruta para evitar Directory Traversal
$file = basename($file);
$basePath = "uploads/clinicos/";
$filePath = $basePath . $file;

if (!file_exists($filePath)) {
    header("HTTP/1.1 404 Not Found");
    exit("El archivo no existe.");
}

$mime = mime_content_type($filePath);
header("Content-Type: $mime");
header("Content-Length: " . filesize($filePath));

// Si es PDF, mostrarlo en línea; si es imagen, también.
header("Content-Disposition: inline; filename=\"" . $file . "\"");

readfile($filePath);
exit;
