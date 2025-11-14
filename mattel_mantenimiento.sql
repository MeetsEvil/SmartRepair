-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-11-2025 a las 07:53:44
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mattel_mantenimiento`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_ticket`
--

CREATE TABLE `estados_ticket` (
  `id_estado` int(11) NOT NULL,
  `nombre_estado` varchar(50) NOT NULL,
  `orden` int(11) NOT NULL COMMENT 'Orden del flujo (1=Pendiente, 2=En progreso, 3=En confirmación, 4=Finalizado)',
  `color` varchar(20) DEFAULT NULL COMMENT 'Color para UI',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estados_ticket`
--

INSERT INTO `estados_ticket` (`id_estado`, `nombre_estado`, `orden`, `color`, `created_at`) VALUES
(1, 'Pendiente', 1, '#6B7280', '2025-11-08 17:59:05'),
(2, 'En progreso', 2, '#3B82F6', '2025-11-08 17:59:05'),
(3, 'En confirmación', 3, '#F59E0B', '2025-11-08 17:59:05'),
(4, 'Finalizado', 4, '#10B981', '2025-11-08 17:59:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineas`
--

CREATE TABLE `lineas` (
  `id_linea` int(11) NOT NULL,
  `nombre_linea` varchar(100) NOT NULL,
  `id_planta` int(11) NOT NULL,
  `id_prioridad` int(11) NOT NULL,
  `estado` enum('Activa','Inactiva') DEFAULT 'Activa',
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lineas`
--

INSERT INTO `lineas` (`id_linea`, `nombre_linea`, `id_planta`, `id_prioridad`, `estado`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Línea A', 1, 2, 'Activa', '', '2025-11-10 02:53:14', '2025-11-14 06:09:35'),
(2, 'Línea B', 2, 2, 'Activa', '', '2025-11-10 03:59:25', '2025-11-10 03:59:25'),
(3, 'Línea C', 3, 3, 'Activa', '', '2025-11-10 03:59:41', '2025-11-10 03:59:41'),
(4, 'Línea C', 4, 4, 'Inactiva', '', '2025-11-10 03:59:56', '2025-11-11 00:51:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimientos`
--

CREATE TABLE `mantenimientos` (
  `id_mantenimiento` int(11) NOT NULL,
  `id_maquina` int(11) NOT NULL,
  `id_tipo_mantenimiento` int(11) NOT NULL,
  `id_tecnico_responsable` int(11) NOT NULL,
  `fecha_mantenimiento` datetime NOT NULL,
  `actividades_realizadas` text NOT NULL,
  `repuestos_utilizados` text DEFAULT NULL COMMENT 'Repuestos o materiales utilizados',
  `tiempo_empleado` int(11) DEFAULT NULL COMMENT 'Tiempo en minutos',
  `costo_aproximado` decimal(10,2) DEFAULT NULL COMMENT 'Costo estimado del mantenimiento',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL COMMENT 'Usuario que registró el mantenimiento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mantenimientos`
--

INSERT INTO `mantenimientos` (`id_mantenimiento`, `id_maquina`, `id_tipo_mantenimiento`, `id_tecnico_responsable`, `fecha_mantenimiento`, `actividades_realizadas`, `repuestos_utilizados`, `tiempo_empleado`, `costo_aproximado`, `observaciones`, `created_at`, `updated_at`, `created_by`) VALUES
(2, 1, 3, 102, '2025-11-13 23:52:00', 'Se ajustaron los tornillos', NULL, NULL, NULL, NULL, '2025-11-14 05:52:18', '2025-11-14 06:18:45', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maquinas`
--

CREATE TABLE `maquinas` (
  `id_maquina` int(11) NOT NULL,
  `codigo_maquina` varchar(50) NOT NULL COMMENT 'Código único de la máquina (ej: MAQ-INY-005)',
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `id_planta` int(11) NOT NULL,
  `id_linea` int(11) NOT NULL,
  `area` varchar(100) DEFAULT NULL COMMENT 'Área dentro de la planta',
  `imagen` varchar(255) DEFAULT 'imgMaquinas/no-maquina.png' COMMENT 'Ruta de la imagen de la máquina',
  `foto_url` varchar(255) DEFAULT NULL COMMENT 'Ruta de la foto de la máquina',
  `fecha_instalacion` date DEFAULT NULL,
  `estado` enum('Activa','Inactiva','Mantenimiento','Fuera de servicio') DEFAULT 'Activa',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL COMMENT 'Usuario que registró la máquina'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `maquinas`
--

INSERT INTO `maquinas` (`id_maquina`, `codigo_maquina`, `marca`, `modelo`, `numero_serie`, `id_planta`, `id_linea`, `area`, `imagen`, `foto_url`, `fecha_instalacion`, `estado`, `observaciones`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 'MA_INY_01', 'EKF', 'HS2100A', NULL, 1, 1, 'Inyección', 'imgMaquinas/1.png', 'upload/1.png', '2025-11-04', 'Activa', 'Ninguna', '2025-11-11 01:36:46', '2025-11-14 06:31:32', 2),
(2, 'MA_INY_02', 'EKFJH', 'HS2100AA', NULL, 1, 1, 'Inyección', 'imgMaquinas/2.png', NULL, '2025-11-04', 'Activa', 'Ninguna', '2025-11-11 01:36:46', '2025-11-14 06:31:32', 2),
(3, 'MA_INY_03', 'EKFJH24', 'HS2100AA2', NULL, 1, 1, 'Inyección', 'imgMaquinas/3.png', NULL, '2025-11-04', 'Activa', 'Ninguna', '2025-11-11 01:36:46', '2025-11-14 06:31:32', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantas`
--

CREATE TABLE `plantas` (
  `id_planta` int(11) NOT NULL,
  `nombre_planta` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('Activa','Inactiva') DEFAULT 'Activa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `plantas`
--

INSERT INTO `plantas` (`id_planta`, `nombre_planta`, `descripcion`, `estado`, `created_at`, `updated_at`) VALUES
(1, 'Planta A', 'Planta de producción A', 'Activa', '2025-11-08 17:59:04', '2025-11-08 17:59:04'),
(2, 'Planta B', 'Planta de producción B', 'Activa', '2025-11-08 17:59:04', '2025-11-08 17:59:04'),
(3, 'Planta C', 'Planta de producción C', 'Activa', '2025-11-08 17:59:04', '2025-11-08 17:59:04'),
(4, 'Planta D', 'Planta de producción D', 'Activa', '2025-11-08 17:59:04', '2025-11-08 17:59:04'),
(5, 'Planta E', 'Planta de producción E', 'Activa', '2025-11-08 17:59:04', '2025-11-08 17:59:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prioridades`
--

CREATE TABLE `prioridades` (
  `id_prioridad` int(11) NOT NULL,
  `nombre_prioridad` varchar(50) NOT NULL,
  `nivel` int(11) NOT NULL COMMENT 'Nivel numérico para ordenar (1=Crítica, 2=Alta, 3=Media, 4=Baja)',
  `color` varchar(20) DEFAULT NULL COMMENT 'Color hexadecimal para UI',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `prioridades`
--

INSERT INTO `prioridades` (`id_prioridad`, `nombre_prioridad`, `nivel`, `color`, `created_at`) VALUES
(1, 'Crítica', 1, '#DC2626', '2025-11-08 17:59:05'),
(2, 'Alta', 2, '#F59E0B', '2025-11-08 17:59:05'),
(3, 'Media', 3, '#3B82F6', '2025-11-08 17:59:05'),
(4, 'Baja', 4, '#10B981', '2025-11-08 17:59:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`, `created_at`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', '2025-11-08 17:59:05'),
(2, 'Técnico', 'Gestión de mantenimientos y tickets', '2025-11-08 17:59:05'),
(3, 'Operador', 'Consulta de máquinas y reporte de fallas', '2025-11-08 17:59:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id_ticket` int(11) NOT NULL,
  `codigo_ticket` varchar(50) NOT NULL COMMENT 'Código único del ticket generado automáticamente',
  `id_maquina` int(11) NOT NULL,
  `id_tipo_falla` int(11) NOT NULL,
  `id_prioridad` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL DEFAULT 1 COMMENT 'Por defecto: Pendiente',
  `id_tecnico_responsable` int(11) DEFAULT NULL COMMENT 'Técnico asignado (NULL si está pendiente)',
  `id_usuario_reporta` int(11) NOT NULL COMMENT 'Usuario que reportó la falla (operario)',
  `descripcion_falla` text NOT NULL,
  `foto_url` varchar(255) DEFAULT NULL COMMENT 'Foto opcional de la falla',
  `causa_raiz` text DEFAULT NULL COMMENT 'Causa raíz identificada por el técnico',
  `solucion_aplicada` text DEFAULT NULL COMMENT 'Solución aplicada por el técnico',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_asignacion` datetime DEFAULT NULL COMMENT 'Cuando pasa a En progreso',
  `fecha_resolucion` datetime DEFAULT NULL COMMENT 'Cuando pasa a En confirmación',
  `fecha_cierre` datetime DEFAULT NULL COMMENT 'Cuando pasa a Finalizado',
  `tiempo_respuesta` int(11) DEFAULT NULL COMMENT 'Minutos desde creación hasta asignación',
  `tiempo_resolucion` int(11) DEFAULT NULL COMMENT 'Minutos desde asignación hasta resolución',
  `tiempo_total` int(11) DEFAULT NULL COMMENT 'Minutos desde creación hasta cierre',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tickets`
--

INSERT INTO `tickets` (`id_ticket`, `codigo_ticket`, `id_maquina`, `id_tipo_falla`, `id_prioridad`, `id_estado`, `id_tecnico_responsable`, `id_usuario_reporta`, `descripcion_falla`, `foto_url`, `causa_raiz`, `solucion_aplicada`, `fecha_creacion`, `fecha_asignacion`, `fecha_resolucion`, `fecha_cierre`, `tiempo_respuesta`, `tiempo_resolucion`, `tiempo_total`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 'TKT-20251110-0001', 1, 2, 4, 4, 2, 1, 'fnklofisgijsig', NULL, 'abrgbioarsgosnaiognaesf egwijojipegsjgsiasag msjopgejposgjpoaegspoe', 'seginsingsgesgseg egj9\'saj\'ogjoesg esgjosegjojogs', '2025-11-10 20:03:55', '2025-11-13 19:50:17', '2025-11-13 22:29:35', '2025-11-13 22:29:47', 4306, 159, 4465, NULL, '2025-11-11 02:03:55', '2025-11-14 04:29:47'),
(4, 'TKT-20251113-0001', 1, 13, 1, 4, 102, 1, 'Se presenta fallas en el encendido de la máquina', 'uploads/tickets/ticket_20251114055718_6916b6aea8cf3.jpg', 'Se detecto que no habían conectado la máquina', 'Se conectó al circuito', '2025-11-13 22:57:18', '2025-11-13 23:04:18', '2025-11-13 23:04:57', '2025-11-13 23:05:16', 7, 0, 7, NULL, '2025-11-14 04:57:18', '2025-11-14 05:15:41'),
(5, 'TKT-20251113-0002', 2, 5, 2, 1, NULL, 1, 'El tornillo se encuentra barrido', 'uploads/tickets/ticket_20251114060549_6916b8ad82174.jpg', NULL, NULL, '2025-11-13 23:05:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:05:49', '2025-11-14 05:15:41'),
(6, 'TKT-20251113-0003', 1, 7, 4, 3, 2, 1, 'No tenemos el tornillo', 'uploads/tickets/ticket_20251114061011_6916b9b35d710.jpg', 'Se corroboró la falta de una pieza especial para la máquina', 'Se consiguió el tornillo necesario desde el departamento de materiales', '2025-11-13 23:10:11', '2025-11-13 23:23:38', '2025-11-13 23:24:29', NULL, 13, 0, NULL, NULL, '2025-11-14 05:10:11', '2025-11-14 05:24:29');

--
-- Disparadores `tickets`
--
DELIMITER $$
CREATE TRIGGER `before_insert_ticket` BEFORE INSERT ON `tickets` FOR EACH ROW BEGIN
    DECLARE nuevo_codigo VARCHAR(50);
    DECLARE contador INT;
    
    -- Obtener el último número de ticket del día
    SELECT COUNT(*) + 1 INTO contador
    FROM tickets
    WHERE DATE(fecha_creacion) = CURDATE();
    
    -- Generar código: TKT-YYYYMMDD-NNNN
    SET nuevo_codigo = CONCAT('TKT-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(contador, 4, '0'));
    SET NEW.codigo_ticket = nuevo_codigo;
    SET NEW.fecha_creacion = NOW();
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_update_ticket` BEFORE UPDATE ON `tickets` FOR EACH ROW BEGIN
    -- Si pasa a En progreso (estado 2) y se asigna técnico
    IF NEW.id_estado = 2 AND OLD.id_estado = 1 AND NEW.id_tecnico_responsable IS NOT NULL THEN
        SET NEW.fecha_asignacion = NOW();
        SET NEW.tiempo_respuesta = TIMESTAMPDIFF(MINUTE, NEW.fecha_creacion, NOW());
    END IF;
    
    -- Si pasa a En confirmación (estado 3)
    IF NEW.id_estado = 3 AND OLD.id_estado = 2 THEN
        SET NEW.fecha_resolucion = NOW();
        IF NEW.fecha_asignacion IS NOT NULL THEN
            SET NEW.tiempo_resolucion = TIMESTAMPDIFF(MINUTE, NEW.fecha_asignacion, NOW());
        END IF;
    END IF;
    
    -- Si pasa a Finalizado (estado 4)
    IF NEW.id_estado = 4 AND OLD.id_estado = 3 THEN
        SET NEW.fecha_cierre = NOW();
        SET NEW.tiempo_total = TIMESTAMPDIFF(MINUTE, NEW.fecha_creacion, NOW());
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_tecnicos`
--

CREATE TABLE `ticket_tecnicos` (
  `id` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_tecnico` int(11) NOT NULL,
  `fecha_asignacion` datetime DEFAULT current_timestamp(),
  `es_responsable_principal` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_falla`
--

CREATE TABLE `tipos_falla` (
  `id_tipo_falla` int(11) NOT NULL,
  `nombre_tipo_falla` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `maquina_aplicable` varchar(100) DEFAULT NULL COMMENT 'Tipo de máquina a la que aplica (ej: Semi Screw, Full Auto Screw)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_falla`
--

INSERT INTO `tipos_falla` (`id_tipo_falla`, `nombre_tipo_falla`, `descripcion`, `maquina_aplicable`, `created_at`, `updated_at`) VALUES
(1, 'Atoramiento de tornillo en tambor', 'Tornillo atascado en el tambor de alimentación', 'Semi Screw (EKF-HS2100A)', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(2, 'Atoramiento de tornillo en mecanismo interno', 'Tornillo atascado en mecanismo interno', 'Semi Screw (EKF-HS2100A)', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(3, 'Atoramiento de tornillo en boquilla', 'Tornillo atascado en la boquilla de salida', 'Semi Screw (EKF-HS2100A)', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(4, 'Desfogue de aire', 'Pérdida de presión de aire en el sistema', 'Semi Screw (EKF-HS2100A)', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(5, 'Tornillo no ajusta bien - deslizamiento', 'El tornillo no ajusta correctamente y se desliza', 'Full Auto Screw (EKF-S9001-A2)', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(6, 'Destornillador no funciona', 'Falla en el mecanismo del destornillador', 'Full Auto Screw (EKF-S9001-A2)', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(7, 'Retraso en entrega de tornillo', 'Demora en el sistema de alimentación de tornillos', 'Full Auto Screw (EKF-S9001-A2)', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(8, 'Cambio de coordenadas', 'Necesidad de ajustar coordenadas de posicionamiento', 'Full Auto Screw (EKF-S9001-A2)', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(9, 'Máquina se bloquea', 'Bloqueo general de la máquina', 'Full Auto Screw (EKF-S9001-A2)', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(10, 'Falla eléctrica', 'Problema en el sistema eléctrico', 'General', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(11, 'Falla mecánica', 'Problema mecánico general', 'General', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(12, 'Falla neumática', 'Problema en el sistema neumático', 'General', '2025-11-08 17:59:05', '2025-11-08 17:59:05'),
(13, 'Otro', 'Otro tipo de falla no especificada', 'General', '2025-11-08 17:59:05', '2025-11-08 17:59:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_mantenimiento`
--

CREATE TABLE `tipos_mantenimiento` (
  `id_tipo_mantenimiento` int(11) NOT NULL,
  `nombre_tipo` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_mantenimiento`
--

INSERT INTO `tipos_mantenimiento` (`id_tipo_mantenimiento`, `nombre_tipo`, `descripcion`, `created_at`) VALUES
(1, 'Preventivo', 'Mantenimiento programado para prevenir fallas', '2025-11-08 17:59:05'),
(2, 'Correctivo', 'Mantenimiento para corregir fallas existentes', '2025-11-08 17:59:05'),
(3, 'Predictivo', 'Mantenimiento basado en análisis predictivo', '2025-11-08 17:59:05'),
(4, 'Otro', 'Otro tipo de mantenimiento', '2025-11-08 17:59:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Hash de contraseña',
  `telefono` varchar(20) DEFAULT NULL,
  `id_rol` int(11) NOT NULL,
  `id_planta` int(11) DEFAULT NULL COMMENT 'Planta asignada (opcional)',
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `email`, `usuario`, `password`, `telefono`, `id_rol`, `id_planta`, `estado`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'Sistema', 'admin@mattel.com', 'admin', '0192023a7bbd73250516f069df18b500', '8124839394', 1, NULL, 'Activo', '2025-11-08 17:59:05', '2025-11-11 02:22:02'),
(2, 'Orlando', 'García', 'orlandojgarciap@gmail.com', 'OrlandoJair10', '26afb62ab464a7d634f8455218cb80ed', '8129486409', 2, 4, 'Activo', '2025-11-09 14:55:36', '2025-11-14 05:23:10'),
(100, 'Orlando', 'García', 'orlandogarcia@gmail.com', 'OrlandoPuente', 'MeetsEvil10', '8129486409', 1, 4, 'Inactivo', '2025-11-09 14:56:20', '2025-11-11 01:18:26'),
(101, 'Orlando', 'García Puente', 'unionline@uanl.mx', 'prueba', '0192023a7bbd73250516f069df18b500', '8129486409', 1, 4, 'Inactivo', '2025-11-10 01:05:30', '2025-11-10 02:22:27'),
(102, 'Janneth', 'González', 'janneth.gonzalezm@uanl.edu.mx', 'jane', '0192023a7bbd73250516f069df18b500', '8131018203', 2, 4, 'Activo', '2025-11-11 02:15:54', '2025-11-14 05:04:08');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_estado_maquinas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_estado_maquinas` (
`id_maquina` int(11)
,`codigo_maquina` varchar(50)
,`marca` varchar(100)
,`modelo` varchar(100)
,`nombre_planta` varchar(50)
,`nombre_linea` varchar(100)
,`area` varchar(100)
,`estado_maquina` enum('Activa','Inactiva','Mantenimiento','Fuera de servicio')
,`tickets_activos` bigint(21)
,`tiene_ticket_critico` bigint(1)
,`ultimo_mantenimiento` datetime
,`color_semaforo` varchar(8)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_resumen_tickets`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_resumen_tickets` (
`id_ticket` int(11)
,`codigo_ticket` varchar(50)
,`codigo_maquina` varchar(50)
,`marca` varchar(100)
,`modelo` varchar(100)
,`nombre_planta` varchar(50)
,`nombre_linea` varchar(100)
,`nombre_prioridad` varchar(50)
,`nivel_prioridad` int(11)
,`nombre_estado` varchar(50)
,`orden_estado` int(11)
,`nombre_tipo_falla` varchar(100)
,`reportado_por` varchar(201)
,`tecnico_responsable` varchar(201)
,`fecha_creacion` datetime
,`fecha_asignacion` datetime
,`fecha_resolucion` datetime
,`fecha_cierre` datetime
,`tiempo_total` int(11)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_estado_maquinas`
--
DROP TABLE IF EXISTS `vista_estado_maquinas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_estado_maquinas`  AS SELECT `m`.`id_maquina` AS `id_maquina`, `m`.`codigo_maquina` AS `codigo_maquina`, `m`.`marca` AS `marca`, `m`.`modelo` AS `modelo`, `p`.`nombre_planta` AS `nombre_planta`, `l`.`nombre_linea` AS `nombre_linea`, `m`.`area` AS `area`, `m`.`estado` AS `estado_maquina`, count(distinct case when `t`.`id_estado` in (1,2) then `t`.`id_ticket` end) AS `tickets_activos`, max(case when `t`.`id_estado` in (1,2) and `t`.`id_prioridad` = 1 then 1 else 0 end) AS `tiene_ticket_critico`, max(`mt`.`fecha_mantenimiento`) AS `ultimo_mantenimiento`, CASE WHEN count(distinct case when `t`.`id_estado` in (1,2) AND `t`.`id_prioridad` = 1 then `t`.`id_ticket` end) > 0 THEN 'Rojo' WHEN count(distinct case when `t`.`id_estado` in (1,2) then `t`.`id_ticket` end) > 0 THEN 'Amarillo' ELSE 'Verde' END AS `color_semaforo` FROM ((((`maquinas` `m` left join `plantas` `p` on(`m`.`id_planta` = `p`.`id_planta`)) left join `lineas` `l` on(`m`.`id_linea` = `l`.`id_linea`)) left join `tickets` `t` on(`m`.`id_maquina` = `t`.`id_maquina`)) left join `mantenimientos` `mt` on(`m`.`id_maquina` = `mt`.`id_maquina`)) WHERE `m`.`estado` = 'Activa' GROUP BY `m`.`id_maquina` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_resumen_tickets`
--
DROP TABLE IF EXISTS `vista_resumen_tickets`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_resumen_tickets`  AS SELECT `t`.`id_ticket` AS `id_ticket`, `t`.`codigo_ticket` AS `codigo_ticket`, `m`.`codigo_maquina` AS `codigo_maquina`, `m`.`marca` AS `marca`, `m`.`modelo` AS `modelo`, `p`.`nombre_planta` AS `nombre_planta`, `l`.`nombre_linea` AS `nombre_linea`, `pr`.`nombre_prioridad` AS `nombre_prioridad`, `pr`.`nivel` AS `nivel_prioridad`, `e`.`nombre_estado` AS `nombre_estado`, `e`.`orden` AS `orden_estado`, `tf`.`nombre_tipo_falla` AS `nombre_tipo_falla`, concat(`u_reporta`.`nombre`,' ',`u_reporta`.`apellido`) AS `reportado_por`, concat(`u_tecnico`.`nombre`,' ',`u_tecnico`.`apellido`) AS `tecnico_responsable`, `t`.`fecha_creacion` AS `fecha_creacion`, `t`.`fecha_asignacion` AS `fecha_asignacion`, `t`.`fecha_resolucion` AS `fecha_resolucion`, `t`.`fecha_cierre` AS `fecha_cierre`, `t`.`tiempo_total` AS `tiempo_total` FROM ((((((((`tickets` `t` join `maquinas` `m` on(`t`.`id_maquina` = `m`.`id_maquina`)) join `plantas` `p` on(`m`.`id_planta` = `p`.`id_planta`)) join `lineas` `l` on(`m`.`id_linea` = `l`.`id_linea`)) join `prioridades` `pr` on(`t`.`id_prioridad` = `pr`.`id_prioridad`)) join `estados_ticket` `e` on(`t`.`id_estado` = `e`.`id_estado`)) join `tipos_falla` `tf` on(`t`.`id_tipo_falla` = `tf`.`id_tipo_falla`)) join `usuarios` `u_reporta` on(`t`.`id_usuario_reporta` = `u_reporta`.`id_usuario`)) left join `usuarios` `u_tecnico` on(`t`.`id_tecnico_responsable` = `u_tecnico`.`id_usuario`)) ORDER BY `pr`.`nivel` ASC, `t`.`fecha_creacion` DESC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `estados_ticket`
--
ALTER TABLE `estados_ticket`
  ADD PRIMARY KEY (`id_estado`),
  ADD UNIQUE KEY `nombre_estado` (`nombre_estado`),
  ADD UNIQUE KEY `orden` (`orden`);

--
-- Indices de la tabla `lineas`
--
ALTER TABLE `lineas`
  ADD PRIMARY KEY (`id_linea`),
  ADD UNIQUE KEY `unique_linea_planta` (`nombre_linea`,`id_planta`),
  ADD KEY `id_prioridad` (`id_prioridad`),
  ADD KEY `idx_planta` (`id_planta`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD PRIMARY KEY (`id_mantenimiento`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_maquina` (`id_maquina`),
  ADD KEY `idx_fecha` (`fecha_mantenimiento`),
  ADD KEY `idx_tecnico` (`id_tecnico_responsable`),
  ADD KEY `idx_tipo` (`id_tipo_mantenimiento`);

--
-- Indices de la tabla `maquinas`
--
ALTER TABLE `maquinas`
  ADD PRIMARY KEY (`id_maquina`),
  ADD UNIQUE KEY `codigo_maquina` (`codigo_maquina`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_codigo` (`codigo_maquina`),
  ADD KEY `idx_planta` (`id_planta`),
  ADD KEY `idx_linea` (`id_linea`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `plantas`
--
ALTER TABLE `plantas`
  ADD PRIMARY KEY (`id_planta`),
  ADD UNIQUE KEY `nombre_planta` (`nombre_planta`);

--
-- Indices de la tabla `prioridades`
--
ALTER TABLE `prioridades`
  ADD PRIMARY KEY (`id_prioridad`),
  ADD UNIQUE KEY `nombre_prioridad` (`nombre_prioridad`),
  ADD UNIQUE KEY `nivel` (`nivel`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id_ticket`),
  ADD UNIQUE KEY `codigo_ticket` (`codigo_ticket`),
  ADD KEY `id_tipo_falla` (`id_tipo_falla`),
  ADD KEY `id_usuario_reporta` (`id_usuario_reporta`),
  ADD KEY `idx_codigo` (`codigo_ticket`),
  ADD KEY `idx_maquina` (`id_maquina`),
  ADD KEY `idx_estado` (`id_estado`),
  ADD KEY `idx_prioridad` (`id_prioridad`),
  ADD KEY `idx_tecnico` (`id_tecnico_responsable`),
  ADD KEY `idx_fecha_creacion` (`fecha_creacion`);

--
-- Indices de la tabla `ticket_tecnicos`
--
ALTER TABLE `ticket_tecnicos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_ticket_tecnico` (`id_ticket`,`id_tecnico`),
  ADD KEY `idx_ticket` (`id_ticket`),
  ADD KEY `idx_tecnico` (`id_tecnico`);

--
-- Indices de la tabla `tipos_falla`
--
ALTER TABLE `tipos_falla`
  ADD PRIMARY KEY (`id_tipo_falla`);

--
-- Indices de la tabla `tipos_mantenimiento`
--
ALTER TABLE `tipos_mantenimiento`
  ADD PRIMARY KEY (`id_tipo_mantenimiento`),
  ADD UNIQUE KEY `nombre_tipo` (`nombre_tipo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `id_planta` (`id_planta`),
  ADD KEY `idx_usuario` (`usuario`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`id_rol`),
  ADD KEY `idx_estado` (`estado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `estados_ticket`
--
ALTER TABLE `estados_ticket`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `lineas`
--
ALTER TABLE `lineas`
  MODIFY `id_linea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  MODIFY `id_mantenimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `maquinas`
--
ALTER TABLE `maquinas`
  MODIFY `id_maquina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `plantas`
--
ALTER TABLE `plantas`
  MODIFY `id_planta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `prioridades`
--
ALTER TABLE `prioridades`
  MODIFY `id_prioridad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ticket_tecnicos`
--
ALTER TABLE `ticket_tecnicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_falla`
--
ALTER TABLE `tipos_falla`
  MODIFY `id_tipo_falla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tipos_mantenimiento`
--
ALTER TABLE `tipos_mantenimiento`
  MODIFY `id_tipo_mantenimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `lineas`
--
ALTER TABLE `lineas`
  ADD CONSTRAINT `lineas_ibfk_1` FOREIGN KEY (`id_planta`) REFERENCES `plantas` (`id_planta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `lineas_ibfk_2` FOREIGN KEY (`id_prioridad`) REFERENCES `prioridades` (`id_prioridad`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD CONSTRAINT `mantenimientos_ibfk_1` FOREIGN KEY (`id_maquina`) REFERENCES `maquinas` (`id_maquina`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mantenimientos_ibfk_2` FOREIGN KEY (`id_tipo_mantenimiento`) REFERENCES `tipos_mantenimiento` (`id_tipo_mantenimiento`) ON UPDATE CASCADE,
  ADD CONSTRAINT `mantenimientos_ibfk_3` FOREIGN KEY (`id_tecnico_responsable`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `mantenimientos_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `maquinas`
--
ALTER TABLE `maquinas`
  ADD CONSTRAINT `maquinas_ibfk_1` FOREIGN KEY (`id_planta`) REFERENCES `plantas` (`id_planta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `maquinas_ibfk_2` FOREIGN KEY (`id_linea`) REFERENCES `lineas` (`id_linea`) ON UPDATE CASCADE,
  ADD CONSTRAINT `maquinas_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`id_maquina`) REFERENCES `maquinas` (`id_maquina`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`id_tipo_falla`) REFERENCES `tipos_falla` (`id_tipo_falla`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`id_prioridad`) REFERENCES `prioridades` (`id_prioridad`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_4` FOREIGN KEY (`id_estado`) REFERENCES `estados_ticket` (`id_estado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_5` FOREIGN KEY (`id_tecnico_responsable`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_6` FOREIGN KEY (`id_usuario_reporta`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `ticket_tecnicos`
--
ALTER TABLE `ticket_tecnicos`
  ADD CONSTRAINT `ticket_tecnicos_ibfk_1` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_ticket`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ticket_tecnicos_ibfk_2` FOREIGN KEY (`id_tecnico`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_planta`) REFERENCES `plantas` (`id_planta`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
