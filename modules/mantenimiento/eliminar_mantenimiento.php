<?php
session_start();

// Verificar sesión y permisos (solo Administrador puede eliminar)
if (!isset($_SESSION['usuarioingresando']) || $_SESSION['rol'] != 'Administrador') {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

// Obtener ID del mantenimiento
$id_mantenimiento = isset($_GET['id']) ? mysqli_real_escape_string($conexion, $_GET['id']) : null;

if (!$id_mantenimiento) {
    $_SESSION['mensaje'] = 'ID de mantenimiento no válido';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: index_mantenimiento.php");
    exit();
}

// Verificar que el mantenimiento existe
$query_verificar = "SELECT id_mantenimiento FROM mantenimientos WHERE id_mantenimiento = '$id_mantenimiento'";
$result_verificar = mysqli_query($conexion, $query_verificar);

if (!$result_verificar || mysqli_num_rows($result_verificar) == 0) {
    $_SESSION['mensaje'] = 'El mantenimiento no existe';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: index_mantenimiento.php");
    exit();
}

// Eliminar el mantenimiento
$query_eliminar = "DELETE FROM mantenimientos WHERE id_mantenimiento = '$id_mantenimiento'";

if (mysqli_query($conexion, $query_eliminar)) {
    $_SESSION['mensaje'] = 'Mantenimiento eliminado correctamente';
    $_SESSION['tipo_mensaje'] = 'success';
} else {
    $_SESSION['mensaje'] = 'Error al eliminar el mantenimiento: ' . mysqli_error($conexion);
    $_SESSION['tipo_mensaje'] = 'error';
}

header("Location: index_mantenimiento.php");
exit();
?>
