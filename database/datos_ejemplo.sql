-- ============================================
-- DATOS DE EJEMPLO - SISTEMA MANTENIMIENTO MATTEL
-- Ejecutar DESPUÉS de schema.sql
-- ============================================

USE mattel_mantenimiento;

-- ============================================
-- USUARIOS DE EJEMPLO
-- ============================================

-- Administradores
INSERT INTO usuarios (nombre, apellido, email, usuario, password, telefono, id_rol, id_planta, estado) VALUES
('Carlos', 'Ramírez', 'carlos.ramirez@mattel.com', 'cramirez', MD5('admin123'), '555-0101', 1, NULL, 'Activo'),
('Ana', 'Martínez', 'ana.martinez@mattel.com', 'amartinez', MD5('admin123'), '555-0102', 1, NULL, 'Activo');

-- Técnicos
INSERT INTO usuarios (nombre, apellido, email, usuario, password, telefono, id_rol, id_planta, estado) VALUES
('Luis', 'García', 'luis.garcia@mattel.com', 'lgarcia', MD5('tecnico123'), '555-0201', 2, 1, 'Activo'),
('María', 'López', 'maria.lopez@mattel.com', 'mlopez', MD5('tecnico123'), '555-0202', 2, 1, 'Activo'),
('Pedro', 'Hernández', 'pedro.hernandez@mattel.com', 'phernandez', MD5('tecnico123'), '555-0203', 2, 2, 'Activo'),
('Sofia', 'Torres', 'sofia.torres@mattel.com', 'storres', MD5('tecnico123'), '555-0204', 2, 3, 'Activo'),
('Jorge', 'Sánchez', 'jorge.sanchez@mattel.com', 'jsanchez', MD5('tecnico123'), '555-0205', 2, 4, 'Activo');

-- Operadores
INSERT INTO usuarios (nombre, apellido, email, usuario, password, telefono, id_rol, id_planta, estado) VALUES
('Roberto', 'Díaz', 'roberto.diaz@mattel.com', 'rdiaz', MD5('operador123'), '555-0301', 3, 1, 'Activo'),
('Carmen', 'Ruiz', 'carmen.ruiz@mattel.com', 'cruiz', MD5('operador123'), '555-0302', 3, 1, 'Activo'),
('Miguel', 'Flores', 'miguel.flores@mattel.com', 'mflores', MD5('operador123'), '555-0303', 3, 2, 'Activo'),
('Laura', 'Morales', 'laura.morales@mattel.com', 'lmorales', MD5('operador123'), '555-0304', 3, 3, 'Activo'),
('Diego', 'Castro', 'diego.castro@mattel.com', 'dcastro', MD5('operador123'), '555-0305', 3, 4, 'Activo'),
('Patricia', 'Vargas', 'patricia.vargas@mattel.com', 'pvargas', MD5('operador123'), '555-0306', 3, 5, 'Activo');

-- ============================================
-- LÍNEAS DE PRODUCCIÓN
-- ============================================

INSERT INTO lineas (nombre_linea, id_planta, id_prioridad, estado, descripcion) VALUES
-- Planta A
('Línea A1 - Inyección', 1, 1, 'Activa', 'Línea principal de inyección de plástico'),
('Línea A2 - Ensamble', 1, 2, 'Activa', 'Línea de ensamble de juguetes'),
('Línea A3 - Empaque', 1, 3, 'Activa', 'Línea de empaque final'),

-- Planta B
('Línea B1 - Inyección', 2, 1, 'Activa', 'Línea de inyección de alta velocidad'),
('Línea B2 - Pintura', 2, 2, 'Activa', 'Línea de pintura automatizada'),

-- Planta C
('Línea C1 - Moldeado', 3, 1, 'Activa', 'Línea de moldeado por soplado'),
('Línea C2 - Control Calidad', 3, 3, 'Activa', 'Línea de inspección y control'),

-- Planta D
('Línea D1 - Ensamble Electrónico', 4, 1, 'Activa', 'Línea de ensamble de componentes electrónicos'),
('Línea D2 - Pruebas', 4, 2, 'Activa', 'Línea de pruebas funcionales'),

-- Planta E
('Línea E1 - Empaque Premium', 5, 2, 'Activa', 'Línea de empaque para productos premium');

