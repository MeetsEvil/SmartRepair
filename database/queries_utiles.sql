-- ============================================
-- CONSULTAS ÚTILES - SISTEMA MANTENIMIENTO MATTEL
-- ============================================

USE mattel_mantenimiento;

-- ============================================
-- CONSULTAS PARA DASHBOARD
-- ============================================

-- Total de máquinas por estado
SELECT 
    estado,
    COUNT(*) as total
FROM maquinas
GROUP BY estado;

-- Total de tickets por estado
SELECT 
    e.nombre_estado,
    COUNT(*) as total
FROM tickets t
INNER JOIN estados_ticket e ON t.id_estado = e.id_estado
GROUP BY e.nombre_estado, e.orden
ORDER BY e.orden;

-- Tickets críticos pendientes
SELECT 
    t.codigo_ticket,
    m.codigo_maquina,
    p.nombre_planta,
    t.descripcion_falla,
    t.fecha_creacion,
    TIMESTAMPDIFF(MINUTE, t.fecha_creacion, NOW()) as minutos_sin_atender
FROM tickets t
INNER JOIN maquinas m ON t.id_maquina = m.id_maquina
INNER JOIN plantas p ON m.id_planta = p.id_planta
WHERE t.id_prioridad = 1 
  AND t.id_estado IN (1, 2)
ORDER BY t.fecha_creacion ASC;

-- Máquinas con más tickets en el último mes
SELECT 
    m.codigo_maquina,
    m.marca,
    m.modelo,
    COUNT(t.id_ticket) as total_tickets,
    SUM(CASE WHEN t.id_prioridad = 1 THEN 1 ELSE 0 END) as tickets_criticos
FROM maquinas m
INNER JOIN tickets t ON m.id_maquina = t.id_maquina
WHERE t.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY m.id_maquina
ORDER BY total_tickets DESC
LIMIT 10;

-- Técnicos con más tickets asignados activos
SELECT 
    CONCAT(u.nombre, ' ', u.apellido) as tecnico,
    COUNT(t.id_ticket) as tickets_activos,
    SUM(CASE WHEN t.id_prioridad = 1 THEN 1 ELSE 0 END) as criticos,
    SUM(CASE WHEN t.id_estado = 1 THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN t.id_estado = 2 THEN 1 ELSE 0 END) as en_progreso
FROM usuarios u
INNER JOIN tickets t ON u.id_usuario = t.id_tecnico_responsable
WHERE t.id_estado IN (1, 2)
  AND u.id_rol = 2
GROUP BY u.id_usuario
ORDER BY tickets_activos DESC;

-- ============================================
-- CONSULTAS PARA MÓDULO DE MÁQUINAS
-- ============================================

-- Buscar máquina por código (para QR)
SELECT 
    m.*,
    p.nombre_planta,
    l.nombre_linea,
    l.id_prioridad as prioridad_linea
FROM maquinas m
INNER JOIN plantas p ON m.id_planta = p.id_planta
INNER JOIN lineas l ON m.id_linea = l.id_linea
WHERE m.codigo_maquina = 'MAQ-INY-005';

-- Listado completo de máquinas con estado semáforo
SELECT * FROM vista_estado_maquinas
ORDER BY color_semaforo ASC, codigo_maquina ASC;

-- Máquinas por planta y línea
SELECT 
    p.nombre_planta,
    l.nombre_linea,
    COUNT(m.id_maquina) as total_maquinas,
    SUM(CASE WHEN m.estado = 'Activa' THEN 1 ELSE 0 END) as activas,
    SUM(CASE WHEN m.estado = 'Inactiva' THEN 1 ELSE 0 END) as inactivas
FROM plantas p
LEFT JOIN lineas l ON p.id_planta = l.id_planta
LEFT JOIN maquinas m ON l.id_linea = m.id_linea
GROUP BY p.id_planta, l.id_linea
ORDER BY p.nombre_planta, l.nombre_linea;

-- ============================================
-- CONSULTAS PARA MÓDULO DE MANTENIMIENTO
-- ============================================

-- Historial de mantenimiento de una máquina
SELECT 
    mt.id_mantenimiento,
    mt.fecha_mantenimiento,
    tm.nombre_tipo as tipo_mantenimiento,
    CONCAT(u.nombre, ' ', u.apellido) as tecnico,
    mt.actividades_realizadas,
    mt.repuestos_utilizados,
    mt.tiempo_empleado,
    mt.costo_aproximado
FROM mantenimientos mt
INNER JOIN tipos_mantenimiento tm ON mt.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
INNER JOIN usuarios u ON mt.id_tecnico_responsable = u.id_usuario
WHERE mt.id_maquina = 1
ORDER BY mt.fecha_mantenimiento DESC;

-- Mantenimientos realizados por técnico en un periodo
SELECT 
    CONCAT(u.nombre, ' ', u.apellido) as tecnico,
    COUNT(mt.id_mantenimiento) as total_mantenimientos,
    SUM(mt.tiempo_empleado) as minutos_totales,
    SUM(mt.costo_aproximado) as costo_total
