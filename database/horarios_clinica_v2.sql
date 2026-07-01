-- Eliminar tabla anterior si existe
DROP TABLE IF EXISTS horarios_clinica;

-- Nueva tabla para configurar los horarios de atención de la clínica con múltiples bloques por día
CREATE TABLE IF NOT EXISTS horarios_clinica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana INT NOT NULL COMMENT '1=Lunes, 2=Martes, ..., 7=Domingo',
    activo TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    bloque_morning_inicio TIME DEFAULT NULL COMMENT 'Inicio horario mañana',
    bloque_morning_fin TIME DEFAULT NULL COMMENT 'Fin horario mañana',
    bloque_afternoon_inicio TIME DEFAULT NULL COMMENT 'Inicio horario tarde',
    bloque_afternoon_fin TIME DEFAULT NULL COMMENT 'Fin horario tarde',

    UNIQUE KEY uk_dia_semana (dia_semana),
    CONSTRAINT chk_morning CHECK (bloque_morning_fin IS NULL OR bloque_morning_fin > bloque_morning_inicio),
    CONSTRAINT chk_afternoon CHECK (bloque_afternoon_fin IS NULL OR bloque_afternoon_fin > bloque_afternoon_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar horarios por defecto (lunes a viernes, 8am-12pm y 2pm-6pm)
INSERT INTO horarios_clinica (dia_semana, activo, bloque_morning_inicio, bloque_morning_fin, bloque_afternoon_inicio, bloque_afternoon_fin) VALUES
(1, 1, '08:00:00', '12:00:00', '14:00:00', '18:00:00'), -- Lunes
(2, 1, '08:00:00', '12:00:00', '14:00:00', '18:00:00'), -- Martes
(3, 1, '08:00:00', '12:00:00', '14:00:00', '18:00:00'), -- Miércoles
(4, 1, '08:00:00', '12:00:00', '14:00:00', '18:00:00'), -- Jueves
(5, 1, '08:00:00', '12:00:00', '14:00:00', '18:00:00'), -- Viernes
(6, 0, '08:00:00', '13:00:00', NULL, NULL), -- Sábado (solo mañana, inactivo por defecto)
(7, 0, NULL, NULL, NULL, NULL); -- Domingo (inactivo por defecto)
