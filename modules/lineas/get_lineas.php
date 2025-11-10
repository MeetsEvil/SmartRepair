<?php
session_start();
require_once '../../config/db.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuarioingresando'])) {
    echo json_encode([]);
    exit();
}

// Solo administradores y técnicos pueden ver líneas
if ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico') {
    echo json_encode([]);
    exit();
}

// Verificar conexión
if (!$conexion) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}

// Verificar si se solicitan líneas inactivas
$mostrar_inactivas = isset($_GET['inactivas']) && $_GET['inactivas'] == '1';

// Consulta para obtener líneas con información de planta y prioridad
$sql = "SELECT 
            l.id_linea,
            l.nombre_linea,
            p.nombre_planta as planta,
            pr.nombre_prioridad as prioridad,
            l.estado,
            l.descripcion,
            l.created_at,
            (SELECT COUNT(*) FROM maquinas m WHERE m.id_linea = l.id_linea) as total_maquinas
        FROM lineas l
        INNER JOIN plantas p ON l.id_planta = p.id_planta
        INNER JOIN prioridades pr ON l.id_prioridad = pr.id_prioridad";

// Filtrar por estado
if (!$mostrar_inactivas) {
    $sql .= " WHERE l.estado = 'Activa'";
}

$sql .= " ORDER BY l.id_linea ASC";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conexion)]);
    exit();
}

$lineas = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $id = $row['id_linea'];
    $estado = $row['estado'];
    $rol = $_SESSION['rol'];
    
    // Botones de acción según el rol
    if ($rol == 'Administrador') {
        $row['acciones'] = '
            <div style="display: flex; gap: 5px; justify-content: center;">
                <a href="ver_lineas.php?id=' . $id . '" 
                style="background: linear-gradient(90deg, #2196F3, #0D47A1); 
                        color: white; 
                        padding: 6px 12px; 
                        border-radius: 5px; 
                        text-decoration: none; 
                        font-size: 0.9em;
                        font-weight: 700;
                        display: inline-flex;
                        align-items: center;
                        gap: 3px;">
                    <ion-icon name="eye-outline"></ion-icon> Ver
                </a>
                <a href="editar_lineas.php?id=' . $id . '" 
                style="background: linear-gradient(90deg, #FF9800, #E65100); 
                        color: white; 
                        padding: 6px 12px; 
                        border-radius: 5px; 
                        text-decoration: none; 
                        font-size: 0.9em;
                        font-weight: 700;
                        display: inline-flex;
                        align-items: center;
                        gap: 3px;">
                    <ion-icon name="create-outline"></ion-icon> Editar
                </a>
                <a href="#" 
                onclick="confirmarCambioEstado(' . $id . ', \'' . $estado . '\'); return false;"
                style="background: linear-gradient(90deg, ' . ($estado == 'Activa' ? '#F44336, #B71C1C' : '#4CAF50, #2E7D32') . '); 
                        color: white; 
                        padding: 6px 12px; 
                        border-radius: 5px; 
                        text-decoration: none; 
                        font-size: 0.9em;
                        font-weight: 700;
                        display: inline-flex;
                        align-items: center;
                        gap: 3px;">
                    <ion-icon name="' . ($estado == 'Activa' ? 'close-circle-outline' : 'checkmark-circle-outline') . '"></ion-icon> 
                    ' . ($estado == 'Activa' ? 'Desactivar' : 'Activar') . '
                </a>
            </div>
        ';
    } else {
        // Solo técnicos: solo ver
        $row['acciones'] = '
            <div style="display: flex; gap: 5px; justify-content: center;">
                <a href="ver_lineas.php?id=' . $id . '" 
                style="background: linear-gradient(90deg, #2196F3, #0D47A1); 
                        color: white; 
                        padding: 6px 12px; 
                        border-radius: 5px; 
                        text-decoration: none; 
                        font-size: 0.9em;
                        font-weight: 700;
                        display: inline-flex;
                        align-items: center;
                        gap: 3px;">
                    <ion-icon name="eye-outline"></ion-icon> Ver
                </a>
            </div>
        ';
    }
    
    $lineas[] = $row;
}

// Cerrar conexión
mysqli_close($conexion);

// Devolver JSON
header('Content-Type: application/json');
echo json_encode($lineas);
