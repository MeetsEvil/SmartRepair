<?php
session_start();

// Verificar sesión y permisos (solo Administrador)
if (!isset($_SESSION['usuarioingresando']) || $_SESSION['rol'] != 'Administrador') {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

if (isset($_GET['id'])) {
    $id_ticket = mysqli_real_escape_string($conexion, $_GET['id']);

    // Nota: En tu schema no existe campo 'activo', así que vamos a eliminar el ticket directamente
    // Si prefieres mantenerlo, necesitarías agregar el campo 'activo' a la tabla tickets
    $query = "DELETE FROM tickets WHERE id_ticket = '$id_ticket'";
    
    if (mysqli_query($conexion, $query)) {
        $_SESSION['mensaje'] = 'Ticket eliminado correctamente';
        $_SESSION['tipo_mensaje'] = 'success';
    } else {
        $_SESSION['mensaje'] = 'Error al eliminar el ticket: ' . mysqli_error($conexion);
        $_SESSION['tipo_mensaje'] = 'error';
    }
} else {
    $_SESSION['mensaje'] = 'ID de ticket no especificado';
    $_SESSION['tipo_mensaje'] = 'error';
}

header("Location: index_tickets.php");
exit();
?>
