<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuarioingresando']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index_lineas.php");
    exit();
}

$id_linea = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_linea <= 0) {
    $_SESSION['error'] = "ID de línea inválido";
    header("Location: index_lineas.php");
    exit();
}

// Obtener estado actual
$query = mysqli_query($conexion, "SELECT estado FROM lineas WHERE id_linea = $id_linea");
$linea = mysqli_fetch_assoc($query);

if (!$linea) {
    $_SESSION['error'] = "Línea no encontrada";
    header("Location: index_lineas.php");
    exit();
}

// Cambiar al estado opuesto
$nuevo_estado = ($linea['estado'] == 'Activa') ? 'Inactiva' : 'Activa';
$sql = "UPDATE lineas SET estado = '$nuevo_estado' WHERE id_linea = $id_linea";

if (mysqli_query($conexion, $sql)) {
    $_SESSION['success_delete'] = true;
    mysqli_close($conexion);
    header("Location: index_lineas.php");
    exit();
} else {
    $_SESSION['error'] = "Error al cambiar el estado: " . mysqli_error($conexion);
    mysqli_close($conexion);
    header("Location: index_lineas.php");
    exit();
}
