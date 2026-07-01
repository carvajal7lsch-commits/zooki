<?php
require_once '../config/Database.php';
require_once '../models/Usuario.php';
require_once '../models/Auditoria.php';
require_once '../config/EmailService.php';

class UsuarioController {
    private $db;
    private $usuario;
    private $auditoria;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->usuario = new Usuario($this->db);
        $this->auditoria = new Auditoria($this->db);
    }

    // Listar todos los usuarios para la vista de admin
    public function listar() {
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'administrador') {
            header("Location: index.php?action=dashboard");
            exit();
        }
        return $this->usuario->getAll();
    }

    // Obtener roles para el formulario
    public function getRoles() {
        return $this->usuario->getRoles();
    }

    // Registrar un nuevo usuario (AJAX)
    public function registrarAjax() {
        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $documento = $_POST['documento'];
                $email = $_POST['email'];

                // Verificar si el documento ya existe
                $existingDoc = $this->usuario->getById($documento);
                if ($existingDoc) {
                    echo json_encode(['success' => false, 'message' => 'El documento ya está registrado en el sistema.']);
                    exit;
                }

                // Verificar si el email ya existe
                $existingEmail = $this->usuario->getUserByEmail($email);
                if ($existingEmail) {
                    echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado en el sistema.']);
                    exit;
                }

                $data = [
                    'documento' => $documento,
                    'tipo_documento' => $_POST['tipo_documento'],
                    'nombre_completo' => $_POST['nombre_completo'],
                    'telefono' => $_POST['telefono'],
                    'email' => $email,
                    'password' => password_hash(isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : '12345', PASSWORD_DEFAULT),
                    'id_rol' => $_POST['id_rol'],
                    'estado' => isset($_POST['estado']) ? (int) $_POST['estado'] : 1,
                    'debe_cambiar_password' => 1
                ];

                if ($this->usuario->create($data)) {
                    // Auditoría: usuario creado
                    $adminDoc = $_SESSION['usuario_doc'] ?? 'sistema';
                    $this->auditoria->log($adminDoc, 'INSERT', 'usuarios', $documento, null, [
                        'nombre_completo' => $_POST['nombre_completo'],
                        'email' => $email,
                        'id_rol' => $_POST['id_rol']
                    ], 'Usuario creado');

                    // Enviar correo con credenciales
                    $password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : '12345';
                    $emailService = new EmailService();
                    $enviado = $emailService->enviarCredencialesUsuario($email, $_POST['nombre_completo'], $documento, $password);
                    
                    if ($enviado) {
                        echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente. Se han enviado las credenciales al correo registrado.']);
                    } else {
                        echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente. No se pudo enviar el correo con las credenciales, pero el usuario fue creado correctamente.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al crear el usuario.']);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Actualizar usuario (AJAX)
    public function actualizarAjax() {
        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $documento = $_POST['documento'];
                $email = $_POST['email'];
                $original_doc = $_POST['original_doc'];

                // Si el documento está vacío (porque el input está deshabilitado), usar el original
                if (empty($documento)) {
                    $documento = $original_doc;
                }

                // Debug info
                $debugInfo = "Documento: '$documento', Original: '$original_doc', Son iguales: " . ($documento == $original_doc ? "SÍ" : "NO");

                // Verificar si el email ya existe (excluyendo el usuario actual)
                $existingEmail = $this->usuario->getUserByEmailExcluding($email, $original_doc);
                if ($existingEmail) {
                    echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado en el sistema.']);
                    exit;
                }

                $data = [
                    'documento' => $documento,
                    'tipo_documento' => $_POST['tipo_documento'],
                    'original_doc' => $original_doc,
                    'nombre_completo' => $_POST['nombre_completo'],
                    'telefono' => $_POST['telefono'],
                    'email' => $email,
                    'id_rol' => $_POST['id_rol'],
                    'estado' => $_POST['estado']
                ];

                if ($this->usuario->update($data)) {
                    // Auditoría: usuario actualizado
                    $adminDoc = $_SESSION['usuario_doc'] ?? 'sistema';
                    $this->auditoria->log($adminDoc, 'UPDATE', 'usuarios', $documento, ['original_doc' => $original_doc], [
                        'nombre_completo' => $_POST['nombre_completo'],
                        'email' => $email,
                        'id_rol' => $_POST['id_rol'],
                        'estado' => $_POST['estado']
                    ], 'Usuario actualizado');
                    echo json_encode(['success' => true, 'message' => 'Usuario actualizado.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar.']);
                }
            }
        } catch (PDOException $e) {
            // Verificar si es un error de restricción de clave foránea
            if (strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
                $debugInfo = isset($debugInfo) ? $debugInfo : "No disponible";
                echo json_encode(['success' => false, 'message' => "Error de restricción de clave foránea. Debug: $debugInfo. Error: " . $e->getMessage()]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Obtener un usuario por documento (AJAX)
    public function getUsuarioAjax() {
        try {
            $documento = $_GET['documento'] ?? '';
            error_log("getUsuarioAjax - documento recibido: '$documento'");
            
            $u = $this->usuario->getById($documento);
            error_log("getUsuarioAjax - resultado: " . ($u ? "encontrado" : "no encontrado"));
            
            if ($u) {
                error_log("getUsuarioAjax - datos: " . json_encode($u));
            }
            
            echo json_encode($u);
        } catch (Exception $e) {
            error_log("getUsuarioAjax - error: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Cambiar estado del usuario (AJAX)
    public function cambiarEstadoAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['documento']) && isset($_POST['estado'])) {
                $doc = $_POST['documento'];
                $est = $_POST['estado'];
                if ($this->usuario->updateStatus($doc, $est)) {
                    // Auditoría: cambio de estado
                    $adminDoc = $_SESSION['usuario_doc'] ?? 'sistema';
                    $this->auditoria->log($adminDoc, 'UPDATE', 'usuarios', $doc, ['estado_anterior' => 'desconocido'], ['estado_nuevo' => $est], 'Estado de usuario cambiado a ' . $est);
                    echo json_encode(['success' => true, 'message' => 'Estado actualizado exitosamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado.']);
                }
            }
        }
    }

    // Verificar si documento ya existe (AJAX)
    public function verificarDocumentoAjax() {
        $documento = $_GET['documento'] ?? '';
        $exclude_doc = $_GET['exclude_doc'] ?? '';
        
        if ($exclude_doc && $documento == $exclude_doc) {
            // Es el mismo documento, no está duplicado
            echo json_encode(['exists' => false]);
            return;
        }
        
        $existing = $this->usuario->getById($documento);
        echo json_encode(['exists' => $existing !== false]);
    }

    // Verificar si email ya existe (AJAX)
    public function verificarEmailAjax() {
        $email = $_GET['email'] ?? '';
        $exclude_doc = $_GET['exclude_doc'] ?? '';
        
        if ($exclude_doc) {
            $existing = $this->usuario->getUserByEmailExcluding($email, $exclude_doc);
        } else {
            $existing = $this->usuario->getUserByEmail($email);
        }
        
        echo json_encode(['exists' => $existing !== false]);
    }
}