FROM mantenimientos mt
INNER JOIN usuarios u ON mt.id_tecnico_responsable = u.id_usuario
WHERE mt.fecha_mantenimiento BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY u.id_usuario
ORDER BY total_mantenimientos DESC;

-- Máquinas sin mantenimiento en los últimos 90 días
SELECT 
    m.codigo_maquina,
    m.marca,
    m.modelo,
    p.nombre_planta,
    MAX(mt.fecha_mantenimiento) as ultimo_mantenimiento,
    DATEDIFF(NOW(), MAX(mt.fecha_mantenimiento)) as dias_sin_mantenimiento
FROM maquinas m
LEFT JOIN mantenimientos mt ON m.id_maquina = mt.id_maquina
INNER JOIN plantas p ON m.id_planta = p.id_planta
WHERE m.estado = 'Activa'
GROUP BY m.id_maquina
HAVING ultimo_mantenimiento IS NULL 
    OR dias_sin_mantenimiento > 90
ORDER BY dias_sin_mantenimiento DESC;

-- ============================================
-- CONSULTAS PARA MÓDULO DE TICKETS
-- ============================================

-- Tickets pendientes (para columna "To Do")
SELECT * FROM vista_resumen_tickets
WHERE orden_estado = 1
ORDER BY nivel_prioridad ASC, fecha_creacion ASC;

-- Tickets en progreso (para columna "Doing")
SELECT * FROM vista_resumen_tickets
WHERE orden_estado = 2
ORDER BY nivel_prioridad ASC, fecha_creacion ASC;

-- Tickets en confirmación (para columna "En Confirmación")
SELECT * FROM vista_resumen_tickets
WHERE orden_estado = 3
ORDER BY fecha_resolucion DESC;

-- Tickets finalizados (para columna "Done")
SELECT * FROM vista_resumen_tickets
WHERE orden_estado = 4
ORDER BY fecha_cierre DESC
LIMIT 50;

-- Detalle completo de un ticket
SELECT 
    t.*,
    m.codigo_maquina,
    m.marca,
    m.modelo,
    m.area,
    p.nombre_planta,
    l.nombre_linea,
    pr.nombre_prioridad,
    e.nombre_estado,
    tf.nombre_tipo_falla,
    CONCAT(u_reporta.nombre, ' ', u_reporta.apellido) as reportado_por,
    u_reporta.email as email_reportante,
    CONCAT(u_tecnico.nombre, ' ', u_tecnico.apellido) as tecnico_responsable,
    u_tecnico.email as email_tecnico
FROM tickets t
INNER JOIN maquinas m ON t.id_maquina = m.id_maquina
INNER JOIN plantas p ON m.id_planta = p.id_planta
INNER JOIN lineas l ON m.id_linea = l.id_linea
INNER JOIN prioridades pr ON t.id_prioridad = pr.id_prioridad
INNER JOIN estados_ticket e ON t.id_estado = e.id_estado
INNER JOIN tipos_falla tf ON t.id_tipo_falla = tf.id_tipo_falla
INNER JOIN usuarios u_reporta ON t.id_usuario_reporta = u_reporta.id_usuario
LEFT JOIN usuarios u_tecnico ON t.id_tecnico_responsable = u_tecnico.id_usuario
WHERE t.codigo_ticket = 'TKT-20251108-0001';

-- Tickets por tipo de falla (estadísticas)
SELECT 
    tf.nombre_tipo_falla,
    tf.maquina_aplicable,
    COUNT(t.id_ticket) as total_tickets,
    AVG(t.tiempo_total) as tiempo_promedio_resolucion,
    SUM(CASE WHEN t.id_estado = 4 THEN 1 ELSE 0 END) as resueltos,
    SUM(CASE WHEN t.id_estado IN (1,2,3) THEN 1 ELSE 0 END) as activos
FROM tipos_falla tf
LEFT JOIN tickets t ON tf.id_tipo_falla = t.id_tipo_falla
GROUP BY tf.id_tipo_falla
ORDER BY total_tickets DESC;

-- Tiempo promedio de resolución por prioridad
SELECT 
    pr.nombre_prioridad,
    COUNT(t.id_ticket) as total_tickets,
    AVG(t.tiempo_respuesta) as promedio_respuesta_min,
    AVG(t.tiempo_resolucion) as promedio_resolucion_min,
    AVG(t.tiempo_total) as promedio_total_min
FROM tickets t
INNER JOIN prioridades pr ON t.id_prioridad = pr.id_prioridad
WHERE t.id_estado = 4
GROUP BY pr.id_prioridad
ORDER BY pr.nivel ASC;

-- ============================================
-- CONSULTAS PARA MÓDULO DE USUARIOS
-- ============================================

-- Listado de usuarios por rol
SELECT 
    u.id_usuario,
    u.nombre,
    u.apellido,
    u.email,
    u.usuario,
    r.nombre_rol,
    p.nombre_planta,
    u.estado,
    u.created_at
FROM usuarios u
INNER JOIN roles r ON u.id_rol = r.id_rol
LEFT JOIN plantas p ON u.id_planta = p.id_planta
ORDER BY r.id_rol, u.nombre;

