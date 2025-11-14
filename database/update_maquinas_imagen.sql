-- ============================================
-- ACTUALIZACIÓN: Agregar campo imagen a máquinas existentes
-- ============================================

USE mattel_mantenimiento;

-- Agregar columna imagen si no existe (para bases de datos ya creadas)
ALTER TABLE maquinas 
ADD COLUMN IF NOT EXISTS imagen VARCHAR(255) DEFAULT 'imgMaquinas/no-maquina.png' 
COMMENT 'Ruta de la imagen de la máquina'
AFTER area;

-- Actualizar máquinas existentes con imagen por defecto
UPDATE maquinas 
SET imagen = 'imgMaquinas/no-maquina.png' 
WHERE imagen IS NULL OR imagen = '';

-- Actualizar máquinas con imágenes específicas según su ID (ejemplo)
-- Puedes personalizar estas rutas según las imágenes que tengas

UPDATE maquinas SET imagen = 'imgMaquinas/1.png' WHERE id_maquina = 1;
UPDATE maquinas SET imagen = 'imgMaquinas/2.png' WHERE id_maquina = 2;
UPDATE maquinas SET imagen = 'imgMaquinas/3.png' WHERE id_maquina = 3;
UPDATE maquinas SET imagen = 'imgMaquinas/4.png' WHERE id_maquina = 4;
UPDATE maquinas SET imagen = 'imgMaquinas/5.png' WHERE id_maquina = 5;
UPDATE maquinas SET imagen = 'imgMaquinas/6.png' WHERE id_maquina = 6;
UPDATE maquinas SET imagen = 'imgMaquinas/7.png' WHERE id_maquina = 7;
UPDATE maquinas SET imagen = 'imgMaquinas/8.png' WHERE id_maquina = 8;
UPDATE maquinas SET imagen = 'imgMaquinas/9.png' WHERE id_maquina = 9;
UPDATE maquinas SET imagen = 'imgMaquinas/10.png' WHERE id_maquina = 10;
UPDATE maquinas SET imagen = 'imgMaquinas/11.png' WHERE id_maquina = 11;
UPDATE maquinas SET imagen = 'imgMaquinas/12.png' WHERE id_maquina = 12;
UPDATE maquinas SET imagen = 'imgMaquinas/13.png' WHERE id_maquina = 13;
UPDATE maquinas SET imagen = 'imgMaquinas/14.png' WHERE id_maquina = 14;

-- Verificar las actualizaciones
SELECT id_maquina, codigo_maquina, marca, modelo, imagen 
FROM maquinas 
ORDER BY id_maquina;

-- ============================================
-- NOTAS:
-- ============================================
-- 1. Asegúrate de crear la carpeta 'imgMaquinas' en la raíz del proyecto
-- 2. Coloca las imágenes con el nombre del ID de la máquina (1.png, 2.png, etc.)
-- 3. Coloca una imagen por defecto llamada 'no-maquina.png' para máquinas sin foto
-- 4. Formatos recomendados: PNG, JPG, JPEG
-- 5. Tamaño recomendado: máximo 2MB por imagen
