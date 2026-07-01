<?php

require_once '../config/Database.php';
require_once '../models/Mascota.php';
require_once '../models/Usuario.php';

class MascotaController {
    private $db;
    private $mascotaModel;
    private $usuarioModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->mascotaModel = new Mascota($this->db);
        $this->usuarioModel = new Usuario($this->db);
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validaciones básicas
            $nombre = trim($_POST['nombre']);
            $especie = $_POST['especie'];
            $doc_propietario = trim($_POST['doc_propietario']);
            $peso = trim($_POST['peso']);

            if (empty($nombre) || empty($especie) || empty($doc_propietario) || empty($peso)) {
                $this->redirectWithError("Por favor complete los campos obligatorios (*).");
            }

            // IMPORTANTE: El HC ya no se genera al registrar la mascota (Sprint 2)
            $hc = "";

            // Verificar si el propietario existe
            $propietario = $this->usuarioModel->getUserByDocumento($doc_propietario);
            if (!$propietario) {
                $this->redirectWithError("El propietario con documento $doc_propietario no está registrado.");
            }

            // Manejo de la foto
            $foto_nombre = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png'];
                $filename = $_FILES['foto']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $filesize = $_FILES['foto']['size'];

                if (!in_array($ext, $allowed)) {
                    $this->redirectWithError("Solo se permiten archivos JPG o PNG.");
                }

                if ($filesize > 5 * 1024 * 1024) {
                    $this->redirectWithError("La foto no debe superar los 5MB.");
                }

