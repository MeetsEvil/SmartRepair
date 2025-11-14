-- ============================================
-- ACTUALIZACIÓN: Agregar códigos QR a máquinas existentes
-- ============================================

USE mattel_mantenimiento;

-- Agregar columna codigoQR si no existe (para bases de datos ya creadas)
ALTER TABLE maquinas 
ADD COLUMN IF NOT EXISTS codigoQR VARCHAR(100) 
COMMENT 'Código QR único: ID + código de máquina (ej: 3MAQ-INY-005)'
AFTER imagen;

-- Agregar índice para búsquedas rápidas por QR
ALTER TABLE maquinas 
ADD INDEX IF NOT EXISTS idx_codigoqr (codigoQR);

-- Generar códigos QR para todas las máquinas existentes
-- Formato: ID + código_maquina (sin espacios ni guiones entre ellos)
-- Ejemplo: máquina ID 3 con código "MAQ-INY-005" → "3MAQ-INY-005"
UPDATE maquinas 
SET codigoQR = CONCAT(id_maquina, codigo_maquina)
WHERE codigoQR IS NULL OR codigoQR = '';

-- Verificar las actualizaciones
SELECT 
    id_maquina, 
    codigo_maquina, 
    codigoQR,
    marca,
    modelo
FROM maquinas 
ORDER BY id_maquina;

-- ============================================
-- NOTA SOBRE TRIGGERS:
-- ============================================
-- Los triggers automáticos han sido removidos para evitar conflictos.
-- El código QR se genera automáticamente en el archivo PHP:
-- modules/maquinas/procesar_crear_maquina.php
-- 
-- Esto permite mayor control y evita problemas con valores NULL o temporales.
-- El proceso es:
-- 1. INSERT con codigoQR = 'TEMP'
-- 2. Se obtiene el ID generado
-- 3. UPDATE con codigoQR = ID + codigo_maquina

-- ============================================
-- NOTAS:
-- ============================================
-- 1. El código QR se genera automáticamente al crear una máquina
-- 2. Formato: ID + codigo_maquina (sin separadores)
-- 3. Ejemplo: ID=3, codigo="MAQ-INY-005" → codigoQR="3MAQ-INY-005"
-- 4. El código QR NO cambia aunque se edite el codigo_maquina
-- 5. Esto permite identificar la máquina de forma única e inmutable
-- 6. Para buscar por QR: SELECT * FROM maquinas WHERE codigoQR = 'texto_escaneado'

-- ============================================
-- EJEMPLOS DE USO:
-- ============================================

-- Buscar máquina por código QR escaneado
-- SELECT * FROM maquinas WHERE codigoQR = '3MAQ-INY-005';

-- Listar todas las máquinas con sus códigos QR
-- SELECT id_maquina, codigo_maquina, codigoQR, marca, modelo FROM maquinas;

-- Regenerar código QR para una máquina específica
-- UPDATE maquinas SET codigoQR = CONCAT(id_maquina, codigo_maquina) WHERE id_maquina = 5;
