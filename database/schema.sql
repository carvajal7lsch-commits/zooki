CREATE DATABASE IF NOT EXISTS `zooki_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `zooki_db`;

-- =================================================================
-- TABLAS INDEPENDIENTES (Sin llaves foráneas)
-- =================================================================

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre_rol` (`nombre_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'administrador'), (2, 'veterinario'), (3, 'recepcionista'), (4, 'propietario');


CREATE TABLE `colores_base` (
  `id_color` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_color` varchar(30) NOT NULL,
  PRIMARY KEY (`id_color`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `colores_base` (`id_color`, `nombre_color`) VALUES
(1, 'Blanco'), (2, 'Negro'), (3, 'Café'), (4, 'Gris'),
(5, 'Canela'), (6, 'Crema'), (7, 'Naranja'), (8, 'Chocolate');


CREATE TABLE `especies` (
  `id_especie` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_especie` varchar(50) NOT NULL,
  PRIMARY KEY (`id_especie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `especies` (`id_especie`, `nombre_especie`) VALUES
(1, 'Canino'), (2, 'Felino'), (3, 'Roedor'), (4, 'Ave'), (5, 'Reptil'), (6, 'Exótico');

-- =================================================================
-- TABLAS DE NIVEL 1 (Dependen de las independientes)
-- =================================================================

CREATE TABLE `razas` (
  `id_raza` int(11) NOT NULL AUTO_INCREMENT,
  `id_especie` int(11) NOT NULL,
  `nombre_raza` varchar(50) NOT NULL,
  PRIMARY KEY (`id_raza`),
  CONSTRAINT `razas_ibfk_1` FOREIGN KEY (`id_especie`) REFERENCES `especies` (`id_especie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `razas` (`id_raza`, `id_especie`, `nombre_raza`) VALUES
(1, 1, 'Labrador Retriever'), (2, 1, 'Pastor Alemán'), (3, 1, 'Golden Retriever'), (4, 1, 'Bulldog Inglés'),
(5, 1, 'Bulldog Francés'), (6, 1, 'Poodle (Caniche)'), (7, 1, 'Beagle'), (8, 1, 'Chihuahua'),
(9, 1, 'Boxer'), (10, 1, 'Rottweiler'), (11, 1, 'Husky Siberiano'), (12, 1, 'Pinscher'),
(13, 1, 'Shih Tzu'), (14, 1, 'Pug'), (15, 1, 'Yorkshire Terrier'), (16, 1, 'Dóberman'),
(17, 1, 'Dálmata'), (18, 1, 'Criollo (Mestizo)'), (19, 2, 'Persa'), (20, 2, 'Siamés'),
(21, 2, 'Maine Coon'), (22, 2, 'Angora'), (23, 2, 'Azul Ruso'), (24, 2, 'Bengala'),
(25, 2, 'Ragdoll'), (26, 2, 'Sphynx'), (27, 2, 'Criollo'), (28, 3, 'Conejo Enano'),
(29, 3, 'Hámster Sirio'), (30, 3, 'Hámster Ruso'), (31, 3, 'Cobaya (Cuy)'), (32, 3, 'Hurón'),
(33, 3, 'Chinchilla'), (34, 4, 'Canario'), (35, 4, 'Periquito Australiano'), (36, 4, 'Loro Amazónico'),
(37, 4, 'Cacatúa'), (38, 4, 'Agapornis'), (39, 4, 'Ninfa'), (40, 5, 'Tortuga Morrocoy'),
(41, 5, 'Tortuga Jicotea'), (42, 5, 'Iguana Verde'), (43, 5, 'Dragón Barbudo'), (44, 5, 'Gecko'),
(45, 6, 'Erizo de Tierra'), (46, 6, 'Mini Pig'), (47, 6, 'Serpiente del Maíz'), (48, 1, 'Pitbull');


CREATE TABLE `usuarios` (
  `documento` varchar(20) NOT NULL,
  `tipo_documento` varchar(20) DEFAULT NULL,
  `nombre_completo` varchar(200) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`documento`),
  UNIQUE KEY `email` (`email`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_documento` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_password_resets_email` (`email`),
  KEY `idx_password_resets_documento` (`usuario_documento`),
  CONSTRAINT `fk_password_resets_usuario` FOREIGN KEY (`usuario_documento`) REFERENCES `usuarios` (`documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- =================================================================
-- TABLA PRINCIPAL (Mascotas)
-- =================================================================

CREATE TABLE `mascotas` (
  `id_mascota` int(11) NOT NULL AUTO_INCREMENT,
  `numero_historia_clinica` varchar(255) NOT NULL,
  `doc_propietario` varchar(20) NOT NULL,
  `id_especie` int(11) NOT NULL,
  `id_raza` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `peso` decimal(5,2) NOT NULL,
  `sexo` enum('Macho','Hembra','Desconocido') NOT NULL DEFAULT 'Desconocido',
  `color` varchar(255) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1,
  `url_foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_mascota`),
  CONSTRAINT `fk_mascota_especie` FOREIGN KEY (`id_especie`) REFERENCES `especies` (`id_especie`),
  CONSTRAINT `fk_mascota_raza` FOREIGN KEY (`id_raza`) REFERENCES `razas` (`id_raza`),
  CONSTRAINT `mascotas_ibfk_1` FOREIGN KEY (`doc_propietario`) REFERENCES `usuarios` (`documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =================================================================
-- TABLAS DE NIVEL 3 (Dependen de Mascotas y Usuarios)
-- =================================================================

CREATE TABLE IF NOT EXISTS `tipos_cita` (
  `id_tipo_cita`      INT(11)      NOT NULL AUTO_INCREMENT,
  `nombre`            VARCHAR(100) NOT NULL,
  `duracion_minutos`  INT(11)      NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_tipo_cita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tipos_cita` (`nombre`, `duracion_minutos`, `activo`) VALUES
('Consulta general',  30, 1),
('Vacunación',        20, 1),
('Desparasitación',   15, 1),
('Cirugía menor',     60, 1);


CREATE TABLE `mascota_colores` (
  `id_mascota` int(11) NOT NULL,
  `id_color` int(11) NOT NULL,
  PRIMARY KEY (`id_mascota`,`id_color`),
  CONSTRAINT `fk_mascota_colores_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mascota_colores_color` FOREIGN KEY (`id_color`) REFERENCES `colores_base` (`id_color`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `auditoria_mascotas` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `id_mascota` int(11) NOT NULL,
  `usuario_doc` varchar(20) NOT NULL,
  `campo_modificado` varchar(100) DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `fecha_cambio` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_auditoria`),
  CONSTRAINT `auditoria_mascotas_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`),
  CONSTRAINT `auditoria_mascotas_ibfk_2` FOREIGN KEY (`usuario_doc`) REFERENCES `usuarios` (`documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL AUTO_INCREMENT,
  `id_mascota` int(11) NOT NULL,
  `doc_veterinario` varchar(20) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `hora_fin` time DEFAULT NULL,
  `duracion_minutos` int(11) DEFAULT 30,
  `id_tipo_cita` int(11) DEFAULT NULL,
  `motivo` varchar(255) NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada','completada') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_cita`),
  UNIQUE KEY `uq_vet_fecha_hora` (`doc_veterinario`, `fecha`, `hora`),
  CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`),
  CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`doc_veterinario`) REFERENCES `usuarios` (`documento`),
  CONSTRAINT `fk_cita_tipo` FOREIGN KEY (`id_tipo_cita`) REFERENCES `tipos_cita` (`id_tipo_cita`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `consultas` (
  `id_consulta` int(11) NOT NULL AUTO_INCREMENT,
  `id_cita` int(11) DEFAULT NULL,
  `id_mascota` int(11) NOT NULL,
  `doc_veterinario` varchar(20) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `motivo_consulta` text NOT NULL,
  `anamnesis` text NOT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `temperatura` decimal(4,1) DEFAULT NULL,
  `frecuencia_cardiaca` int(11) DEFAULT NULL,
  `diagnostico` text NOT NULL,
  `plan_tratamiento` text NOT NULL,
  PRIMARY KEY (`id_consulta`),
  UNIQUE KEY `uq_consulta_cita` (`id_cita`),
  CONSTRAINT `consultas_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`),
  CONSTRAINT `consultas_ibfk_2` FOREIGN KEY (`doc_veterinario`) REFERENCES `usuarios` (`documento`),
  CONSTRAINT `consultas_ibfk_3` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `desparasitaciones` (
  `id_desparasitacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_mascota` int(11) NOT NULL,
  `tipo` enum('interna','externa') NOT NULL,
  `producto` varchar(150) NOT NULL,
  `periodicidad` enum('mensual','trimestral','semestral') NOT NULL,
  `fecha_aplicacion` date NOT NULL,
  `fecha_proxima` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_desparasitacion`),
  CONSTRAINT `desparasitaciones_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `doc_propietario` varchar(20) NOT NULL,
  `tipo_entidad` varchar(50) NOT NULL,
  `id_entidad` int(11) NOT NULL,
  `destinatario_email` varchar(255) NOT NULL,
  `tipo_notificacion` varchar(50) NOT NULL,
  `asunto` varchar(255) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `estado` enum('pendiente','enviado','error') DEFAULT 'pendiente',
  PRIMARY KEY (`id_notificacion`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`doc_propietario`) REFERENCES `usuarios` (`documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notificaciones_internas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_usuario` varchar(20) DEFAULT NULL,
  `id_rol_destino` int(11) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_noti_usr` FOREIGN KEY (`doc_usuario`) REFERENCES `usuarios`(`documento`),
  CONSTRAINT `fk_noti_rol` FOREIGN KEY (`id_rol_destino`) REFERENCES `roles`(`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `vacunas` (
  `id_vacuna` int(11) NOT NULL AUTO_INCREMENT,
  `id_mascota` int(11) NOT NULL,
  `nombre_vacuna` varchar(150) NOT NULL,
  `laboratorio` varchar(150) DEFAULT NULL,
  `lote` varchar(100) DEFAULT NULL,
  `fecha_aplicacion` date NOT NULL,
  `fecha_proxima_dosis` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_vacuna`),
  CONSTRAINT `vacunas_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =================================================================
-- TABLAS DE NIVEL 4 (Dependen de Consultas)
-- =================================================================

CREATE TABLE `archivos_clinicos` (
  `id_archivo` int(11) NOT NULL AUTO_INCREMENT,
  `id_consulta` int(11) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_servidor` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(255) NOT NULL,
  `extension` varchar(20) NOT NULL,
  `tamano_bytes` int(11) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_archivo`),
  CONSTRAINT `archivos_clinicos_ibfk_1` FOREIGN KEY (`id_consulta`) REFERENCES `consultas` (`id_consulta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `tratamientos` (
  `id_tratamiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_consulta` int(11) NOT NULL,
  `medicamento` varchar(255) NOT NULL,
  `dosis` varchar(255) NOT NULL,
  `via_administracion` varchar(255) NOT NULL,
  `duracion` varchar(100) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tratamiento`),
  CONSTRAINT `tratamientos_ibfk_1` FOREIGN KEY (`id_consulta`) REFERENCES `consultas` (`id_consulta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `auditoria_sistema` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_doc` varchar(20) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `accion` enum('LOGIN','LOGIN_FAIL','LOGOUT','INSERT','UPDATE','DELETE','VIEW','OTHER') NOT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `registro_id` varchar(50) DEFAULT NULL,
  `datos_anteriores` json DEFAULT NULL,
  `datos_nuevos` json DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_auditoria_usuario` (`usuario_doc`),
  KEY `idx_auditoria_fecha` (`fecha_hora`),
  KEY `idx_auditoria_accion` (`accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