-- Técnicos disponibles (activos)
SELECT 
    u.id_usuario,
    CONCAT(u.nombre, ' ', u.apellido) as nombre_completo,
    u.email,
    p.nombre_planta,
    COUNT(t.id_ticket) as tickets_activos
FROM usuarios u
LEFT JOIN tickets t ON u.id_usuario = t.id_tecnico_responsable 
    AND t.id_estado IN (1, 2)
LEFT JOIN plantas p ON u.id_planta = p.id_planta
WHERE u.id_rol = 2 
  AND u.estado = 'Activo'
GROUP BY u.id_usuario
ORDER BY tickets_activos ASC;

-- ============================================
-- CONSULTAS PARA MÓDULO DE LÍNEAS
-- ============================================

-- Listado de líneas con estadísticas
SELECT 
    l.id_linea,
    l.nombre_linea,
    p.nombre_planta,
    pr.nombre_prioridad,
    l.estado,
    COUNT(DISTINCT m.id_maquina) as total_maquinas,
    COUNT(DISTINCT t.id_ticket) as total_tickets_activos
FROM lineas l
INNER JOIN plantas p ON l.id_planta = p.id_planta
INNER JOIN prioridades pr ON l.id_prioridad = pr.id_prioridad
LEFT JOIN maquinas m ON l.id_linea = m.id_linea AND m.estado = 'Activa'
LEFT JOIN tickets t ON m.id_maquina = t.id_maquina AND t.id_estado IN (1, 2)
GROUP BY l.id_linea
ORDER BY p.nombre_planta, l.nombre_linea;

-- ============================================
-- CONSULTAS PARA REPORTES
-- ============================================

-- Reporte mensual de tickets
SELECT 
    DATE_FORMAT(t.fecha_creacion, '%Y-%m') as mes,
    COUNT(*) as total_tickets,
    SUM(CASE WHEN t.id_estado = 4 THEN 1 ELSE 0 END) as finalizados,
    SUM(CASE WHEN t.id_prioridad = 1 THEN 1 ELSE 0 END) as criticos,
    AVG(CASE WHEN t.id_estado = 4 THEN t.tiempo_total END) as tiempo_promedio
FROM tickets t
WHERE t.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY mes
ORDER BY mes DESC;

-- Reporte de disponibilidad de máquinas
SELECT 
    p.nombre_planta,
    COUNT(DISTINCT m.id_maquina) as total_maquinas,
    COUNT(DISTINCT CASE WHEN t.id_estado IN (1,2) THEN m.id_maquina END) as maquinas_con_tickets,
    ROUND(
        (COUNT(DISTINCT m.id_maquina) - COUNT(DISTINCT CASE WHEN t.id_estado IN (1,2) THEN m.id_maquina END)) 
        / COUNT(DISTINCT m.id_maquina) * 100, 
        2
    ) as porcentaje_disponibilidad
FROM plantas p
LEFT JOIN maquinas m ON p.id_planta = m.id_planta AND m.estado = 'Activa'
LEFT JOIN tickets t ON m.id_maquina = t.id_maquina
GROUP BY p.id_planta
ORDER BY porcentaje_disponibilidad ASC;

-- Top 10 fallas más comunes
SELECT 
    tf.nombre_tipo_falla,
    COUNT(*) as total_ocurrencias,
    AVG(t.tiempo_total) as tiempo_promedio_resolucion
FROM tickets t
INNER JOIN tipos_falla tf ON t.id_tipo_falla = tf.id_tipo_falla
WHERE t.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
GROUP BY tf.id_tipo_falla
ORDER BY total_ocurrencias DESC
LIMIT 10;

-- ============================================
-- CONSULTAS PARA NOTIFICACIONES
-- ============================================

-- Obtener emails de técnicos para notificar nuevo ticket
SELECT DISTINCT
    u.email,
    CONCAT(u.nombre, ' ', u.apellido) as nombre_completo
FROM usuarios u
WHERE u.id_rol = 2 
  AND u.estado = 'Activo'
  AND (u.id_planta = 1 OR u.id_planta IS NULL);

-- Tickets sin asignar por más de 30 minutos
SELECT 
    t.codigo_ticket,
    m.codigo_maquina,
    p.nombre_planta,
    pr.nombre_prioridad,
    t.descripcion_falla,
    TIMESTAMPDIFF(MINUTE, t.fecha_creacion, NOW()) as minutos_sin_asignar
FROM tickets t
INNER JOIN maquinas m ON t.id_maquina = m.id_maquina
INNER JOIN plantas p ON m.id_planta = p.id_planta
INNER JOIN prioridades pr ON t.id_prioridad = pr.id_prioridad
WHERE t.id_estado = 1 
  AND TIMESTAMPDIFF(MINUTE, t.fecha_creacion, NOW()) > 30
ORDER BY pr.nivel ASC, minutos_sin_asignar DESC;

-- ============================================
-- FIN DE CONSULTAS ÚTILES
-- ============================================
