-- ============================================
-- SCRIPT DE CORRECCIÓN: Eliminar triggers conflictivos
-- ============================================

USE mattel_mantenimiento;

-- Eliminar triggers que puedan causar conflictos
DROP TRIGGER IF EXISTS before_insert_maquina_qr;
DROP TRIGGER IF EXISTS after_insert_maquina_qr;

-- Verificar que no existan triggers
SHOW TRIGGERS WHERE `Table` = 'maquinas';

-- ============================================
-- CORRECCIÓN: Actualizar máquinas con codigoQR NULL o TEMP
-- ============================================

-- Actualizar máquinas que tengan codigoQR NULL
UPDATE maquinas 
SET codigoQR = CONCAT(id_maquina, REPLACE(REPLACE(REPLACE(codigo_maquina, ' ', ''), '-', ''), '_', ''))
WHERE codigoQR IS NULL;

-- Actualizar máquinas que tengan codigoQR = 'TEMP'
UPDATE maquinas 
SET codigoQR = CONCAT(id_maquina, REPLACE(REPLACE(REPLACE(codigo_maquina, ' ', ''), '-', ''), '_', ''))
WHERE codigoQR = 'TEMP';

-- Actualizar máquinas que tengan codigoQR vacío
UPDATE maquinas 
SET codigoQR = CONCAT(id_maquina, REPLACE(REPLACE(REPLACE(codigo_maquina, ' ', ''), '-', ''), '_', ''))
WHERE codigoQR = '';

-- Verificar resultados
SELECT 
    id_maquina, 
    codigo_maquina, 
    codigoQR,
    CASE 
        WHEN codigoQR IS NULL THEN '❌ NULL'
        WHEN codigoQR = '' THEN '❌ Vacío'
        WHEN codigoQR = 'TEMP' THEN '❌ Temporal'
        ELSE '✅ OK'
    END as estado
FROM maquinas 
ORDER BY id_maquina;

-- ============================================
-- NOTAS:
-- ============================================
-- 1. Este script elimina los triggers que causan conflictos
-- 2. Actualiza todas las máquinas con códigos QR válidos
-- 3. El código QR se genera ahora en PHP, no en triggers
-- 4. Formato: ID + codigo_maquina (sin espacios, guiones ni guiones bajos)
-- 5. Ejecutar este script si tienes problemas al crear máquinas
