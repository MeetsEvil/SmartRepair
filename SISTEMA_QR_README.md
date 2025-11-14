# Sistema de Códigos QR para Máquinas

## Descripción

Sistema de identificación y búsqueda de máquinas mediante códigos QR. Cada máquina tiene un código QR único que permite su identificación rápida mediante escaneo con cámara.

## Estructura del Código QR

### Formato
```
ID + codigo_maquina
```

### Ejemplos
- Máquina ID 3 con código "MAQ-INY-005" → QR: `3MAQ-INY-005`
- Máquina ID 15 con código "MAQ-SCR-001" → QR: `15MAQ-SCR-001`
- Máquina ID 7 con código "MAQ-ELE-010" → QR: `7MAQ-ELE-010`

### Características
- ✅ **Único**: Cada máquina tiene un código QR diferente
- ✅ **Inmutable**: No cambia aunque se edite el código de la máquina
- ✅ **Identificable**: Combina ID (único en BD) + código de máquina

## Base de Datos

### Campo Agregado
```sql
codigoQR VARCHAR(100) COMMENT 'Código QR único: ID + código de máquina'
```

### Índice
```sql
INDEX idx_codigoqr (codigoQR)
```

### Generación Automática
El código QR se genera automáticamente al crear una máquina:
```sql
codigoQR = CONCAT(id_maquina, codigo_maquina)
```

## Funcionalidades Implementadas

### 1. Generación Automática
- ✅ Al crear una máquina, se genera su código QR automáticamente
- ✅ Formato: ID + codigo_maquina (sin espacios)
- ✅ Se almacena en la base de datos

### 2. Búsqueda por QR
- ✅ Botón "Buscar por QR" en index_maquinas.php
- ✅ Abre modal con cámara para escanear
- ✅ Detecta código QR automáticamente
- ✅ Busca máquina en base de datos
- ✅ Redirige a ver_maquinas.php si encuentra la máquina

### 3. Actualización de Máquinas Existentes
- ✅ Script SQL para agregar códigos QR a máquinas existentes
- ✅ Triggers para generación automática en nuevas máquinas

## Archivos Creados/Modificados

### Base de Datos
- `database/schema.sql` - Campo codigoQR agregado
- `database/update_codigoQR.sql` - Script de actualización

### Backend
- `modules/maquinas/buscar_por_qr.php` - API para buscar por QR
- `modules/maquinas/procesar_crear_maquina.php` - Genera código QR al crear

### Frontend
- `modules/maquinas/index_maquinas.php` - Botón y modal de escáner

### Referencia
- `QR/escanearQR.html` - Ejemplo de escáner
- `QR/generarQR.html` - Ejemplo de generador

## Uso del Sistema

### Para Usuarios

#### 1. Buscar Máquina por QR
1. Ir a "Máquinas" en el menú
2. Click en botón "Buscar por QR" (azul)
3. Permitir acceso a la cámara
4. Apuntar cámara hacia el código QR
5. El sistema detecta automáticamente y redirige

#### 2. Permisos Requeridos
- Solo **Administradores** y **Técnicos** pueden usar el escáner
- Se requiere permiso de cámara en el navegador
- Funciona mejor en HTTPS o localhost

### Para Desarrolladores

#### Actualizar Base de Datos Existente
```sql
-- Ejecutar en MySQL
source database/update_codigoQR.sql;
```

O manualmente:
```sql
-- Agregar columna
ALTER TABLE maquinas 
ADD COLUMN codigoQR VARCHAR(100) 
AFTER imagen;

-- Generar códigos para máquinas existentes
UPDATE maquinas 
SET codigoQR = CONCAT(id_maquina, codigo_maquina);
```

#### Buscar Máquina por Código
```sql
SELECT * FROM maquinas WHERE codigoQR = '3MAQ-INY-005';
```

#### API de Búsqueda
```javascript
// POST a buscar_por_qr.php
fetch('buscar_por_qr.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ codigoQR: '3MAQ-INY-005' })
})
.then(res => res.json())
.then(data => {
    if (data.success) {
        console.log('Máquina encontrada:', data.maquina);
    }
});
```

## Tecnologías Utilizadas

### Librerías
- **jsQR** (v1.4.0) - Decodificación de códigos QR
- CDN: `https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js`

### APIs del Navegador
- **MediaDevices API** - Acceso a la cámara
- **getUserMedia** - Captura de video
- **Canvas API** - Procesamiento de imágenes

## Flujo de Funcionamiento

