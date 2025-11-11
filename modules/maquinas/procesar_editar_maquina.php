<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuarioingresando']) || ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico')) {
    header("Location: index_maquinas.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index_maquinas.php");
    exit();
}

$id_maquina = intval($_POST['id_maquina']);
$codigo_maquina = mysqli_real_escape_string($conexion, trim($_POST['codigo_maquina']));
$marca = mysqli_real_escape_string($conexion, trim($_POST['marca']));
$modelo = mysqli_real_escape_string($conexion, trim($_POST['modelo']));
$numero_serie = mysqli_real_escape_string($conexion, trim($_POST['numero_serie']));
$id_planta = intval($_POST['id_planta']);
$id_linea = intval($_POST['id_linea']);
$area = mysqli_real_escape_string($conexion, trim($_POST['area']));
$fecha_instalacion = !empty($_POST['fecha_instalacion']) ? mysqli_real_escape_string($conexion, $_POST['fecha_instalacion']) : NULL;
$estado = mysqli_real_escape_string($conexion, $_POST['estado']);
$observaciones = mysqli_real_escape_string($conexion, trim($_POST['observaciones']));

// Verificar que no exista otra máquina con el mismo código
$check = mysqli_query($conexion, "SELECT id_maquina FROM maquinas WHERE codigo_maquina = '$codigo_maquina' AND id_maquina != $id_maquina");
if (mysqli_num_rows($check) > 0) {
    $_SESSION['error'] = "Ya existe otra máquina con ese código";
    header("Location: editar_maquinas.php?id=$id_maquina");
    exit();
}

$fecha_sql = $fecha_instalacion ? "'$fecha_instalacion'" : "NULL";

$sql = "UPDATE maquinas SET 
        codigo_maquina = '$codigo_maquina', 
        marca = '$marca', 
        modelo = '$modelo', 
        numero_serie = '$numero_serie', 
        id_planta = $id_planta, 
        id_linea = $id_linea, 
        area = '$area', 
        fecha_instalacion = $fecha_sql, 
        estado = '$estado', 
        observaciones = '$observaciones'
        WHERE id_maquina = $id_maquina";

if (mysqli_query($conexion, $sql)) {
    $_SESSION['success'] = true;
    mysqli_close($conexion);
    header("Location: editar_maquinas.php?id=$id_maquina");
    exit();
} else {
    $_SESSION['error'] = "Error al actualizar la máquina: " . mysqli_error($conexion);
    mysqli_close($conexion);
    header("Location: editar_maquinas.php?id=$id_maquina");
    exit();
}
