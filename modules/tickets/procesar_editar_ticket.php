<?php
session_start();

// Verificar sesión y permisos
if (!isset($_SESSION['usuarioingresando']) || 
    ($_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Técnico')) {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_ticket = mysqli_real_escape_string($conexion, $_POST['id_ticket']);
    $id_maquina = mysqli_real_escape_string($conexion, $_POST['id_maquina']);
    $id_prioridad = mysqli_real_escape_string($conexion, $_POST['id_prioridad']);
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
    $id_responsable = !empty($_POST['id_responsable']) ? mysqli_real_escape_string($conexion, $_POST['id_responsable']) : null;
    $estado_actual = mysqli_real_escape_string($conexion, $_POST['estado_actual']);

    // Validaciones
    if (empty($id_maquina) || empty($id_prioridad) || empty($descripcion)) {
        $_SESSION['mensaje'] = 'Todos los campos obligatorios deben ser completados';
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: editar_tickets.php?id=" . $id_ticket);
        exit();
    }

    if (strlen($descripcion) < 10) {
        $_SESSION['mensaje'] = 'La descripción debe tener al menos 10 caracteres';
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: editar_tickets.php?id=" . $id_ticket);
        exit();
    }

    // Determinar el nuevo estado
    $nuevo_estado = $estado_actual;

    // Si está en Pendiente (1) y se asigna responsable, pasar a En Progreso (2)
    if ($estado_actual == 1 && !empty($id_responsable)) {
        $nuevo_estado = 2;
        
        // Cambio de Pendiente a En Progreso (el trigger calculará fecha_asignacion y tiempo_respuesta)
        $query = "UPDATE tickets SET 
                    id_maquina = '$id_maquina',
                    id_prioridad = '$id_prioridad',
                    descripcion_falla = '$descripcion',
                    id_tecnico_responsable = '$id_responsable',
                    id_estado = '$nuevo_estado'
                  WHERE id_ticket = '$id_ticket'";
    } else {
        // Actualización normal sin cambio de estado
        if (!empty($id_responsable)) {
            $query = "UPDATE tickets SET 
                        id_maquina = '$id_maquina',
                        id_prioridad = '$id_prioridad',
                        descripcion_falla = '$descripcion',
                        id_tecnico_responsable = '$id_responsable'
                      WHERE id_ticket = '$id_ticket'";
        } else {
            $query = "UPDATE tickets SET 
                        id_maquina = '$id_maquina',
                        id_prioridad = '$id_prioridad',
                        descripcion_falla = '$descripcion',
                        id_tecnico_responsable = NULL
                      WHERE id_ticket = '$id_ticket'";
        }
    }

    if (mysqli_query($conexion, $query)) {
        if ($nuevo_estado == 2 && $estado_actual == 1) {
            $_SESSION['mensaje'] = 'Ticket actualizado y movido a "En Progreso" correctamente';
        } else {
            $_SESSION['mensaje'] = 'Ticket actualizado correctamente';
        }
        $_SESSION['tipo_mensaje'] = 'success';
        header("Location: index_tickets.php");
    } else {
        $_SESSION['mensaje'] = 'Error al actualizar el ticket: ' . mysqli_error($conexion);
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: editar_tickets.php?id=" . $id_ticket);
    }
} else {
    header("Location: index_tickets.php");
}
exit();
?>
