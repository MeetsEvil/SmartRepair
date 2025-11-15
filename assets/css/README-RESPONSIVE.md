# Guía de Estilos Responsive

## Archivos CSS Creados

### 1. `sidebar.css` (Actualizado)
Contiene los estilos base del sistema y responsive para:
- Navegación lateral (sidebar)
- Contenedores principales
- Topbar y user box
- Modales
- Botones generales
- **Breakpoints**: 991px, 768px, 480px

### 2. `style.css` (Actualizado)
Estilos para la página de login con responsive completo:
- Formulario de login adaptable
- Imágenes de fondo responsive
- **Breakpoints**: 991px, 768px, 480px, 360px

### 3. `dashboard.css` (Nuevo)
Estilos específicos para el dashboard:
- Cards de estadísticas con gradientes
- Grid responsive de 4 columnas
- Efectos hover y animaciones
- **Breakpoints**: 1200px, 991px, 768px, 480px

### 4. `responsive-modules.css` (Nuevo)
Estilos para módulos de máquinas y tickets:
- Contenedores de formularios
- Grids de información
- Imágenes de máquinas
- Códigos QR
- Badges de prioridad y estado
- Botones de acción
- **Breakpoints**: 991px, 768px, 480px

### 5. `responsive-tables.css` (Nuevo)
Estilos responsive para DataTables:
- Tablas con scroll horizontal
- Botones de acción en tablas
- Controles de DataTables
- Paginación responsive
- **Breakpoints**: 991px, 768px, 480px

## Cómo Incluir los Archivos CSS

### En archivos PHP con sidebar (dashboard, usuarios, máquinas, tickets):

```php
<head>
    <!-- CSS Base -->
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    
    <!-- CSS Dashboard (solo para dashboard.php) -->
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    
    <!-- CSS Módulos (para máquinas y tickets) -->
    <link rel="stylesheet" href="../../assets/css/responsive-modules.css">
    
    <!-- CSS Tablas (para páginas con DataTables) -->
    <link rel="stylesheet" href="../../assets/css/responsive-tables.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
```

### En la página de login:

```php
<head>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
```

## Breakpoints Utilizados

### Desktop Grande (> 1200px)
- Sidebar: 300px
- Dashboard: 4 columnas
- Formularios: 2 columnas

### Desktop (992px - 1199px)
- Sidebar: 300px
- Dashboard: 2 columnas
- Formularios: 2 columnas

### Tablet (768px - 991px)
- Sidebar: Oculto por defecto, overlay cuando activo
- Dashboard: 2 columnas
- Formularios: 1 columna
- Tablas: Scroll horizontal

### Mobile (481px - 767px)
- Sidebar: Fullscreen overlay
- Dashboard: 1 columna
- Formularios: 1 columna apilada
- Botones: Ancho completo
- Tablas: Scroll horizontal con columnas ocultas

### Mobile Pequeño (< 480px)
- Todo optimizado para pantallas pequeñas
- Fuentes reducidas
- Padding/margin reducidos
- Botones más compactos

## Características Responsive Implementadas

### Navegación
- ✅ Sidebar colapsable en desktop
- ✅ Sidebar overlay en tablet/mobile
- ✅ Toggle button adaptable
- ✅ Logo responsive

### Dashboard
- ✅ Cards con grid responsive (4→2→1 columnas)
- ✅ Gradientes de colores
- ✅ Efectos hover
- ✅ Iconos y números escalables

### Formularios
- ✅ Grid de 2 columnas → 1 columna en mobile
- ✅ Inputs con ancho completo en mobile
- ✅ Botones apilados verticalmente en mobile
- ✅ Labels alineados correctamente

### Tablas
- ✅ Scroll horizontal en mobile
- ✅ Columnas menos importantes ocultas en mobile
- ✅ Botones de acción adaptables
- ✅ Controles de DataTables responsive

### Imágenes
- ✅ Imágenes de máquinas escalables
- ✅ Códigos QR responsive
- ✅ Preview de imágenes adaptable

### Modales
- ✅ Ancho adaptable (95% en mobile)
- ✅ Botones apilados en mobile
- ✅ Contenido scrollable

## Clases CSS Útiles

### Contenedores
- `.usuarios-container` - Contenedor para módulo de usuarios
- `.maquinas-container` - Contenedor para módulo de máquinas
- `.tickets-container` - Contenedor para módulo de tickets
- `.form-container` - Contenedor para formularios
- `.view-container` - Contenedor para vistas de detalle

### Grids
- `.form-grid` - Grid de 2 columnas para formularios
- `.info-grid` - Grid de 2 columnas para información
- `.cardBox` - Grid para cards del dashboard

### Botones
- `.btn-new` - Botón nuevo (gradiente rojo)
- `.btn-submit` - Botón enviar (gradiente rojo)
- `.btn-cancel` - Botón cancelar (gris)
- `.btn-action` - Botón de acción general
- `.btn-regresar` - Botón regresar
- `.exportar` - Botón exportar (gradiente azul)

### Badges
- `.priority-badge` - Badge de prioridad
- `.priority-alta` - Prioridad alta (rojo)
- `.priority-media` - Prioridad media (amarillo)
- `.priority-baja` - Prioridad baja (verde)
- `.status-badge` - Badge de estado
- `.status-pendiente` - Estado pendiente (amarillo)
- `.status-en-proceso` - Estado en proceso (azul)
- `.status-completado` - Estado completado (verde)

## Testing Responsive

### Herramientas Recomendadas
1. Chrome DevTools (F12 → Toggle Device Toolbar)
2. Firefox Responsive Design Mode
3. Dispositivos reales para testing final

### Dispositivos a Probar
- Desktop: 1920x1080, 1366x768
- Tablet: iPad (768x1024), iPad Pro (1024x1366)
- Mobile: iPhone SE (375x667), iPhone 12 (390x844), Samsung Galaxy (360x740)

## Notas Importantes

1. **Orden de Carga**: Cargar `sidebar.css` primero, luego los específicos
2. **!important**: Usado solo cuando necesario para sobrescribir estilos de librerías
3. **Flexbox y Grid**: Usados para layouts modernos y responsive
4. **Transiciones**: Todas las animaciones tienen `transition` para suavidad
5. **Touch**: `-webkit-overflow-scrolling: touch` para scroll suave en iOS

## Mantenimiento

Para agregar nuevos estilos responsive:
1. Identificar el breakpoint apropiado
2. Agregar estilos en el archivo CSS correspondiente
3. Probar en múltiples dispositivos
4. Documentar cambios importantes