-- ============================================
-- MÁQUINAS
-- ============================================

-- Planta A - Línea A1
INSERT INTO maquinas (codigo_maquina, marca, modelo, numero_serie, id_planta, id_linea, area, fecha_instalacion, estado, created_by) VALUES
('MAQ-INY-001', 'Engel', 'e-victory 200', 'ENG-2023-001', 1, 1, 'Zona Norte', '2023-01-15', 'Activa', 2),
('MAQ-INY-002', 'Engel', 'e-victory 200', 'ENG-2023-002', 1, 1, 'Zona Norte', '2023-01-15', 'Activa', 2),
('MAQ-INY-003', 'Haitian', 'Mars 250', 'HAI-2023-001', 1, 1, 'Zona Sur', '2023-02-10', 'Activa', 2),

-- Planta A - Línea A2
('MAQ-ENS-001', 'ABB', 'IRB 6700', 'ABB-2023-001', 1, 2, 'Zona Central', '2023-03-05', 'Activa', 2),
('MAQ-ENS-002', 'KUKA', 'KR 210', 'KUKA-2023-001', 1, 2, 'Zona Central', '2023-03-05', 'Activa', 2),

-- Semi Screw y Full Auto Screw (para los tipos de falla específicos)
('MAQ-SCR-001', 'EKF', 'HS2100A', 'EKF-2023-001', 1, 2, 'Zona Este', '2023-04-01', 'Activa', 2),
('MAQ-SCR-002', 'EKF', 'S9001-A2', 'EKF-2023-002', 1, 2, 'Zona Este', '2023-04-01', 'Activa', 2),

-- Planta B
('MAQ-INY-004', 'Sumitomo', 'SE180DUZ', 'SUM-2023-001', 2, 4, 'Zona A', '2023-05-10', 'Activa', 2),
('MAQ-PIN-001', 'Dürr', 'EcoRP L033', 'DUR-2023-001', 2, 5, 'Zona B', '2023-06-15', 'Activa', 2),

-- Planta C
('MAQ-MOL-001', 'Kautex', 'KBB 3.0', 'KAU-2023-001', 3, 6, 'Zona Principal', '2023-07-20', 'Activa', 2),
('MAQ-CAL-001', 'Cognex', 'In-Sight 9912', 'COG-2023-001', 3, 7, 'Zona Inspección', '2023-08-01', 'Activa', 2),

-- Planta D
('MAQ-ELE-001', 'Universal Robots', 'UR10e', 'UR-2023-001', 4, 8, 'Zona Electrónica', '2023-09-10', 'Activa', 2),
('MAQ-PRU-001', 'National Instruments', 'PXI-1095', 'NI-2023-001', 4, 9, 'Zona Pruebas', '2023-10-05', 'Activa', 2),

-- Planta E
('MAQ-EMP-001', 'Bosch', 'SVE 3210', 'BOS-2023-001', 5, 10, 'Zona Premium', '2023-11-01', 'Activa', 2);

-- ============================================
-- MANTENIMIENTOS
-- ============================================

-- Mantenimientos preventivos recientes
INSERT INTO mantenimientos (id_maquina, id_tipo_mantenimiento, id_tecnico_responsable, fecha_mantenimiento, actividades_realizadas, repuestos_utilizados, tiempo_empleado, costo_aproximado, created_by) VALUES
(1, 1, 3, '2025-10-15 08:00:00', 'Lubricación de componentes móviles, revisión de sistema hidráulico, limpieza de filtros', 'Aceite hidráulico ISO 68 (5L), Filtros de aire (2 pzas)', 120, 250.00, 3),
(1, 1, 3, '2025-09-15 08:00:00', 'Inspección general, ajuste de parámetros, calibración de sensores', 'Ninguno', 90, 0.00, 3),
(2, 1, 4, '2025-10-20 09:00:00', 'Mantenimiento preventivo trimestral, revisión eléctrica, limpieza profunda', 'Contactores (2 pzas), Fusibles (5 pzas)', 150, 180.00, 4),
(3, 1, 3, '2025-10-25 10:00:00', 'Cambio de aceite, revisión de sistema de enfriamiento, inspección de mangueras', 'Aceite sintético (4L), Refrigerante (2L)', 100, 150.00, 3),

