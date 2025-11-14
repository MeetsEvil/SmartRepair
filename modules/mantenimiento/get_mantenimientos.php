<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuarioingresando']) || ($_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Técnico')) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once '../../config/db.php';

$query = "SELECT 
            m.id_mantenimiento,
            m.fecha_mantenimiento,
            maq.codigo_maquina,
            l.nombre_linea,
            tm.nombre_tipo,
            CONCAT(u.nombre, ' ', u.apellido) as tecnico_responsable
          FROM mantenimientos m
          INNER JOIN maquinas maq ON m.id_maquina = maq.id_maquina
          INNER JOIN lineas l ON maq.id_linea = l.id_linea
          INNER JOIN tipos_mantenimiento tm ON m.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
          INNER JOIN usuarios u ON m.id_tecnico_responsable = u.id_usuario
          ORDER BY m.fecha_mantenimiento DESC";

$result = mysqli_query($conexion, $query);

if ($result) {
    $mantenimientos = array();
    $rol = $_SESSION['rol'];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id_mantenimiento'];
        
        // Botones de acción según el rol
        if ($rol == 'Administrador') {
            $row['acciones'] = '
                <div style="display: flex; gap: 5px; justify-content: center;">
                    <a href="ver_mantenimiento.php?id=' . $id . '" 
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
                    <a href="editar_mantenimiento.php?id=' . $id . '" 
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
                </div>
            ';
        } else {
            // Solo técnicos: solo ver
            $row['acciones'] = '
                <div style="display: flex; gap: 5px; justify-content: center;">
                    <a href="ver_mantenimiento.php?id=' . $id . '" 
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
        
        $mantenimientos[] = $row;
    }
    echo json_encode($mantenimientos);
} else {
    echo json_encode(['error' => 'Error en la base de datos: ' . mysqli_error($conexion)]);
}
?>
