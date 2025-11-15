-- ============================================
-- ACTUALIZACIÓN: Agregar campo visible a tickets
-- ============================================

USE mattel_mantenimiento;

-- Agregar columna visible si no existe
ALTER TABLE tickets 
ADD COLUMN IF NOT EXISTS visible TINYINT(1) DEFAULT 1 
COMMENT 'Si es 0, el ticket está oculto (eliminado lógicamente)'
AFTER observaciones;

-- Agregar índice para búsquedas rápidas
ALTER TABLE tickets 
ADD INDEX IF NOT EXISTS idx_visible (visible);

-- Establecer todos los tickets existentes como visibles
UPDATE tickets 
SET visible = 1 
WHERE visible IS NULL;

-- Verificar la actualización
SELECT 
    id_ticket,
    codigo_ticket,
    id_estado,
    visible,
    CASE 
        WHEN visible = 1 THEN '✅ Visible'
        WHEN visible = 0 THEN '❌ Oculto'
        ELSE '⚠️ NULL'
    END as estado_visibilidad
FROM tickets 
ORDER BY id_ticket DESC
LIMIT 10;

-- ============================================
-- NOTAS:
-- ============================================
-- 1. El campo 'visible' controla si el ticket se muestra en el sistema
-- 2. visible = 1: El ticket es visible (por defecto)
-- 3. visible = 0: El ticket está oculto (eliminado lógicamente)
-- 4. Solo los administradores pueden ocultar tickets
-- 5. Solo se pueden ocultar tickets con estado "Finalizado" (id_estado = 4)
-- 6. Los tickets ocultos NO se eliminan de la base de datos (eliminación lógica)
-- 7. Esto permite mantener el historial completo para auditorías

-- ============================================
-- CONSULTAS ÚTILES:
-- ============================================

-- Ver solo tickets visibles
-- SELECT * FROM tickets WHERE visible = 1;

-- Ver solo tickets ocultos
-- SELECT * FROM tickets WHERE visible = 0;

-- Ocultar un ticket específico (solo si está finalizado)
-- UPDATE tickets SET visible = 0 WHERE id_ticket = X AND id_estado = 4;

-- Restaurar un ticket oculto
-- UPDATE tickets SET visible = 1 WHERE id_ticket = X;

-- Contar tickets visibles vs ocultos
-- SELECT 
--     SUM(CASE WHEN visible = 1 THEN 1 ELSE 0 END) as visibles,
--     SUM(CASE WHEN visible = 0 THEN 1 ELSE 0 END) as ocultos,
--     COUNT(*) as total
-- FROM tickets;