                $foto_nombre = time() . '_' . str_replace(' ', '_', $nombre) . '.' . $ext;
                $target_path = '../public/uploads/mascotas/' . $foto_nombre;

                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
                    $this->redirectWithError("Error al subir la imagen.");
                }
            }

            // Preparar datos para el modelo
            $data = [
                'numero_historia_clinica' => $hc,
                'doc_propietario' => $doc_propietario,
                'nombre' => $nombre,
                'id_especie' => $_POST['especie'],
                'id_raza' => $_POST['raza'],
                'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
                'peso' => $peso,
                'sexo' => $_POST['sexo'],
                'color' => '', // Legacy temporal
                'url_foto' => $foto_nombre
            ];

            $newId = $this->mascotaModel->insert($data);
            if ($newId) {
                // Guardar Colores (Relación Muchos a Muchos)
                $colores = $_POST['colores'] ?? [];
                $this->mascotaModel->saveColores($newId, $colores);

                $_SESSION['success_message'] = "¡Mascota registrada con éxito!";
                header("Location: index.php?action=dashboard");
                exit();
            } else {
                $this->redirectWithError("Error al guardar en la base de datos.");
            }
        }
    }

    public function editar() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: index.php?action=dashboard");
            exit();
        }

        $mascota = $this->mascotaModel->getById($id);
        if (!$mascota) {
            $this->redirectWithError("Mascota no encontrada.");
        }

        return $mascota;
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id_mascota'];
            $oldData = $this->mascotaModel->getById($id);
            
            if (!$oldData) {
                $this->redirectWithError("Error al identificar la mascota.");
            }

            $newData = [
                'id_mascota' => $id,
                'nombre' => trim($_POST['nombre']),
                'id_especie' => $_POST['especie'],
                'id_raza' => $_POST['raza'],
                'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
                'peso' => trim($_POST['peso']),
                'sexo' => $_POST['sexo'],
                'color' => '', // Legacy temporal
                'estado' => $_POST['estado'],
                'url_foto' => $oldData['url_foto'] // Por defecto mantenemos la vieja
            ];

            // Manejo de nueva foto si se sube
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $foto_nombre = time() . '_' . str_replace(' ', '_', $newData['nombre']) . '.' . pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], '../public/uploads/mascotas/' . $foto_nombre)) {
                    $newData['url_foto'] = $foto_nombre;
                }
            }

            if ($this->mascotaModel->update($newData)) {
                // Registrar Auditoría (Solo campos que cambiaron)
                $camposAValidar = ['nombre', 'id_especie', 'id_raza', 'fecha_nacimiento', 'peso', 'sexo', 'estado'];
                foreach ($camposAValidar as $campo) {
                    if ($oldData[$campo] != $newData[$campo]) {
                        $this->mascotaModel->registrarAuditoria($id, $_SESSION['usuario_doc'], $campo, $oldData[$campo], $newData[$campo]);
                    }
                }

                // Actualizar colores
                $colores = $_POST['colores'] ?? [];
                $this->mascotaModel->saveColores($id, $colores);

                $_SESSION['success_message'] = "¡Mascota actualizada correctamente!";
                header("Location: index.php?action=dashboard");
                exit();
            } else {
                $this->redirectWithError("Error al actualizar.");
            }
        }
    }

    public function listar() {
        return $this->mascotaModel->getAll();
    }

    public function listarMascotasAjax() {
        $mascotas = $this->mascotaModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($mascotas);
        exit;
    }

    public function buscar() {
        header('Content-Type: application/json');
        $term = $_GET['query'] ?? '';
        if (strlen($term) < 2) {
            echo json_encode([]);
            return;
        }

        $resultados = $this->mascotaModel->search($term);
        echo json_encode($resultados);
    }

    public function getMascotaAjax() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $mascota = $this->mascotaModel->getById($id);
            header('Content-Type: application/json');
            echo json_encode($mascota);
            exit;
        }
    }

    public function actualizarAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id_mascota'];
            $id_raza = $_POST['raza'];
            
            // Obtener datos actuales para la auditoría y la foto antigua
            $oldData = $this->mascotaModel->getById($id);

            if ($id_raza === 'Otra' && !empty($_POST['nueva_raza'])) {
                $id_raza = $this->mascotaModel->insertRaza($_POST['especie'], $_POST['nueva_raza']);
            }

            $newData = [
                'id_mascota' => $id,
                'nombre' => trim($_POST['nombre']),
                'id_especie' => $_POST['especie'],
                'id_raza' => $id_raza,
                'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : ($oldData['fecha_nacimiento'] ?? null),
                'peso' => trim($_POST['peso']),
                'sexo' => $_POST['sexo'],
                'estado' => $_POST['estado'],
                'url_foto' => $oldData['url_foto'] ?? null
            ];

            // Si hay cambio de dueño
            if (!empty($_POST['doc_propietario'])) {
                $newData['doc_propietario'] = $_POST['doc_propietario'];
            }

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] != UPLOAD_ERR_NO_FILE) {
                if ($_FILES['foto']['error'] != UPLOAD_ERR_OK) {
                    $error_msg = 'Error al subir el archivo.';
                    switch ($_FILES['foto']['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $error_msg = 'La foto excede el límite máximo de tamaño de archivo (5MB).';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $error_msg = 'El archivo se subió solo parcialmente.';
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $error_msg = 'Falta una carpeta temporal en el servidor.';
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $error_msg = 'No se pudo escribir el archivo en el disco.';
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $error_msg = 'Una extensión de PHP detuvo la subida del archivo.';
                            break;
                    }
                    echo json_encode(['success' => false, 'message' => $error_msg]);
                    exit;
                }

                $allowed = ['jpg', 'jpeg', 'png'];
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    echo json_encode(['success' => false, 'message' => 'Solo se permiten imágenes en formato JPG o PNG.']);
                    exit;
                }

                if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'La foto no debe superar los 5MB.']);
                    exit;
                }

                $foto_nombre = time() . '_' . str_replace(' ', '_', $newData['nombre']) . '.' . $ext;
                $target_dir = '../public/uploads/mascotas/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_nombre)) {
                    $newData['url_foto'] = $foto_nombre;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen en el servidor. Verifique permisos.']);
                    exit;
                }
            }

            if ($this->mascotaModel->update($newData)) {
                // Guardar Colores
                $colores = $_POST['colores'] ?? [];
                $this->mascotaModel->saveColores($id, $colores);

                // Auditoría
                $campos = ['nombre', 'id_especie', 'id_raza', 'peso', 'sexo', 'estado'];
                foreach ($campos as $c) {
                    if (isset($oldData[$c]) && isset($newData[$c]) && $oldData[$c] != $newData[$c]) {
                        $this->mascotaModel->registrarAuditoria($id, $_SESSION['usuario_doc'], $c, $oldData[$c], $newData[$c]);
                    }
                }
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
                exit;
            }
        }
    }

    public function registrarAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim($_POST['nombre']);
            $especie = $_POST['especie'];
            $doc_propietario = trim($_POST['doc_propietario']);
            $peso = trim($_POST['peso']);

            if (empty($nombre) || empty($especie) || empty($doc_propietario)) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios']);
                return;
            }

            // Sprint 2: El HC se asigna en la primera consulta médica
            $hc = "";
            
            $foto_nombre = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] != UPLOAD_ERR_NO_FILE) {
                if ($_FILES['foto']['error'] != UPLOAD_ERR_OK) {
                    $error_msg = 'Error al subir el archivo.';
                    switch ($_FILES['foto']['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $error_msg = 'La foto excede el límite máximo de tamaño de archivo (5MB).';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $error_msg = 'El archivo se subió solo parcialmente.';
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $error_msg = 'Falta una carpeta temporal en el servidor.';
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $error_msg = 'No se pudo escribir el archivo en el disco.';
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $error_msg = 'Una extensión de PHP detuvo la subida del archivo.';
                            break;
                    }
                    echo json_encode(['success' => false, 'message' => $error_msg]);
                    exit;
                }

                $allowed = ['jpg', 'jpeg', 'png'];
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    echo json_encode(['success' => false, 'message' => 'Solo se permiten imágenes en formato JPG o PNG.']);
                    exit;
                }

                if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'La foto no debe superar los 5MB.']);
                    exit;
                }

                $foto_nombre = time() . '_' . str_replace(' ', '_', $nombre) . '.' . $ext;
                $target_dir = '../public/uploads/mascotas/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_nombre)) {
                    echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen en el servidor. Verifique permisos.']);
                    exit;
                }
            }

            $id_raza = $_POST['raza'];
            if ($id_raza === 'Otra' && !empty($_POST['nueva_raza'])) {
                $id_raza = $this->mascotaModel->insertRaza($_POST['especie'], $_POST['nueva_raza']);
            }

            $data = [
                'numero_historia_clinica' => $hc,
                'doc_propietario' => $doc_propietario,
                'nombre' => $nombre,
                'id_especie' => $_POST['especie'],
                'id_raza' => $id_raza,
                'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
                'peso' => $peso,
                'sexo' => $_POST['sexo'],
                'color' => '', // Legacy temporal
                'url_foto' => $foto_nombre
            ];

            $newId = $this->mascotaModel->insert($data);
            if ($newId) {
                // Guardar Colores
                $colores = $_POST['colores'] ?? [];
                $this->mascotaModel->saveColores($newId, $colores);
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Error en DB']);
                exit;
            }
        }
    }

    public function listarPropietariosAjax() {
        $owners = $this->usuarioModel->getAllOwnersWithPetCount();
        header('Content-Type: application/json');
        echo json_encode($owners);
        exit;
    }

    public function listarMascotasPorPropietarioAjax() {
        $doc = $_GET['doc'] ?? null;
        if ($doc) {
            $pets = $this->mascotaModel->getByPropietario($doc);
            header('Content-Type: application/json');
            echo json_encode($pets);
            exit;
        }
    }

    public function getPropietarioAjax() {
        $doc = $_GET['doc'] ?? null;
        if ($doc) {
            $owner = $this->usuarioModel->getById($doc);
            header('Content-Type: application/json');
            echo json_encode($owner);
            exit;
        }
    }

    public function actualizarPropietarioAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'documento' => $_POST['documento'],
                'nombre_completo' => $_POST['nombre_completo'],
                'telefono' => $_POST['telefono'],
                'email' => $_POST['email'],
                'estado' => $_POST['estado']
            ];

            if ($this->usuarioModel->update($data)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar propietario']);
            }
        }
    }

    public function listarEspeciesAjax() {
        $especies = $this->mascotaModel->getEspecies();
        header('Content-Type: application/json');
        echo json_encode($especies);
        exit;
    }

    public function registrarEspecieAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim($_POST['nombre_especie']);
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre de la especie no puede estar vacío.']);
                exit;
            }
            
            // Check if species already exists case-insensitively
            $stmt = $this->db->prepare("SELECT id_especie FROM especies WHERE LOWER(nombre_especie) = LOWER(?)");
            $stmt->execute([$nombre]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                echo json_encode(['success' => true, 'id_especie' => $existing['id_especie'], 'nombre_especie' => $nombre]);
                exit;
            }

            $id = $this->mascotaModel->insertEspecie($nombre);
            if ($id) {
                echo json_encode(['success' => true, 'id_especie' => $id, 'nombre_especie' => $nombre]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar la especie en la base de datos.']);
                exit;
            }
        }
    }

    public function listarRazasAjax() {
        $id_especie = $_GET['id_especie'] ?? null;
        if ($id_especie) {
            $razas = $this->mascotaModel->getRazasByEspecie($id_especie);
            header('Content-Type: application/json');
            echo json_encode($razas);
            exit;
        }
    }

    public function listarColoresAjax() {
        $colores = $this->mascotaModel->getColoresBase();
        header('Content-Type: application/json');
        echo json_encode($colores);
        exit;
    }

    public function registrarColorAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim($_POST['nombre_color']);
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre del color no puede estar vacío.']);
                exit;
            }

            // Check if color already exists case-insensitively
            $stmt = $this->db->prepare("SELECT id_color FROM colores_base WHERE LOWER(nombre_color) = LOWER(?)");
            $stmt->execute([$nombre]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                echo json_encode(['success' => true, 'id_color' => $existing['id_color'], 'nombre_color' => $nombre]);
                exit;
            }

            $id = $this->mascotaModel->insertColor($nombre);
            if ($id) {
                echo json_encode(['success' => true, 'id_color' => $id, 'nombre_color' => $nombre]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar el color en la base de datos.']);
                exit;
            }
        }
    }

    public function cambiarEstadoAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['id_mascota']) && isset($_POST['estado'])) {
                $id = $_POST['id_mascota'];
                $est = $_POST['estado'];
                if ($this->mascotaModel->updateStatus($id, $est)) {
                    require_once '../models/Auditoria.php';
                    $auditoria = new Auditoria($this->db);
                    $adminDoc = $_SESSION['usuario_doc'] ?? 'sistema';
                    $auditoria->log($adminDoc, 'UPDATE', 'mascotas', $id, ['estado_anterior' => 'desconocido'], ['estado_nuevo' => $est], 'Estado de mascota cambiado a ' . $est);
                    echo json_encode(['success' => true, 'message' => 'Estado de mascota cambiado a ' . $est]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado de la mascota.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Parámetros incompletos.']);
            }
            exit;
        }
    }

    private function redirectWithError($message) {
        $_SESSION['error_message'] = $message;
        header("Location: index.php?action=nueva_mascota");
        exit();
    }
}
?>
