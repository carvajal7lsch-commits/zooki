<?php
class Cita {
    private $conn;
    private $table_name = "citas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function insert($data) {
        // Calcular hora_fin basado en la duración del tipo de cita
        $hora_fin = null;
        if (isset($data['duracion_minutos']) && $data['duracion_minutos']) {
            $hora_fin = date('H:i:s', strtotime($data['hora'] . ' +' . $data['duracion_minutos'] . ' minutes'));
        }
        
        $estado = isset($data['estado']) ? $data['estado'] : 'pendiente';

        $query = "INSERT INTO " . $this->table_name . " 
                  (id_mascota, doc_veterinario, fecha, hora, hora_fin, motivo, id_tipo_cita, duracion_minutos, estado) 
                  VALUES (:id_mascota, :doc_veterinario, :fecha, :hora, :hora_fin, :motivo, :id_tipo_cita, :duracion_minutos, :estado)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_mascota', $data['id_mascota']);
        $stmt->bindParam(':doc_veterinario', $data['doc_veterinario']);
        $stmt->bindParam(':fecha', $data['fecha']);
        $stmt->bindParam(':hora', $data['hora']);
        $stmt->bindParam(':hora_fin', $hora_fin);
        $stmt->bindParam(':motivo', $data['motivo']);
        $stmt->bindParam(':id_tipo_cita', $data['id_tipo_cita']);
        $stmt->bindParam(':duracion_minutos', $data['duracion_minutos']);
        $stmt->bindParam(':estado', $estado);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function checkDisponibilidad($doc_veterinario, $fecha, $hora, $duracion_minutos = 0, $id_cita_excluir = null) {
        // Usar timestamps numéricos para comparar horas de forma fiable
        // (evita bugs por diferencias de formato: '08:00' vs '08:00:00')
        $nueva_inicio = strtotime(date('Y-m-d') . ' ' . $hora);
        $nueva_fin    = $nueva_inicio + (max((int)$duracion_minutos, 1) * 60);

        $query = "SELECT hora, hora_fin, duracion_minutos FROM " . $this->table_name . "
                  WHERE doc_veterinario = :doc_vet
                  AND fecha = :fecha
                  AND estado != 'cancelada'";

        if ($id_cita_excluir) {
            $query .= " AND id_cita != :id_cita_excluir";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doc_vet', $doc_veterinario);
        $stmt->bindParam(':fecha', $fecha);
        if ($id_cita_excluir) {
            $stmt->bindParam(':id_cita_excluir', $id_cita_excluir);
        }
        $stmt->execute();
        $citas_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($citas_existentes as $cita) {
            $cita_inicio_ts = strtotime(date('Y-m-d') . ' ' . $cita['hora']);

            if ($cita['hora_fin']) {
                $cita_fin_ts = strtotime(date('Y-m-d') . ' ' . $cita['hora_fin']);
            } else {
                $dur = !empty($cita['duracion_minutos']) ? (int)$cita['duracion_minutos'] : 30;
                $cita_fin_ts = $cita_inicio_ts + ($dur * 60);
            }

            // Solapamiento: nueva_inicio < cita_fin  AND  nueva_fin > cita_inicio
            if ($nueva_inicio < $cita_fin_ts && $nueva_fin > $cita_inicio_ts) {
                return false;
            }
        }

        return true;
    }

    public function checkMascotaDisponible($id_mascota, $fecha, $hora, $duracion_minutos = 0, $id_cita_excluir = null) {
        $nueva_inicio = strtotime(date('Y-m-d') . ' ' . $hora);
        $nueva_fin    = $nueva_inicio + (max((int)$duracion_minutos, 1) * 60);

        $query = "SELECT hora, hora_fin, duracion_minutos FROM " . $this->table_name . "
                  WHERE id_mascota = :id_mascota
                  AND fecha = :fecha
                  AND estado != 'cancelada'";

        if ($id_cita_excluir) {
            $query .= " AND id_cita != :id_cita_excluir";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_mascota', $id_mascota);
        $stmt->bindParam(':fecha', $fecha);
        if ($id_cita_excluir) {
            $stmt->bindParam(':id_cita_excluir', $id_cita_excluir);
        }
        $stmt->execute();
        $citas_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($citas_existentes as $cita) {
            $cita_inicio_ts = strtotime(date('Y-m-d') . ' ' . $cita['hora']);

            if ($cita['hora_fin']) {
                $cita_fin_ts = strtotime(date('Y-m-d') . ' ' . $cita['hora_fin']);
            } else {
                $dur = !empty($cita['duracion_minutos']) ? (int)$cita['duracion_minutos'] : 30;
                $cita_fin_ts = $cita_inicio_ts + ($dur * 60);
            }

            // Solapamiento: nueva_inicio < cita_fin  AND  nueva_fin > cita_inicio
            if ($nueva_inicio < $cita_fin_ts && $nueva_fin > $cita_inicio_ts) {
                return false; // Conflicto de horario para la misma mascota
            }
        }
        return true;
    }
    
    public function getTiposCita() {
        $query = "SELECT * FROM tipos_cita WHERE activo = 1 ORDER BY duracion_minutos ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Mapear nombre_tipo a nombre para compatibilidad
        return array_map(function($tipo) {
            return [
                'id_tipo_cita' => $tipo['id_tipo_cita'],
                'nombre' => $tipo['nombre_tipo'] ?? $tipo['nombre'] ?? '',
                'nombre_tipo' => $tipo['nombre_tipo'] ?? $tipo['nombre'] ?? '',
                'duracion_minutos' => $tipo['duracion_minutos'],
                'activo' => $tipo['activo']
            ];
        }, $tipos);
    }
    
    public function getTipoCitaById($id_tipo_cita) {
        $query = "SELECT * FROM tipos_cita WHERE id_tipo_cita = :id_tipo_cita";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_tipo_cita', $id_tipo_cita);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getSugerenciasHorario($doc_veterinario, $fecha, $duracion_minutos, $id_cita_excluir = null) {
        $duracion_minutos = max((int)$duracion_minutos, 1);

        $tz = new DateTimeZone('America/Bogota');
        $now = new DateTime('now', $tz);
        $hoy = $now->format('Y-m-d');
        $hora_actual_ts = null;
        if ($fecha === $hoy) {
            $hora_actual_ts = $now->getTimestamp();
        }

        // Obtener citas existentes
        $query = "SELECT hora, hora_fin, duracion_minutos as dur FROM " . $this->table_name . "
                  WHERE doc_veterinario = :doc_vet
                  AND fecha = :fecha
                  AND estado != 'cancelada'";
        if ($id_cita_excluir) {
            $query .= " AND id_cita != :id_cita_excluir";
        }
        $query .= " ORDER BY hora ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doc_vet', $doc_veterinario);
        $stmt->bindParam(':fecha', $fecha);
        if ($id_cita_excluir) {
            $stmt->bindParam(':id_cita_excluir', $id_cita_excluir);
        }
        $stmt->execute();
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Construir lista de intervalos bloqueados como timestamps
        $bloqueados = [];
        foreach ($citas as $c) {
            $inicioDt = new DateTime($fecha . ' ' . $c['hora'], $tz);
            $ini = $inicioDt->getTimestamp();

            if (!empty($c['hora_fin'])) {
                $finDt = new DateTime($fecha . ' ' . $c['hora_fin'], $tz);
            } else {
                $dur = !empty($c['dur']) ? (int)$c['dur'] : 30;
                $finDt = (clone $inicioDt)->modify('+' . $dur . ' minutes');
            }

            $bloqueados[] = [$ini, $finDt->getTimestamp()];
        }

        // Recorrer el horario de atención slot por slot
        $apertura_ts = (new DateTime($fecha . ' 08:00', $tz))->getTimestamp();
        $cierre_ts   = (new DateTime($fecha . ' 18:00', $tz))->getTimestamp();
        $paso        = $duracion_minutos * 60;

        $sugerencias = [];
        $slot_ts = $apertura_ts;

        while ($slot_ts + $paso <= $cierre_ts) {
            if ($hora_actual_ts !== null && $slot_ts < $hora_actual_ts) {
                $slot_ts += $paso;
                continue;
            }

            $slot_fin = $slot_ts + $paso;
            $disponible = true;

            foreach ($bloqueados as $blq) {
                // Solapamiento: nueva_inicio < cita_fin  AND  nueva_fin > cita_inicio
                if ($slot_ts < $blq[1] && $slot_fin > $blq[0]) {
                    $disponible = false;
                    break;
                }
            }

            if ($disponible) {
                $slotDate = new DateTime('@' . $slot_ts);
                $slotDate->setTimezone($tz);
                $sugerencias[] = $slotDate->format('H:i');
            }

            $slot_ts += $paso;
        }

        return $sugerencias;
    }

    public function getByFecha($fecha_inicio, $fecha_fin, $doc_veterinario = null) {
        $query = "SELECT c.*, m.nombre as mascota_nombre, u.nombre_completo as veterinario_nombre, p.nombre_completo as propietario_nombre
                  FROM " . $this->table_name . " c
                  JOIN mascotas m ON c.id_mascota = m.id_mascota
                  JOIN usuarios u ON c.doc_veterinario = u.documento
                  JOIN usuarios p ON m.doc_propietario = p.documento
                  WHERE c.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        
        if ($doc_veterinario) {
            $query .= " AND c.doc_veterinario = :doc_veterinario";
        }
        
        $query .= " ORDER BY c.fecha ASC, c.hora ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        
        if ($doc_veterinario) {
            $stmt->bindParam(':doc_veterinario', $doc_veterinario);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id_cita) {
        $query = "SELECT c.*, m.nombre as mascota_nombre, u.nombre_completo as veterinario_nombre, p.nombre_completo as propietario_nombre, p.email
                  FROM " . $this->table_name . " c
                  JOIN mascotas m ON c.id_mascota = m.id_mascota
                  JOIN usuarios u ON c.doc_veterinario = u.documento
                  JOIN usuarios p ON m.doc_propietario = p.documento
                  WHERE c.id_cita = :id_cita";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cita', $id_cita);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id_cita, $doc_veterinario, $fecha, $hora, $motivo, $id_tipo_cita = null, $duracion_minutos = null) {
        // Calcular hora_fin si se proporciona duración
        $hora_fin = null;
        if ($duracion_minutos) {
            $hora_fin = date('H:i:s', strtotime($hora . ' +' . $duracion_minutos . ' minutes'));
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET doc_veterinario = :doc_vet, fecha = :fecha, hora = :hora, motivo = :motivo";
        
        if ($id_tipo_cita) {
            $query .= ", id_tipo_cita = :id_tipo_cita";
        }
        if ($duracion_minutos) {
            $query .= ", duracion_minutos = :duracion_minutos, hora_fin = :hora_fin";
        }
        
        $query .= " WHERE id_cita = :id_cita";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cita', $id_cita);
        $stmt->bindParam(':doc_vet', $doc_veterinario);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':hora', $hora);
        $stmt->bindParam(':motivo', $motivo);
        if ($id_tipo_cita) {
            $stmt->bindParam(':id_tipo_cita', $id_tipo_cita);
        }
        if ($duracion_minutos) {
            $stmt->bindParam(':duracion_minutos', $duracion_minutos);
            $stmt->bindParam(':hora_fin', $hora_fin);
        }
        return $stmt->execute();
    }

    public function getByMascota($id_mascota) {
        $query = "SELECT c.*, u.nombre_completo as veterinario_nombre
                  FROM " . $this->table_name . " c
                  JOIN usuarios u ON c.doc_veterinario = u.documento
                  WHERE c.id_mascota = :id_mascota
                    AND c.estado != 'cancelada'
                  ORDER BY c.fecha DESC, c.hora DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_mascota', $id_mascota);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProximaByMascota($id_mascota) {
        $query = "SELECT c.*, u.nombre_completo as veterinario_nombre
                  FROM " . $this->table_name . " c
                  JOIN usuarios u ON c.doc_veterinario = u.documento
                  WHERE c.id_mascota = :id_mascota
                    AND c.fecha >= CURDATE()
                    AND c.estado IN ('pendiente', 'confirmada')
                  ORDER BY c.fecha ASC, c.hora ASC
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_mascota', $id_mascota);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function cambiarEstado($id_cita, $estado) {
        $query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id_cita = :id_cita";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cita', $id_cita);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute();
    }
}
