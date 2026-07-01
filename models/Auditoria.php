<?php

class Auditoria {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Registra una acción en la auditoría del sistema
     *
     * @param string|null $usuarioDoc Documento del usuario o null si no está autenticado
     * @param string $accion LOGIN, LOGIN_FAIL, LOGOUT, INSERT, UPDATE, DELETE, VIEW, OTHER
     * @param string|null $tabla Tabla afectada
     * @param string|null $registroId ID del registro afectado
     * @param array|null $datosAnteriores Datos antes del cambio
     * @param array|null $datosNuevos Datos después del cambio
     * @param string|null $descripcion Descripción legible de la acción
     * @return bool
     */
    public function log(
        $usuarioDoc = null,
        $accion = 'OTHER',
        $tabla = null,
        $registroId = null,
        $datosAnteriores = null,
        $datosNuevos = null,
        $descripcion = null
    ) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO auditoria_sistema
                (usuario_doc, ip_address, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos, descripcion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $jsonAnteriores = $datosAnteriores ? json_encode($datosAnteriores, JSON_UNESCAPED_UNICODE) : null;
            $jsonNuevos = $datosNuevos ? json_encode($datosNuevos, JSON_UNESCAPED_UNICODE) : null;

            return $stmt->execute([
                $usuarioDoc,
                $ip,
                $accion,
                $tabla,
                $registroId,
                $jsonAnteriores,
                $jsonNuevos,
                $descripcion
            ]);
        } catch (Exception $e) {
            error_log("Error en auditoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene registros de auditoría con filtros opcionales
     */
    public function getLogs($filtros = [], $limit = 100, $offset = 0) {
        $sql = "SELECT * FROM auditoria_sistema WHERE 1=1";
        $params = [];

        if (!empty($filtros['usuario_doc'])) {
            $sql .= " AND usuario_doc = ?";
            $params[] = $filtros['usuario_doc'];
        }
        if (!empty($filtros['accion'])) {
            $sql .= " AND accion = ?";
            $params[] = $filtros['accion'];
        }
        if (!empty($filtros['tabla'])) {
            $sql .= " AND tabla_afectada = ?";
            $params[] = $filtros['tabla'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND fecha_hora >= ?";
            $params[] = $filtros['fecha_desde'] . ' 00:00:00';
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND fecha_hora <= ?";
            $params[] = $filtros['fecha_hasta'] . ' 23:59:59';
        }

        $limit = (int) $limit;
        $offset = (int) $offset;
        $sql .= " ORDER BY fecha_hora DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta total de registros para paginación
     */
    public function countLogs($filtros = []) {
        $sql = "SELECT COUNT(*) FROM auditoria_sistema WHERE 1=1";
        $params = [];

        if (!empty($filtros['usuario_doc'])) {
            $sql .= " AND usuario_doc = ?";
            $params[] = $filtros['usuario_doc'];
        }
        if (!empty($filtros['accion'])) {
            $sql .= " AND accion = ?";
            $params[] = $filtros['accion'];
        }
        if (!empty($filtros['tabla'])) {
            $sql .= " AND tabla_afectada = ?";
            $params[] = $filtros['tabla'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND fecha_hora >= ?";
            $params[] = $filtros['fecha_desde'] . ' 00:00:00';
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND fecha_hora <= ?";
            $params[] = $filtros['fecha_hasta'] . ' 23:59:59';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene estadísticas de acciones para el dashboard
     */
    public function getStats($dias = 7) {
        try {
            $dias = (int) $dias;
            $stmt = $this->db->prepare("
                SELECT accion, COUNT(*) as total
                FROM auditoria_sistema
                WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL {$dias} DAY)
                GROUP BY accion
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            error_log("Error en getStats auditoria: " . $e->getMessage());
            return [];
        }
    }

    public function getDistinctAcciones() {
        $stmt = $this->db->query("SELECT DISTINCT accion FROM auditoria_sistema ORDER BY accion");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getDistinctTablas() {
        $stmt = $this->db->query("SELECT DISTINCT tabla_afectada FROM auditoria_sistema WHERE tabla_afectada IS NOT NULL ORDER BY tabla_afectada");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
