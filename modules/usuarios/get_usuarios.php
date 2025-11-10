<?php
session_start();
require_once '../../config/db.php';

// Verificar que el usuario esté logueado y sea administrador
if (!isset($_SESSION['usuarioingresando']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode([]);
    exit();
}

// Verificar conexión
if (!$conexion) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}

// Verificar si se solicitan usuarios inactivos
$mostrar_inactivos = isset($_GET['inactivos']) && $_GET['inactivos'] == '1';

// Consulta para obtener usuarios con información de rol y planta
$sql = "SELECT 
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.email,
            u.usuario,
            u.telefono,
            r.nombre_rol as rol,
            COALESCE(p.nombre_planta, 'Sin asignar') as planta,
            u.estado
        FROM usuarios u
        INNER JOIN roles r ON u.id_rol = r.id_rol
        LEFT JOIN plantas p ON u.id_planta = p.id_planta";

// Filtrar por estado
if (!$mostrar_inactivos) {
    $sql .= " WHERE u.estado = 'Activo'";
}

$sql .= " ORDER BY u.id_usuario ASC";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conexion)]);
    exit();
}

$usuarios = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $id = $row['id_usuario'];

    // Agregar botones de acción
    $row['acciones'] = '
        <div style="display: flex; gap: 5px; justify-content: center;">
            <a href="ver_usuarios.php?id=' . $id . '" 
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
            <a href="editar_usuarios.php?id=' . $id . '" 
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
            onclick="confirmarEliminar(' . $id . '); return false;"
            style="background: linear-gradient(90deg, #F44336, #B71C1C); 
                    color: white; 
                    padding: 6px 12px; 
                    border-radius: 5px; 
                    text-decoration: none; 
                    font-size: 0.9em;
                    font-weight: 700;
                    display: inline-flex;
                    align-items: center;
                    gap: 3px;">
                <ion-icon name="trash-outline"></ion-icon> Eliminar
            </a>
        </div>';
    

    $usuarios[] = $row;
}

// Cerrar conexión
mysqli_close($conexion);

// Devolver JSON
header('Content-Type: application/json');
echo json_encode($usuarios);
