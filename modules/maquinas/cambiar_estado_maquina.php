<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuarioingresando']) || ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico')) {
    header("Location: index_maquinas.php");
    exit();
}

$id_maquina = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_maquina <= 0) {
    $_SESSION['error'] = "ID de máquina inválido";
    header("Location: index_maquinas.php");
    exit();
}

// Obtener estado actual
$query = mysqli_query($conexion, "SELECT estado FROM maquinas WHERE id_maquina = $id_maquina");
$maquina = mysqli_fetch_assoc($query);

if (!$maquina) {
    $_SESSION['error'] = "Máquina no encontrada";
    header("Location: index_maquinas.php");
    exit();
}

// Cambiar al estado opuesto
$nuevo_estado = ($maquina['estado'] == 'Activa') ? 'Inactiva' : 'Activa';
$sql = "UPDATE maquinas SET estado = '$nuevo_estado' WHERE id_maquina = $id_maquina";

if (mysqli_query($conexion, $sql)) {
    $_SESSION['success_delete'] = true;
    mysqli_close($conexion);
    header("Location: index_maquinas.php");
    exit();
} else {
    $_SESSION['error'] = "Error al cambiar el estado: " . mysqli_error($conexion);
    mysqli_close($conexion);
    header("Location: index_maquinas.php");
    exit();
}
