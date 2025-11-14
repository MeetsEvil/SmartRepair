<?php
session_start();

if (!isset($_SESSION['usuarioingresando']) || ($_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Técnico')) {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar que el id_usuario esté en la sesión
    if (!isset($_SESSION['id_usuario'])) {
        $usuario = $_SESSION['usuarioingresando'];
        $query_usuario = "SELECT id_usuario FROM usuarios WHERE usuario = '$usuario'";
        $result_usuario = mysqli_query($conexion, $query_usuario);
        if ($result_usuario && mysqli_num_rows($result_usuario) > 0) {
            $row_usuario = mysqli_fetch_assoc($result_usuario);
            $_SESSION['id_usuario'] = $row_usuario['id_usuario'];
        }
    }

    $id_maquina = mysqli_real_escape_string($conexion, $_POST['id_maquina']);
    $id_tipo_mantenimiento = mysqli_real_escape_string($conexion, $_POST['id_tipo_mantenimiento']);
    $id_tecnico_responsable = mysqli_real_escape_string($conexion, $_POST['id_tecnico_responsable']);
    $fecha_mantenimiento = mysqli_real_escape_string($conexion, $_POST['fecha_mantenimiento']);
    $actividades_realizadas = mysqli_real_escape_string($conexion, trim($_POST['actividades_realizadas']));
    $repuestos_utilizados = !empty($_POST['repuestos_utilizados']) ? mysqli_real_escape_string($conexion, trim($_POST['repuestos_utilizados'])) : null;
    $observaciones = !empty($_POST['observaciones']) ? mysqli_real_escape_string($conexion, trim($_POST['observaciones'])) : null;
    $created_by = $_SESSION['id_usuario'];

    // Validaciones
    if (empty($id_maquina) || empty($id_tipo_mantenimiento) || empty($id_tecnico_responsable) || empty($fecha_mantenimiento) || empty($actividades_realizadas)) {
        $_SESSION['mensaje'] = 'Todos los campos obligatorios deben ser completados';
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: crear_mantenimiento.php");
        exit();
    }

    if (strlen($actividades_realizadas) < 10) {
        $_SESSION['mensaje'] = 'Las actividades realizadas deben tener al menos 10 caracteres';
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: crear_mantenimiento.php");
        exit();
    }

    // Construir query
    $query = "INSERT INTO mantenimientos (
                id_maquina, 
                id_tipo_mantenimiento, 
                id_tecnico_responsable, 
                fecha_mantenimiento, 
                actividades_realizadas, 
                repuestos_utilizados, 
                observaciones, 
                created_by
              ) VALUES (
                '$id_maquina', 
                '$id_tipo_mantenimiento', 
                '$id_tecnico_responsable', 
                '$fecha_mantenimiento', 
                '$actividades_realizadas', 
                " . ($repuestos_utilizados ? "'$repuestos_utilizados'" : "NULL") . ", 
                " . ($observaciones ? "'$observaciones'" : "NULL") . ", 
                '$created_by'
              )";

    if (mysqli_query($conexion, $query)) {
        $_SESSION['mensaje'] = 'Mantenimiento registrado correctamente';
        $_SESSION['tipo_mensaje'] = 'success';
        header("Location: index_mantenimiento.php");
    } else {
        $_SESSION['mensaje'] = 'Error al registrar el mantenimiento: ' . mysqli_error($conexion);
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: crear_mantenimiento.php");
    }
} else {
    header("Location: crear_mantenimiento.php");
}
exit();
?>
