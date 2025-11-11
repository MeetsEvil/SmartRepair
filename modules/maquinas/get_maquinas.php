<?php
session_start();
require_once '../../config/db.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuarioingresando'])) {
    echo json_encode([]);
    exit();
}

// Solo administradores y técnicos pueden ver máquinas
// if ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico') {
//     echo json_encode([]);
//     exit();
// }

// Verificar conexión
if (!$conexion) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}

// Verificar si se solicitan máquinas inactivas
$mostrar_inactivas = isset($_GET['inactivas']) && $_GET['inactivas'] == '1';

// Consulta para obtener máquinas con información de planta y línea
$sql = "SELECT 
            m.id_maquina,
            m.codigo_maquina,
            m.marca,
            m.modelo,
            m.numero_serie,
            p.nombre_planta as planta,
            l.nombre_linea as linea,
            m.area,
            m.fecha_instalacion,
            m.estado,
            m.observaciones
        FROM maquinas m
        INNER JOIN plantas p ON m.id_planta = p.id_planta
        INNER JOIN lineas l ON m.id_linea = l.id_linea";

// Filtrar por estado
if (!$mostrar_inactivas) {
    $sql .= " WHERE m.estado = 'Activa'";
}

$sql .= " ORDER BY m.id_maquina ASC";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conexion)]);
    exit();
}

$maquinas = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $id = $row['id_maquina'];
    $estado = $row['estado'];
    $rol = $_SESSION['rol'];
    
    // Botones de acción según el rol
    if ($rol == 'Administrador' || $rol == 'Técnico') {
        // Administradores y Técnicos: Ver, Editar y Cambiar Estado
        $row['acciones'] = '
            <div style="display: flex; gap: 5px; justify-content: center;">
                <a href="ver_maquinas.php?id=' . $id . '" 
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
                <a href="editar_maquinas.php?id=' . $id . '" 
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
        // Operarios: solo ver
        $row['acciones'] = '
            <div style="display: flex; gap: 5px; justify-content: center;">
                <a href="ver_maquinas.php?id=' . $id . '" 
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
    
    $maquinas[] = $row;
}

// Cerrar conexión
mysqli_close($conexion);

// Devolver JSON
header('Content-Type: application/json');
echo json_encode($maquinas);
