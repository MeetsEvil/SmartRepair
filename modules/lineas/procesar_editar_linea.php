<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuarioingresando']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index_lineas.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index_lineas.php");
    exit();
}

$id_linea = intval($_POST['id_linea']);
$nombre_linea = mysqli_real_escape_string($conexion, trim($_POST['nombre_linea']));
$id_planta = intval($_POST['id_planta']);
$id_prioridad = intval($_POST['id_prioridad']);
$descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
$estado = mysqli_real_escape_string($conexion, $_POST['estado']);

// Verificar que no exista otra línea con el mismo nombre en la misma planta
$check = mysqli_query($conexion, "SELECT id_linea FROM lineas WHERE nombre_linea = '$nombre_linea' AND id_planta = $id_planta AND id_linea != $id_linea");
if (mysqli_num_rows($check) > 0) {
    $_SESSION['error'] = "Ya existe otra línea con ese nombre en la planta seleccionada";
    header("Location: editar_lineas.php?id=$id_linea");
    exit();
}

$sql = "UPDATE lineas SET 
        nombre_linea = '$nombre_linea', 
        id_planta = $id_planta, 
        id_prioridad = $id_prioridad, 
        descripcion = '$descripcion', 
        estado = '$estado'
        WHERE id_linea = $id_linea";

if (mysqli_query($conexion, $sql)) {
    $_SESSION['success'] = true;
    mysqli_close($conexion);
    header("Location: editar_lineas.php?id=$id_linea");
    exit();
} else {
    $_SESSION['error'] = "Error al actualizar la línea: " . mysqli_error($conexion);
    mysqli_close($conexion);
    header("Location: editar_lineas.php?id=$id_linea");
    exit();
}
