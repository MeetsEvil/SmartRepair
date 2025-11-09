# Base de Datos - Sistema de Mantenimiento MATTEL

## Estructura General

### Tablas de Catálogo (6)
- `plantas` - Plantas A, B, C, D, E
- `roles` - Administrador, Técnico, Operador
- `tipos_mantenimiento` - Preventivo, Correctivo, Predictivo, Otro
- `prioridades` - Crítica, Alta, Media, Baja
- `estados_ticket` - Pendiente, En progreso, En confirmación, Finalizado
- `tipos_falla` - Catálogo de fallas comunes por tipo de máquina

### Tablas Principales (6)
- `usuarios` - Usuarios del sistema con roles
- `lineas` - Líneas de producción por planta
- `maquinas` - Máquinas con código QR único
- `mantenimientos` - Historial de mantenimientos
- `tickets` - Sistema de tickets con flujo de estados
- `ticket_tecnicos` - Tabla auxiliar para múltiples técnicos (futuro)

## Relaciones Principales

```
plantas (1) -----> (N) lineas
plantas (1) -----> (N) maquinas
plantas (1) -----> (N) usuarios

lineas (1) -----> (N) maquinas

maquinas (1) -----> (N) mantenimientos
maquinas (1) -----> (N) tickets

usuarios (1) -----> (N) mantenimientos (como técnico)
usuarios (1) -----> (N) tickets (como reportante)
usuarios (1) -----> (N) tickets (como técnico responsable)

tipos_falla (1) -----> (N) tickets
prioridades (1) -----> (N) tickets
estados_ticket (1) -----> (N) tickets
```

## Características Especiales

### 1. Sistema de Semáforo (Rojo/Amarillo/Verde)
El color se calcula automáticamente mediante la vista `vista_estado_maquinas`:
- **ROJO**: Tiene tickets críticos (prioridad 1) pendientes o en progreso
- **AMARILLO**: Tiene tickets activos pero no críticos
- **VERDE**: No tiene tickets activos

### 2. Flujo de Tickets
```
1. PENDIENTE (Operador crea ticket)
   ↓
2. EN PROGRESO (Técnico se asigna)
   ↓ (Técnico documenta solución)
3. EN CONFIRMACIÓN (Técnico marca como resuelto)
   ↓ (Admin verifica)
4. FINALIZADO (Admin cierra ticket)
```

### 3. Timestamps Automáticos
Los triggers calculan automáticamente:
- `fecha_asignacion` - Cuando pasa a En progreso
- `fecha_resolucion` - Cuando pasa a En confirmación
- `fecha_cierre` - Cuando pasa a Finalizado
- `tiempo_respuesta` - Minutos desde creación hasta asignación
- `tiempo_resolucion` - Minutos desde asignación hasta resolución
- `tiempo_total` - Minutos desde creación hasta cierre

### 4. Código de Ticket Automático
Formato: `TKT-YYYYMMDD-NNNN`
Ejemplo: `TKT-20251108-0001`

### 5. Auditoría
Todas las tablas principales tienen:
- `created_at` - Fecha de creación
- `updated_at` - Fecha de última actualización
- `created_by` - Usuario que creó el registro (donde aplica)

## Vistas Útiles

### vista_estado_maquinas
Muestra el estado actual de cada máquina con:
- Tickets activos
- Último mantenimiento
- Color del semáforo calculado

### vista_resumen_tickets
Resumen completo de tickets con toda la información relacionada para reportes.

## Índices Creados

Se crearon índices en:
- Códigos únicos (codigo_maquina, codigo_ticket, usuario, email)
- Foreign keys (todas las relaciones)
- Campos de búsqueda frecuente (estado, fecha_creacion, prioridad)

## Instalación

```bash
# Opción 1: Desde línea de comandos
mysql -u root -p < database/schema.sql

# Opción 2: Desde phpMyAdmin
# Importar el archivo schema.sql

# Opción 3: Desde MySQL Workbench
# File > Run SQL Script > Seleccionar schema.sql
```

## Usuario por Defecto

```
Usuario: admin
Contraseña: admin123
Rol: Administrador
```

**IMPORTANTE**: Cambiar la contraseña en producción.

## Notas de Seguridad

1. El script usa MD5 para el usuario admin de ejemplo
2. En producción, usar `password_hash()` de PHP con BCRYPT
3. Cambiar credenciales por defecto
4. Configurar permisos de usuario MySQL apropiados
5. Habilitar SSL para conexiones remotas

## Próximos Pasos

1. Crear archivo de configuración `config/db.php`
2. Implementar sistema de autenticación
3. Crear módulos CRUD para cada tabla
4. Implementar generación de códigos QR
5. Desarrollar API REST para consultas móviles
