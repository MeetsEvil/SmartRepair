<?php
session_start();

if (!isset($_SESSION['usuarioingresando']) || ($_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Técnico')) {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mantenimiento = mysqli_real_escape_string($conexion, $_POST['id_mantenimiento']);
    $id_maquina = mysqli_real_escape_string($conexion, $_POST['id_maquina']);
    $id_tipo_mantenimiento = mysqli_real_escape_string($conexion, $_POST['id_tipo_mantenimiento']);
    $id_tecnico_responsable = mysqli_real_escape_string($conexion, $_POST['id_tecnico_responsable']);
    $fecha_mantenimiento = mysqli_real_escape_string($conexion, $_POST['fecha_mantenimiento']);
    $actividades_realizadas = mysqli_real_escape_string($conexion, trim($_POST['actividades_realizadas']));
    $repuestos_utilizados = !empty($_POST['repuestos_utilizados']) ? mysqli_real_escape_string($conexion, trim($_POST['repuestos_utilizados'])) : null;
    $observaciones = !empty($_POST['observaciones']) ? mysqli_real_escape_string($conexion, trim($_POST['observaciones'])) : null;

    // Validaciones
    if (empty($id_maquina) || empty($id_tipo_mantenimiento) || empty($id_tecnico_responsable) || empty($fecha_mantenimiento) || empty($actividades_realizadas)) {
        $_SESSION['mensaje'] = 'Todos los campos obligatorios deben ser completados';
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: editar_mantenimiento.php?id=" . $id_mantenimiento);
        exit();
    }

    if (strlen($actividades_realizadas) < 10) {
        $_SESSION['mensaje'] = 'Las actividades realizadas deben tener al menos 10 caracteres';
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: editar_mantenimiento.php?id=" . $id_mantenimiento);
        exit();
    }

    // Construir query de actualización
    $query = "UPDATE mantenimientos SET 
                id_maquina = '$id_maquina', 
                id_tipo_mantenimiento = '$id_tipo_mantenimiento', 
                id_tecnico_responsable = '$id_tecnico_responsable', 
                fecha_mantenimiento = '$fecha_mantenimiento', 
                actividades_realizadas = '$actividades_realizadas', 
                repuestos_utilizados = " . ($repuestos_utilizados ? "'$repuestos_utilizados'" : "NULL") . ", 
                observaciones = " . ($observaciones ? "'$observaciones'" : "NULL") . "
              WHERE id_mantenimiento = '$id_mantenimiento'";

    if (mysqli_query($conexion, $query)) {
        $_SESSION['mensaje'] = 'Mantenimiento actualizado correctamente';
        $_SESSION['tipo_mensaje'] = 'success';
        header("Location: index_mantenimiento.php");
    } else {
        $_SESSION['mensaje'] = 'Error al actualizar el mantenimiento: ' . mysqli_error($conexion);
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: editar_mantenimiento.php?id=" . $id_mantenimiento);
    }
} else {
    header("Location: index_mantenimiento.php");
}
exit();
?>
