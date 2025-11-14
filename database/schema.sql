-- ============================================
-- SISTEMA DE MANTENIMIENTO PREVENTIVO MATTEL
-- Base de datos MySQL
-- ============================================

-- Eliminar base de datos si existe (CUIDADO EN PRODUCCIÓN)
-- DROP DATABASE IF EXISTS mattel_mantenimiento;

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS mattel_mantenimiento 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE mattel_mantenimiento;

-- ============================================
-- TABLAS DE CATÁLOGO
-- ============================================

-- Tabla: plantas
CREATE TABLE plantas (
    id_planta INT AUTO_INCREMENT PRIMARY KEY,
    nombre_planta VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    estado ENUM('Activa', 'Inactiva') DEFAULT 'Activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: roles
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tipos_mantenimiento
CREATE TABLE tipos_mantenimiento (
    id_tipo_mantenimiento INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tipo VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: prioridades
CREATE TABLE prioridades (
    id_prioridad INT AUTO_INCREMENT PRIMARY KEY,
    nombre_prioridad VARCHAR(50) NOT NULL UNIQUE,
    nivel INT NOT NULL UNIQUE COMMENT 'Nivel numérico para ordenar (1=Crítica, 2=Alta, 3=Media, 4=Baja)',
    color VARCHAR(20) COMMENT 'Color hexadecimal para UI',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: estados_ticket
CREATE TABLE estados_ticket (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre_estado VARCHAR(50) NOT NULL UNIQUE,
    orden INT NOT NULL UNIQUE COMMENT 'Orden del flujo (1=Pendiente, 2=En progreso, 3=En confirmación, 4=Finalizado)',
    color VARCHAR(20) COMMENT 'Color para UI',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tipos_falla
CREATE TABLE tipos_falla (
    id_tipo_falla INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tipo_falla VARCHAR(100) NOT NULL,
    descripcion TEXT,
    maquina_aplicable VARCHAR(100) COMMENT 'Tipo de máquina a la que aplica (ej: Semi Screw, Full Auto Screw)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLAS PRINCIPALES
-- ============================================

-- Tabla: usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Hash de contraseña',
    telefono VARCHAR(20),
    id_rol INT NOT NULL,
    id_planta INT COMMENT 'Planta asignada (opcional)',
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON UPDATE CASCADE,
    FOREIGN KEY (id_planta) REFERENCES plantas(id_planta) ON UPDATE CASCADE ON DELETE SET NULL,
    
    INDEX idx_usuario (usuario),
    INDEX idx_email (email),
    INDEX idx_rol (id_rol),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: lineas
CREATE TABLE lineas (
    id_linea INT AUTO_INCREMENT PRIMARY KEY,
    nombre_linea VARCHAR(100) NOT NULL,
    id_planta INT NOT NULL,
    id_prioridad INT NOT NULL,
    estado ENUM('Activa', 'Inactiva') DEFAULT 'Activa',
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_planta) REFERENCES plantas(id_planta) ON UPDATE CASCADE,
    FOREIGN KEY (id_prioridad) REFERENCES prioridades(id_prioridad) ON UPDATE CASCADE,
    
    INDEX idx_planta (id_planta),
    INDEX idx_estado (estado),
    UNIQUE KEY unique_linea_planta (nombre_linea, id_planta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: maquinas
CREATE TABLE maquinas (
    id_maquina INT AUTO_INCREMENT PRIMARY KEY,
    codigo_maquina VARCHAR(50) NOT NULL UNIQUE COMMENT 'Código único de la máquina (ej: MAQ-INY-005)',
    marca VARCHAR(100),
    modelo VARCHAR(100),
    numero_serie VARCHAR(100),
    id_planta INT NOT NULL,
    id_linea INT NOT NULL,
    area VARCHAR(100) COMMENT 'Área dentro de la planta',
    imagen VARCHAR(255) DEFAULT 'imgMaquinas/no-maquina.png' COMMENT 'Ruta de la imagen de la máquina',
    fecha_instalacion DATE,
    estado ENUM('Activa', 'Inactiva', 'Mantenimiento', 'Fuera de servicio') DEFAULT 'Activa',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT COMMENT 'Usuario que registró la máquina',
    
    FOREIGN KEY (id_planta) REFERENCES plantas(id_planta) ON UPDATE CASCADE,
    FOREIGN KEY (id_linea) REFERENCES lineas(id_linea) ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario) ON UPDATE CASCADE ON DELETE SET NULL,
    
    INDEX idx_codigo (codigo_maquina),
    INDEX idx_planta (id_planta),
    INDEX idx_linea (id_linea),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: mantenimientos
CREATE TABLE mantenimientos (
    id_mantenimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_maquina INT NOT NULL,
    id_tipo_mantenimiento INT NOT NULL,
    id_tecnico_responsable INT NOT NULL,
    fecha_mantenimiento DATETIME NOT NULL,
    actividades_realizadas TEXT NOT NULL,
    repuestos_utilizados TEXT COMMENT 'Repuestos o materiales utilizados',
    tiempo_empleado INT COMMENT 'Tiempo en minutos',
    costo_aproximado DECIMAL(10,2) COMMENT 'Costo estimado del mantenimiento',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT COMMENT 'Usuario que registró el mantenimiento',
    
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (id_tipo_mantenimiento) REFERENCES tipos_mantenimiento(id_tipo_mantenimiento) ON UPDATE CASCADE,
    FOREIGN KEY (id_tecnico_responsable) REFERENCES usuarios(id_usuario) ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario) ON UPDATE CASCADE ON DELETE SET NULL,
    
    INDEX idx_maquina (id_maquina),
    INDEX idx_fecha (fecha_mantenimiento),
    INDEX idx_tecnico (id_tecnico_responsable),
    INDEX idx_tipo (id_tipo_mantenimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tickets
CREATE TABLE tickets (
    id_ticket INT AUTO_INCREMENT PRIMARY KEY,
    codigo_ticket VARCHAR(50) NOT NULL UNIQUE COMMENT 'Código único del ticket generado automáticamente',
    id_maquina INT NOT NULL,
    id_tipo_falla INT NOT NULL,
    id_prioridad INT NOT NULL,
    id_estado INT NOT NULL DEFAULT 1 COMMENT 'Por defecto: Pendiente',
    id_tecnico_responsable INT COMMENT 'Técnico asignado (NULL si está pendiente)',
    id_usuario_reporta INT NOT NULL COMMENT 'Usuario que reportó la falla (operario)',
    descripcion_falla TEXT NOT NULL,
    foto_url VARCHAR(255) COMMENT 'Foto opcional de la falla',
    
    -- Campos de resolución
    causa_raiz TEXT COMMENT 'Causa raíz identificada por el técnico',
    solucion_aplicada TEXT COMMENT 'Solución aplicada por el técnico',
    
    -- Timestamps del flujo
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_asignacion DATETIME COMMENT 'Cuando pasa a En progreso',
    fecha_resolucion DATETIME COMMENT 'Cuando pasa a En confirmación',
    fecha_cierre DATETIME COMMENT 'Cuando pasa a Finalizado',
    
    -- Tiempos calculados (en minutos)
    tiempo_respuesta INT COMMENT 'Minutos desde creación hasta asignación',
    tiempo_resolucion INT COMMENT 'Minutos desde asignación hasta resolución',
    tiempo_total INT COMMENT 'Minutos desde creación hasta cierre',
    
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina) ON UPDATE CASCADE,
    FOREIGN KEY (id_tipo_falla) REFERENCES tipos_falla(id_tipo_falla) ON UPDATE CASCADE,
    FOREIGN KEY (id_prioridad) REFERENCES prioridades(id_prioridad) ON UPDATE CASCADE,
    FOREIGN KEY (id_estado) REFERENCES estados_ticket(id_estado) ON UPDATE CASCADE,
    FOREIGN KEY (id_tecnico_responsable) REFERENCES usuarios(id_usuario) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (id_usuario_reporta) REFERENCES usuarios(id_usuario) ON UPDATE CASCADE,
    
    INDEX idx_codigo (codigo_ticket),
    INDEX idx_maquina (id_maquina),
    INDEX idx_estado (id_estado),
    INDEX idx_prioridad (id_prioridad),
    INDEX idx_tecnico (id_tecnico_responsable),
    INDEX idx_fecha_creacion (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla auxiliar: ticket_tecnicos (para futuro soporte de múltiples técnicos)
CREATE TABLE ticket_tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    id_tecnico INT NOT NULL,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    es_responsable_principal BOOLEAN DEFAULT FALSE,
    
    FOREIGN KEY (id_ticket) REFERENCES tickets(id_ticket) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (id_tecnico) REFERENCES usuarios(id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    
    UNIQUE KEY unique_ticket_tecnico (id_ticket, id_tecnico),
    INDEX idx_ticket (id_ticket),
    INDEX idx_tecnico (id_tecnico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES (SEED DATA)
-- ============================================

-- Insertar plantas
INSERT INTO plantas (nombre_planta, descripcion) VALUES
('Planta A', 'Planta de producción A'),
('Planta B', 'Planta de producción B'),
('Planta C', 'Planta de producción C'),
('Planta D', 'Planta de producción D'),
('Planta E', 'Planta de producción E');

-- Insertar roles
INSERT INTO roles (nombre_rol, descripcion) VALUES
('Administrador', 'Acceso completo al sistema'),
('Técnico', 'Gestión de mantenimientos y tickets'),
('Operador', 'Consulta de máquinas y reporte de fallas');

-- Insertar tipos de mantenimiento
INSERT INTO tipos_mantenimiento (nombre_tipo, descripcion) VALUES
('Preventivo', 'Mantenimiento programado para prevenir fallas'),
('Correctivo', 'Mantenimiento para corregir fallas existentes'),
('Predictivo', 'Mantenimiento basado en análisis predictivo'),
('Otro', 'Otro tipo de mantenimiento');

-- Insertar prioridades
INSERT INTO prioridades (nombre_prioridad, nivel, color) VALUES
('Crítica', 1, '#DC2626'),
('Alta', 2, '#F59E0B'),
('Media', 3, '#3B82F6'),
('Baja', 4, '#10B981');

-- Insertar estados de ticket
INSERT INTO estados_ticket (nombre_estado, orden, color) VALUES
('Pendiente', 1, '#6B7280'),
('En progreso', 2, '#3B82F6'),
('En confirmación', 3, '#F59E0B'),
('Finalizado', 4, '#10B981');

-- Insertar tipos de falla comunes
INSERT INTO tipos_falla (nombre_tipo_falla, maquina_aplicable, descripcion) VALUES
-- Semi Screw (EKF-HS2100A)
('Atoramiento de tornillo en tambor', 'Semi Screw (EKF-HS2100A)', 'Tornillo atascado en el tambor de alimentación'),
('Atoramiento de tornillo en mecanismo interno', 'Semi Screw (EKF-HS2100A)', 'Tornillo atascado en mecanismo interno'),
('Atoramiento de tornillo en boquilla', 'Semi Screw (EKF-HS2100A)', 'Tornillo atascado en la boquilla de salida'),
('Desfogue de aire', 'Semi Screw (EKF-HS2100A)', 'Pérdida de presión de aire en el sistema'),
-- Full Auto Screw (EKF-S9001-A2)
('Tornillo no ajusta bien - deslizamiento', 'Full Auto Screw (EKF-S9001-A2)', 'El tornillo no ajusta correctamente y se desliza'),
('Destornillador no funciona', 'Full Auto Screw (EKF-S9001-A2)', 'Falla en el mecanismo del destornillador'),
('Retraso en entrega de tornillo', 'Full Auto Screw (EKF-S9001-A2)', 'Demora en el sistema de alimentación de tornillos'),
('Cambio de coordenadas', 'Full Auto Screw (EKF-S9001-A2)', 'Necesidad de ajustar coordenadas de posicionamiento'),
('Máquina se bloquea', 'Full Auto Screw (EKF-S9001-A2)', 'Bloqueo general de la máquina'),
-- Genéricas
('Falla eléctrica', 'General', 'Problema en el sistema eléctrico'),
('Falla mecánica', 'General', 'Problema mecánico general'),
('Falla neumática', 'General', 'Problema en el sistema neumático'),
('Otro', 'General', 'Otro tipo de falla no especificada');

-- Insertar usuario administrador por defecto
-- Contraseña: admin123 (hash MD5 para ejemplo, en producción usar password_hash de PHP)
INSERT INTO usuarios (nombre, apellido, email, usuario, password, id_rol, estado) VALUES
('Administrador', 'Sistema', 'admin@mattel.com', 'admin', MD5('admin123'), 1, 'Activo');
-- Contraseña: 123 (hash MD5 para ejemplo, en producción usar password_hash de PHP)
INSERT INTO usuarios (nombre, apellido, email, usuario, password, id_rol, estado) VALUES
('Administrador', 'Sistema', 'admin@mattel.com', 'admin', MD5('123'), 1, 'Activo');
INSERT INTO usuarios (nombre, apellido, email, usuario, password, id_rol, estado) VALUES
('Técnico', 'Sistema', 'tecnico@mattel.com', 'tecnico', MD5('123'), 1, 'Activo');
INSERT INTO usuarios (nombre, apellido, email, usuario, password, id_rol, estado) VALUES
('Operario', 'Sistema', 'operario@mattel.com', 'operario', MD5('123'), 1, 'Activo');

-- ============================================
-- VISTAS ÚTILES
-- ============================================

-- Vista: Estado de máquinas con semáforo
CREATE VIEW vista_estado_maquinas AS
SELECT 
    m.id_maquina,
    m.codigo_maquina,
    m.marca,
    m.modelo,
    p.nombre_planta,
    l.nombre_linea,
    m.area,
    m.estado as estado_maquina,
    -- Contar tickets activos (Pendiente o En progreso)
    COUNT(DISTINCT CASE WHEN t.id_estado IN (1, 2) THEN t.id_ticket END) as tickets_activos,
    -- Verificar si tiene tickets críticos
    MAX(CASE WHEN t.id_estado IN (1, 2) AND t.id_prioridad = 1 THEN 1 ELSE 0 END) as tiene_ticket_critico,
    -- Último mantenimiento
    MAX(mt.fecha_mantenimiento) as ultimo_mantenimiento,
    -- Calcular color del semáforo
    CASE 
        WHEN COUNT(DISTINCT CASE WHEN t.id_estado IN (1, 2) AND t.id_prioridad = 1 THEN t.id_ticket END) > 0 THEN 'Rojo'
        WHEN COUNT(DISTINCT CASE WHEN t.id_estado IN (1, 2) THEN t.id_ticket END) > 0 THEN 'Amarillo'
        ELSE 'Verde'
    END as color_semaforo
FROM maquinas m
LEFT JOIN plantas p ON m.id_planta = p.id_planta
LEFT JOIN lineas l ON m.id_linea = l.id_linea
LEFT JOIN tickets t ON m.id_maquina = t.id_maquina
LEFT JOIN mantenimientos mt ON m.id_maquina = mt.id_maquina
WHERE m.estado = 'Activa'
GROUP BY m.id_maquina;

-- Vista: Resumen de tickets por estado
CREATE VIEW vista_resumen_tickets AS
SELECT 
    t.id_ticket,
    t.codigo_ticket,
    m.codigo_maquina,
    m.marca,
    m.modelo,
    p.nombre_planta,
    l.nombre_linea,
    pr.nombre_prioridad,
    pr.nivel as nivel_prioridad,
    e.nombre_estado,
    e.orden as orden_estado,
    tf.nombre_tipo_falla,
    CONCAT(u_reporta.nombre, ' ', u_reporta.apellido) as reportado_por,
    CONCAT(u_tecnico.nombre, ' ', u_tecnico.apellido) as tecnico_responsable,
    t.fecha_creacion,
    t.fecha_asignacion,
    t.fecha_resolucion,
    t.fecha_cierre,
    t.tiempo_total
FROM tickets t
INNER JOIN maquinas m ON t.id_maquina = m.id_maquina
INNER JOIN plantas p ON m.id_planta = p.id_planta
INNER JOIN lineas l ON m.id_linea = l.id_linea
INNER JOIN prioridades pr ON t.id_prioridad = pr.id_prioridad
INNER JOIN estados_ticket e ON t.id_estado = e.id_estado
INNER JOIN tipos_falla tf ON t.id_tipo_falla = tf.id_tipo_falla
INNER JOIN usuarios u_reporta ON t.id_usuario_reporta = u_reporta.id_usuario
LEFT JOIN usuarios u_tecnico ON t.id_tecnico_responsable = u_tecnico.id_usuario
ORDER BY pr.nivel ASC, t.fecha_creacion DESC;

-- ============================================
-- TRIGGERS
-- ============================================

-- Trigger: Generar código de ticket automáticamente
DELIMITER //
CREATE TRIGGER before_insert_ticket
BEFORE INSERT ON tickets
FOR EACH ROW
BEGIN
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
END//
DELIMITER ;

-- Trigger: Calcular tiempos cuando cambia el estado del ticket
DELIMITER //
CREATE TRIGGER before_update_ticket
BEFORE UPDATE ON tickets
FOR EACH ROW
BEGIN
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
END//
DELIMITER ;

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
