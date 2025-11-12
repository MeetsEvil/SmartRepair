# Configuración de Imágenes de Máquinas

## Estructura de Carpetas

Crea la siguiente estructura en la raíz del proyecto:

```
proyecto/
├── imgMaquinas/
│   ├── no-maquina.png (imagen por defecto)
│   ├── 1.png
│   ├── 2.png
│   ├── 3.png
│   └── ...
```

## Pasos para Configurar

### 1. Crear la carpeta de imágenes

Crea una carpeta llamada `imgMaquinas` en la raíz del proyecto (al mismo nivel que `modules`, `assets`, etc.)

### 2. Agregar imagen por defecto

Coloca una imagen llamada `no-maquina.png` en la carpeta `imgMaquinas`. Esta imagen se mostrará cuando una máquina no tenga foto asignada.

### 3. Agregar imágenes de máquinas

Para cada máquina, coloca una imagen con el nombre del ID de la máquina:
- Máquina con ID 1: `1.png`
- Máquina con ID 2: `2.png`
- Y así sucesivamente...

### 4. Actualizar la base de datos

Si ya tienes máquinas creadas, ejecuta el script SQL:

```sql
-- Ejecutar en tu gestor de base de datos
source database/update_maquinas_imagen.sql;
```

O ejecuta manualmente:

```sql
ALTER TABLE maquinas 
ADD COLUMN IF NOT EXISTS imagen VARCHAR(255) DEFAULT 'imgMaquinas/no-maquina.png' 
COMMENT 'Ruta de la imagen de la máquina'
AFTER area;

UPDATE maquinas 
SET imagen = CONCAT('imgMaquinas/', id_maquina, '.png');
```

## Especificaciones de Imágenes

### Formatos Aceptados
- PNG (recomendado)
- JPG / JPEG
- GIF

### Tamaño Recomendado
- Ancho: 800-1200px
- Alto: 600-900px
- Peso máximo: 2MB

### Relación de Aspecto
- Recomendado: 4:3 o 16:9
- La imagen se ajustará automáticamente al contenedor

## Funcionamiento

### En ver_maquinas.php

Cuando visualizas una máquina:
1. El sistema busca la imagen en la ruta especificada en la base de datos
2. Si la imagen no existe, muestra automáticamente `no-maquina.png`
3. La imagen se muestra en un contenedor con borde rojo y sombra

### Valor por Defecto

Cuando creas una nueva máquina sin especificar imagen:
- Se asigna automáticamente: `imgMaquinas/no-maquina.png`

Cuando creas una máquina y especificas el ID:
- Puedes asignar: `imgMaquinas/[ID].png`

## Ejemplo de Uso

### Base de Datos

```sql
-- Máquina con imagen personalizada
INSERT INTO maquinas (..., imagen) 
VALUES (..., 'imgMaquinas/1.png');

-- Máquina con imagen por defecto (automático)
INSERT INTO maquinas (...) 
VALUES (...);
-- imagen será: 'imgMaquinas/no-maquina.png'
```

### Estructura de Archivos

```
imgMaquinas/
├── no-maquina.png          (400 KB)
├── 1.png                   (1.2 MB) - Máquina Engel e-victory 200
├── 2.png                   (980 KB) - Máquina Engel e-victory 200
├── 3.png                   (1.5 MB) - Máquina Haitian Mars 250
└── ...
```

## Funcionalidad de Subida de Imágenes

### Crear Nueva Máquina

Al crear una nueva máquina:
1. Completa el formulario con los datos de la máquina
2. Selecciona una imagen en el campo "Fotografía"
3. Verás una previsualización de la imagen
4. Al guardar:
   - Se crea el registro de la máquina
   - Se obtiene el ID generado
   - Se guarda la imagen con el nombre del ID (ej: `5.jpg`)
   - Se actualiza la base de datos con la ruta de la imagen

### Validaciones

El sistema valida:
- **Formato**: Solo PNG, JPG, JPEG, GIF
- **Tamaño**: Máximo 5MB
- **Previsualización**: Se muestra antes de guardar

### Proceso Automático

```
1. Usuario sube imagen → Validación
2. Se crea máquina → Se obtiene ID (ej: 15)
3. Se guarda imagen → imgMaquinas/15.jpg
4. Se actualiza BD → imagen = 'imgMaquinas/15.jpg'
```

## Próximas Funcionalidades

En futuras actualizaciones se agregará:
- Edición de imágenes desde el formulario de editar máquinas
- Recorte y redimensionamiento automático
- Galería de imágenes múltiples por máquina
- Eliminación de imagen anterior al actualizar

## Solución de Problemas

### La imagen no se muestra

1. Verifica que la carpeta `imgMaquinas` existe en la raíz
2. Verifica que el archivo tiene el nombre correcto
3. Verifica los permisos de lectura de la carpeta
4. Revisa la consola del navegador para errores 404

### La imagen se ve distorsionada

- Usa imágenes con relación de aspecto 4:3 o 16:9
- El sistema ajusta automáticamente el tamaño manteniendo proporciones

### Error al cargar imagen

- Verifica que la ruta en la base de datos sea correcta
- El sistema tiene fallback automático a `no-maquina.png`