-- Mantenimientos correctivos
(6, 2, 3, '2025-11-01 14:30:00', 'Reparación de atoramiento en tambor, limpieza de mecanismo, ajuste de tolerancias', 'Rodamientos (4 pzas), Resortes (2 pzas)', 180, 320.00, 3),
(7, 2, 4, '2025-11-03 11:00:00', 'Corrección de problema de deslizamiento, ajuste de torque, reemplazo de punta', 'Punta de destornillador (1 pza), Tornillos de ajuste (10 pzas)', 90, 85.00, 4),

-- Mantenimientos en otras máquinas
(8, 1, 5, '2025-10-18 08:30:00', 'Mantenimiento preventivo mensual, revisión de sistema neumático', 'Ninguno', 60, 0.00, 5),
(9, 1, 6, '2025-10-22 09:00:00', 'Limpieza de sistema de pintura, calibración de boquillas', 'Boquillas (3 pzas)', 120, 210.00, 6),
(10, 2, 6, '2025-11-05 15:00:00', 'Reparación de fuga de aire, reemplazo de válvula', 'Válvula neumática (1 pza), Teflón (1 rollo)', 75, 95.00, 6);

-- ============================================
-- TICKETS
-- ============================================

-- Tickets FINALIZADOS (para historial)
INSERT INTO tickets (id_maquina, id_tipo_falla, id_prioridad, id_estado, id_tecnico_responsable, id_usuario_reporta, descripcion_falla, causa_raiz, solucion_aplicada, fecha_creacion, fecha_asignacion, fecha_resolucion, fecha_cierre, tiempo_respuesta, tiempo_resolucion, tiempo_total) VALUES
(1, 10, 2, 4, 3, 8, 'La máquina presenta ruido anormal en el motor principal', 'Falta de lubricación en rodamientos', 'Se aplicó lubricación y se reemplazaron rodamientos desgastados', '2025-11-01 08:00:00', '2025-11-01 08:15:00', '2025-11-01 10:30:00', '2025-11-01 11:00:00', 15, 135, 180),
(2, 11, 3, 4, 4, 9, 'Sensor de temperatura no responde correctamente', 'Sensor descalibrado por variación de voltaje', 'Se recalibró el sensor y se verificó el sistema eléctrico', '2025-11-02 10:00:00', '2025-11-02 10:30:00', '2025-11-02 12:00:00', '2025-11-02 12:15:00', 30, 90, 135),
(3, 12, 4, 4, 3, 8, 'Fuga menor de aceite en sistema hidráulico', 'Empaque deteriorado por uso normal', 'Se reemplazó el empaque y se verificó el nivel de aceite', '2025-11-03 14:00:00', '2025-11-03 14:20:00', '2025-11-03 15:30:00', '2025-11-03 15:45:00', 20, 70, 105);

-- Tickets EN CONFIRMACIÓN (esperando cierre del admin)
INSERT INTO tickets (id_maquina, id_tipo_falla, id_prioridad, id_estado, id_tecnico_responsable, id_usuario_reporta, descripcion_falla, causa_raiz, solucion_aplicada, fecha_creacion, fecha_asignacion, fecha_resolucion, tiempo_respuesta, tiempo_resolucion) VALUES
(6, 1, 2, 3, 3, 8, 'Atoramiento de tornillo en tambor - Semi Screw', 'Acumulación de residuos plásticos en el tambor', 'Se limpió el tambor completamente y se ajustaron las tolerancias', '2025-11-07 09:00:00', '2025-11-07 09:10:00', '2025-11-07 11:30:00', 10, 140),
(9, 11, 3, 3, 6, 12, 'Sistema de pintura presenta irregularidades en el acabado', 'Boquillas parcialmente obstruidas', 'Se limpiaron y reemplazaron las boquillas afectadas', '2025-11-07 13:00:00', '2025-11-07 13:25:00', '2025-11-07 15:00:00', 25, 95);

-- Tickets EN PROGRESO (técnico trabajando)
INSERT INTO tickets (id_maquina, id_tipo_falla, id_prioridad, id_estado, id_tecnico_responsable, id_usuario_reporta, descripcion_falla, fecha_creacion, fecha_asignacion, tiempo_respuesta) VALUES
(7, 5, 2, 2, 4, 9, 'Tornillo no ajusta bien - Full Auto Screw - hay deslizamiento constante', '2025-11-08 08:00:00', '2025-11-08 08:20:00', 20),
(8, 10, 3, 2, 5, 11, 'Máquina de inyección presenta variación en temperatura de molde', '2025-11-08 09:30:00', '2025-11-08 10:00:00', 30),
(11, 12, 4, 2, 6, 13, 'Vibración excesiva en sistema de transporte', '2025-11-08 11:00:00', '2025-11-08 11:15:00', 15);

