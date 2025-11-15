<?php
session_start();
require_once '../../config/db.php';

// Verificar que el usuario esté logueado y sea administrador
if (!isset($_SESSION['usuarioingresando']) || $_SESSION['rol'] !== 'Administrador') {
    $_SESSION['mensaje'] = 'No tienes permisos para realizar esta acción';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: index_tickets.php");
    exit();
}

$id_ticket = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_ticket <= 0) {
    $_SESSION['mensaje'] = 'ID de ticket inválido';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: index_tickets.php");
    exit();
}

// Verificar que el ticket exista y esté finalizado
$query = "SELECT id_ticket, codigo_ticket, id_estado 
          FROM tickets 
          WHERE id_ticket = $id_ticket";
$resultado = mysqli_query($conexion, $query);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    $_SESSION['mensaje'] = 'Ticket no encontrado';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: index_tickets.php");
    exit();
}

$ticket = mysqli_fetch_assoc($resultado);

// Verificar que el ticket esté finalizado (id_estado = 4)
if ($ticket['id_estado'] != 4) {
    $_SESSION['mensaje'] = 'Solo se pueden ocultar tickets finalizados';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: ver_tickets.php?id=$id_ticket");
    exit();
}

// Ocultar el ticket (eliminación lógica)
$sql = "UPDATE tickets SET visible = 0 WHERE id_ticket = $id_ticket";

if (mysqli_query($conexion, $sql)) {
    $_SESSION['mensaje'] = 'Ticket ocultado correctamente';
    $_SESSION['tipo_mensaje'] = 'success';
} else {
    $_SESSION['mensaje'] = 'Error al ocultar el ticket: ' . mysqli_error($conexion);
    $_SESSION['tipo_mensaje'] = 'error';
}

mysqli_close($conexion);
header("Location: index_tickets.php");
exit();
