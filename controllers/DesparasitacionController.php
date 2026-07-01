<?php
require_once '../config/Database.php';
require_once '../models/Desparasitacion.php';

class DesparasitacionController {
    private $db;
    private $model;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->model = new Desparasitacion($this->db);
    }

    public function registrarAjax() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_mascota' => $_POST['id_mascota'],
                'tipo' => $_POST['tipo'],
                'producto' => $_POST['producto'],
                'periodicidad' => $_POST['periodicidad'],
                'fecha_aplicacion' => $_POST['fecha_aplicacion'],
                'observaciones' => $_POST['observaciones']
            ];

            if ($this->model->insert($data)) {
                echo json_encode(['success' => true, 'message' => 'Desparasitación registrada. Próxima dosis calculada.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al registrar la desparasitación']);
            }
            exit;
        }
    }

    public function listarPendientesAjax() {
        $pendientes = $this->model->getPendientesSemana();
        header('Content-Type: application/json');
        echo json_encode($pendientes);
        exit;
    }

    public function registrarNuevoProductoAjax() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre_producto = trim($_POST['nombre_producto']);
            $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'interna';

            if (empty($nombre_producto)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'El nombre del producto es requerido']);
                exit;
            }

            $id_producto = $this->model->insertarNuevoProducto($nombre_producto, $tipo);

            if ($id_producto) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto registrado exitosamente',
                    'id_producto' => $id_producto,
                    'nombre_producto' => $nombre_producto
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al registrar el producto']);
            }
            exit;
        }
    }

    public function getProductosAjax() {
        $query = "SELECT id_producto, nombre_producto, tipo FROM productos_desparasitacion_base WHERE estado = 1 ORDER BY nombre_producto";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'productos' => $productos]);
        exit;
    }
}
?>
