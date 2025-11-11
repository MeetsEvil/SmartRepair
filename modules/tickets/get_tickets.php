<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuarioingresando'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once '../../config/db.php';

// Query para obtener todos los tickets activos con información relacionada
$query = "SELECT 
            t.id_ticket,
            t.codigo_ticket,
            t.descripcion_falla as descripcion,
            pr.nombre_prioridad as prioridad,
            t.id_estado,
            t.fecha_asignacion as fecha_inicio_progreso,
            t.fecha_resolucion as fecha_inicio_validacion,
            t.fecha_cierre as fecha_finalizacion,
            m.codigo_maquina,
            l.nombre_linea as linea,
            p.nombre_planta as planta,
            CONCAT(u.nombre, ' ', u.apellido) as responsable,
            e.nombre_estado
          FROM tickets t
          INNER JOIN maquinas m ON t.id_maquina = m.id_maquina
          INNER JOIN lineas l ON m.id_linea = l.id_linea
          INNER JOIN plantas p ON l.id_planta = p.id_planta
          INNER JOIN prioridades pr ON t.id_prioridad = pr.id_prioridad
          LEFT JOIN usuarios u ON t.id_tecnico_responsable = u.id_usuario
          INNER JOIN estados_ticket e ON t.id_estado = e.id_estado
          ORDER BY 
            pr.nivel ASC,
            t.fecha_creacion DESC";

$result = mysqli_query($conexion, $query);

if ($result) {
    $tickets = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $tickets[] = $row;
    }
    echo json_encode($tickets);
} else {
    echo json_encode(['error' => 'Error en la base de datos: ' . mysqli_error($conexion)]);
}
?>
