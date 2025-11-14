<?php
session_start();
require_once '../../config/db.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuarioingresando'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// // Solo administradores y técnicos pueden buscar máquinas
// if ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico') {
//     echo json_encode(['success' => false, 'message' => 'No autorizado']);
//     exit();
// }

// Obtener el código QR del POST
$data = json_decode(file_get_contents('php://input'), true);
$codigoQR = isset($data['codigoQR']) ? mysqli_real_escape_string($conexion, trim($data['codigoQR'])) : '';

if (empty($codigoQR)) {
    echo json_encode(['success' => false, 'message' => 'Código QR vacío']);
    exit();
}

// Buscar la máquina por código QR
$sql = "SELECT 
            m.id_maquina,
            m.codigo_maquina,
            m.marca,
            m.modelo,
            m.codigoQR,
            p.nombre_planta,
            l.nombre_linea,
            m.estado
        FROM maquinas m
        INNER JOIN plantas p ON m.id_planta = p.id_planta
        INNER JOIN lineas l ON m.id_linea = l.id_linea
        WHERE m.codigoQR = '$codigoQR'";

$resultado = mysqli_query($conexion, $sql);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $maquina = mysqli_fetch_assoc($resultado);
    echo json_encode([
        'success' => true,
        'maquina' => $maquina
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No se encontró ninguna máquina con ese código QR'
    ]);
}

mysqli_close($conexion);
?>
