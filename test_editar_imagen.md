# Prueba de Edición de Imágenes - Máquinas

## Escenario de Prueba

### Caso 1: Cambiar de PNG a JPG

**Estado Inicial:**
- Máquina ID: 5
- Imagen actual: `imgMaquinas/5.png`
- Base de datos: `imagen = 'imgMaquinas/5.png'`

**Acción:**
1. Ir a editar máquina ID 5
2. Subir una nueva imagen en formato JPG
3. Guardar cambios

**Resultado Esperado:**
- ❌ Se elimina: `imgMaquinas/5.png`
- ✅ Se crea: `imgMaquinas/5.jpg`
- ✅ Base de datos actualizada: `imagen = 'imgMaquinas/5.jpg'`

---

### Caso 2: Cambiar de JPG a GIF

**Estado Inicial:**
- Máquina ID: 10
- Imagen actual: `imgMaquinas/10.jpg`
- Base de datos: `imagen = 'imgMaquinas/10.jpg'`

**Acción:**
1. Ir a editar máquina ID 10
2. Subir una nueva imagen en formato GIF
3. Guardar cambios

**Resultado Esperado:**
- ❌ Se elimina: `imgMaquinas/10.jpg`
- ✅ Se crea: `imgMaquinas/10.gif`
- ✅ Base de datos actualizada: `imagen = 'imgMaquinas/10.gif'`

---

### Caso 3: Editar sin cambiar imagen

**Estado Inicial:**
- Máquina ID: 7
- Imagen actual: `imgMaquinas/7.png`
- Base de datos: `imagen = 'imgMaquinas/7.png'`

**Acción:**
1. Ir a editar máquina ID 7
2. Cambiar otros datos (marca, modelo, etc.)
3. NO subir nueva imagen
4. Guardar cambios

**Resultado Esperado:**
- ✅ Se mantiene: `imgMaquinas/7.png`
- ✅ Base de datos sin cambios: `imagen = 'imgMaquinas/7.png'`
- ✅ Otros datos actualizados correctamente

---

### Caso 4: Múltiples extensiones anteriores

**Estado Inicial:**
- Máquina ID: 3
- Archivos existentes (por error):
  - `imgMaquinas/3.png`
  - `imgMaquinas/3.jpg`
  - `imgMaquinas/3.gif`
- Base de datos: `imagen = 'imgMaquinas/3.png'`

**Acción:**
1. Ir a editar máquina ID 3
2. Subir una nueva imagen en formato JPG
3. Guardar cambios

**Resultado Esperado:**
- ❌ Se eliminan TODAS: `3.png`, `3.jpg`, `3.gif`
- ✅ Se crea nueva: `imgMaquinas/3.jpg`
- ✅ Base de datos actualizada: `imagen = 'imgMaquinas/3.jpg'`

---

## Validaciones a Verificar

### ✅ Validación de Formato
- Intentar subir un archivo .txt → Debe rechazar
- Intentar subir un archivo .pdf → Debe rechazar
- Subir PNG, JPG, JPEG, GIF → Debe aceptar

### ✅ Validación de Tamaño
- Subir imagen de 6MB → Debe rechazar (máximo 5MB)
- Subir imagen de 3MB → Debe aceptar

### ✅ Previsualización
- Al seleccionar imagen → Debe mostrar previsualización
- Al cancelar selección → Debe ocultar previsualización

### ✅ Imagen Actual
- Debe mostrar la imagen actual antes de editar
- Si no hay imagen → Debe mostrar `no-maquina.png`

---

## Checklist de Pruebas

- [ ] Cambiar de PNG a JPG
- [ ] Cambiar de JPG a PNG
- [ ] Cambiar de PNG a GIF
- [ ] Editar sin cambiar imagen
- [ ] Validar rechazo de formato incorrecto
- [ ] Validar rechazo de tamaño excesivo
- [ ] Verificar previsualización funciona
- [ ] Verificar imagen actual se muestra
- [ ] Verificar eliminación de archivo anterior
- [ ] Verificar actualización en base de datos
- [ ] Verificar visualización en ver_maquinas.php

---

## Comandos Útiles para Verificar

### Ver archivos en carpeta imgMaquinas
```bash
# Linux/Mac
ls -la imgMaquinas/

# Windows (PowerShell)
Get-ChildItem imgMaquinas/
```

### Verificar base de datos
```sql
SELECT id_maquina, codigo_maquina, imagen 
FROM maquinas 
WHERE id_maquina IN (3, 5, 7, 10);
```

### Limpiar archivos duplicados (si es necesario)
```bash
# Linux/Mac
cd imgMaquinas/
ls -1 | sort | uniq -d

# Eliminar duplicados manualmente
rm 3.png 3.gif  # Mantener solo 3.jpg
```

---

## Notas Importantes

1. **Siempre se eliminan todas las extensiones posibles** antes de guardar la nueva imagen
2. **El nombre del archivo siempre es el ID de la máquina** + extensión
3. **La base de datos se actualiza automáticamente** con la nueva ruta
4. **Si no se sube imagen**, no se modifica el campo `imagen` en la BD
5. **La previsualización es solo visual**, no afecta hasta guardar

---

## Solución de Problemas

### La imagen no se actualiza
1. Verificar permisos de escritura en `imgMaquinas/`
2. Verificar que el archivo anterior se eliminó
3. Revisar logs de PHP para errores

### Se muestran múltiples versiones
1. Eliminar manualmente archivos duplicados
2. Editar la máquina y subir nueva imagen
3. El sistema limpiará automáticamente

### Error al subir imagen
1. Verificar tamaño del archivo (máximo 5MB)
2. Verificar formato (PNG, JPG, JPEG, GIF)
3. Verificar permisos de carpeta
