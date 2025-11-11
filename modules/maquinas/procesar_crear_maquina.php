<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuarioingresando']) || ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico')) {
    header("Location: index_maquinas.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: crear_maquinas.php");
    exit();
}

$codigo_maquina = mysqli_real_escape_string($conexion, trim($_POST['codigo_maquina']));
$marca = mysqli_real_escape_string($conexion, trim($_POST['marca']));
$modelo = mysqli_real_escape_string($conexion, trim($_POST['modelo']));
$numero_serie = mysqli_real_escape_string($conexion, trim($_POST['numero_serie']));
$id_planta = intval($_POST['id_planta']);
$id_linea = intval($_POST['id_linea']);
$area = mysqli_real_escape_string($conexion, trim($_POST['area']));
$fecha_instalacion = !empty($_POST['fecha_instalacion']) ? mysqli_real_escape_string($conexion, $_POST['fecha_instalacion']) : NULL;
$observaciones = mysqli_real_escape_string($conexion, trim($_POST['observaciones']));
$id_usuario = $_SESSION['id_usuario'] ?? NULL;

// Verificar que no exista una máquina con el mismo código
$check = mysqli_query($conexion, "SELECT id_maquina FROM maquinas WHERE codigo_maquina = '$codigo_maquina'");
if (mysqli_num_rows($check) > 0) {
    $_SESSION['error'] = "Ya existe una máquina con ese código";
    header("Location: crear_maquinas.php");
    exit();
}

$fecha_sql = $fecha_instalacion ? "'$fecha_instalacion'" : "NULL";
$usuario_sql = $id_usuario ? $id_usuario : "NULL";

$sql = "INSERT INTO maquinas (
            codigo_maquina, 
            marca, 
            modelo, 
            numero_serie, 
            id_planta, 
            id_linea, 
            area, 
            fecha_instalacion, 
            estado, 
            observaciones,
            created_by
        ) VALUES (
            '$codigo_maquina', 
            '$marca', 
            '$modelo', 
            '$numero_serie', 
            $id_planta, 
            $id_linea, 
            '$area', 
            $fecha_sql, 
            'Activa', 
            '$observaciones',
            $usuario_sql
        )";

if (mysqli_query($conexion, $sql)) {
    $_SESSION['success'] = true;
    mysqli_close($conexion);
    header("Location: crear_maquinas.php");
    exit();
} else {
    $_SESSION['error'] = "Error al crear la máquina: " . mysqli_error($conexion);
    mysqli_close($conexion);
    header("Location: crear_maquinas.php");
    exit();
}