-- Tickets PENDIENTES (sin asignar)
INSERT INTO tickets (id_maquina, id_tipo_falla, id_prioridad, id_estado, id_usuario_reporta, descripcion_falla, foto_url, fecha_creacion) VALUES
(4, 10, 1, 1, 8, 'Robot de ensamble detenido - no responde a comandos - LÍNEA PARADA', 'uploads/tickets/ticket_001.jpg', '2025-11-08 12:00:00'),
(5, 11, 2, 1, 9, 'Brazo robótico presenta movimientos erráticos en eje Y', NULL, '2025-11-08 12:30:00'),
(10, 12, 3, 1, 12, 'Sistema de soplado con presión irregular', NULL, '2025-11-08 13:00:00'),
(12, 10, 4, 1, 11, 'Pantalla HMI con pixeles muertos - dificulta lectura', NULL, '2025-11-08 13:15:00');

-- ============================================
-- ACTUALIZAR CÓDIGOS DE TICKET
-- (Los triggers deberían generarlos, pero por si acaso)
-- ============================================

UPDATE tickets SET codigo_ticket = CONCAT('TKT-20251101-', LPAD(id_ticket, 4, '0')) WHERE id_ticket <= 3;
UPDATE tickets SET codigo_ticket = CONCAT('TKT-20251107-', LPAD(id_ticket - 3, 4, '0')) WHERE id_ticket BETWEEN 4 AND 5;
UPDATE tickets SET codigo_ticket = CONCAT('TKT-20251108-', LPAD(id_ticket - 5, 4, '0')) WHERE id_ticket >= 6;

-- ============================================
-- VERIFICACIÓN DE DATOS
-- ============================================

-- Resumen de datos insertados
SELECT 'RESUMEN DE DATOS INSERTADOS' as '';
SELECT '=========================' as '';

SELECT CONCAT('Usuarios: ', COUNT(*)) as total FROM usuarios;
SELECT CONCAT('Líneas: ', COUNT(*)) as total FROM lineas;
SELECT CONCAT('Máquinas: ', COUNT(*)) as total FROM maquinas;
SELECT CONCAT('Mantenimientos: ', COUNT(*)) as total FROM mantenimientos;
SELECT CONCAT('Tickets: ', COUNT(*)) as total FROM tickets;

SELECT '' as '';
SELECT 'TICKETS POR ESTADO' as '';
SELECT '==================' as '';
SELECT 
    e.nombre_estado,
    COUNT(t.id_ticket) as cantidad
FROM estados_ticket e
LEFT JOIN tickets t ON e.id_estado = t.id_estado
GROUP BY e.id_estado
ORDER BY e.orden;

SELECT '' as '';
SELECT 'MÁQUINAS CON SEMÁFORO' as '';
SELECT '=====================' as '';
SELECT 
    codigo_maquina,
    nombre_planta,
    tickets_activos,
    color_semaforo
FROM vista_estado_maquinas
ORDER BY 
    CASE color_semaforo 
        WHEN 'Rojo' THEN 1 
        WHEN 'Amarillo' THEN 2 
        ELSE 3 
    END;

-- ============================================
-- CREDENCIALES DE ACCESO
-- ============================================

SELECT '' as '';
SELECT 'CREDENCIALES DE ACCESO' as '';
SELECT '======================' as '';
SELECT '' as '';
SELECT 'ADMINISTRADOR:' as '';
SELECT '  Usuario: admin' as '';
SELECT '  Password: admin123' as '';
SELECT '' as '';
SELECT 'TÉCNICO (ejemplo):' as '';
SELECT '  Usuario: lgarcia' as '';
SELECT '  Password: tecnico123' as '';
SELECT '' as '';
SELECT 'OPERADOR (ejemplo):' as '';
SELECT '  Usuario: rdiaz' as '';
SELECT '  Password: operador123' as '';

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