### Crear Máquina
```
1. Usuario crea máquina
   ↓
2. Se inserta en BD → ID: 15
   ↓
3. Se genera codigoQR: "15" + "MAQ-INY-005"
   ↓
4. Se actualiza BD: codigoQR = "15MAQ-INY-005"
```

### Buscar por QR
```
1. Usuario click "Buscar por QR"
   ↓
2. Se abre modal con cámara
   ↓
3. Usuario apunta a código QR
   ↓
4. jsQR detecta: "15MAQ-INY-005"
   ↓
5. Se busca en BD: WHERE codigoQR = "15MAQ-INY-005"
   ↓
6. Si existe → Redirige a ver_maquinas.php?id=15
   ↓
7. Si no existe → Muestra error y reinicia escaneo
```

## ✅ Visualización y Descarga de QR

### En ver_maquinas.php
- ✅ Se muestra el código QR generado automáticamente
- ✅ Aparece al lado de la fotografía de la máquina
- ✅ Muestra el texto del código QR
- ✅ Botón para descargar QR en formato PNG
- ✅ QR personalizado con colores del sistema (#932323)

### Características del QR Generado
- **Tamaño**: 200x200 píxeles
- **Color**: Rojo (#932323) sobre blanco
- **Nivel de corrección**: Alto (H)
- **Formato de descarga**: PNG
- **Nombre de archivo**: QR-[codigo_maquina].png

## Próximas Funcionalidades

### Mejoras de QR
- Imprimir QR directamente desde el navegador
- Generar QR en lote para todas las máquinas
- Diferentes tamaños de QR para impresión
- Incluir logo de la empresa en el centro del QR

### Mejoras del Escáner
- Soporte para subir imagen de QR
- Historial de QR escaneados
- Estadísticas de uso
- Modo offline con caché

### Integración
- Escanear QR desde tickets
- Escanear QR desde mantenimientos
- Vincular QR con ubicación física
- Generar reportes por QR

## Solución de Problemas

### La cámara no se activa
1. Verificar permisos del navegador
2. Usar HTTPS o localhost
3. Verificar que el dispositivo tenga cámara
4. Probar en otro navegador

### No detecta el código QR
1. Asegurar buena iluminación
2. Mantener cámara estable
3. Acercar/alejar para mejor enfoque
4. Verificar que el QR esté completo en pantalla

### Error "No se encontró máquina"
1. Verificar que el código QR sea correcto
2. Verificar que la máquina exista en BD
3. Ejecutar script de actualización de códigos QR
4. Revisar logs del servidor

### Código QR no se generó
1. Verificar que se ejecutó update_codigoQR.sql
2. Eliminar triggers conflictivos:
```sql
source database/fix_triggers_qr.sql;
```
3. Crear máquina nueva para probar
4. Regenerar manualmente:
```sql
UPDATE maquinas 
SET codigoQR = CONCAT(id_maquina, REPLACE(REPLACE(REPLACE(codigo_maquina, ' ', ''), '-', ''), '_', ''))
WHERE id_maquina = X;
```

### Error al crear máquina (codigoQR NULL)
1. Ejecutar script de corrección:
```sql
source database/fix_triggers_qr.sql;
```
2. Verificar que no existan triggers:
```sql
SHOW TRIGGERS WHERE `Table` = 'maquinas';
```
3. Si existen, eliminarlos:
```sql
DROP TRIGGER IF EXISTS before_insert_maquina_qr;
DROP TRIGGER IF EXISTS after_insert_maquina_qr;
```

## Seguridad

### Validaciones
- ✅ Autenticación requerida
- ✅ Solo Admin y Técnico pueden escanear
- ✅ Validación de código QR en backend
- ✅ Escape de SQL injection

### Permisos
- Cámara: Solo cuando se abre el escáner
- Se detiene automáticamente al cerrar modal
- No se graban videos ni imágenes

## Notas Técnicas

### Compatibilidad
- Chrome/Edge: ✅ Completo
- Firefox: ✅ Completo
- Safari: ✅ Requiere HTTPS
- Mobile: ✅ Funciona en iOS y Android

### Rendimiento
- Escaneo en tiempo real (30-60 FPS)
- Detección automática sin botón
- Bajo consumo de recursos
- No requiere conexión constante (solo para buscar)

### Limitaciones
- Requiere HTTPS en producción (excepto localhost)
- Necesita permisos de cámara
- Funciona mejor con buena iluminación
- QR debe estar completo y visible
