<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_ticket = mysqli_real_escape_string($conexion, $_POST['id_ticket']);
    $accion = mysqli_real_escape_string($conexion, $_POST['accion']);
    $rol = $_SESSION['rol'];
    $id_usuario = $_SESSION['id_usuario'];

    // Obtener estado actual del ticket
    $query_ticket = "SELECT id_estado, id_tecnico_responsable FROM tickets WHERE id_ticket = '$id_ticket'";
    $result_ticket = mysqli_query($conexion, $query_ticket);
    $ticket = mysqli_fetch_assoc($result_ticket);

    if (!$ticket) {
        $_SESSION['mensaje'] = 'Ticket no encontrado';
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: index_tickets.php");
        exit();
    }

    $estado_actual = $ticket['id_estado'];

    // ACCIÓN 1: ASIGNAR TÉCNICO (Pendiente → En Progreso)
    if ($accion == 'asignar_tecnico' && $estado_actual == 1) {
        // Validar permisos (Admin o Técnico)
        if ($rol != 'Administrador' && $rol != 'Técnico') {
            $_SESSION['mensaje'] = 'No tiene permisos para realizar esta acción';
            $_SESSION['tipo_mensaje'] = 'error';
            header("Location: index_tickets.php");
            exit();
        }

        $id_tecnico = mysqli_real_escape_string($conexion, $_POST['id_tecnico']);

        if (empty($id_tecnico)) {
            $_SESSION['mensaje'] = 'Debe seleccionar un técnico';
            $_SESSION['tipo_mensaje'] = 'error';
            header("Location: editar_tickets.php?id=" . $id_ticket);
            exit();
        }

        // Actualizar ticket: asignar técnico y cambiar estado a 2 (En Progreso)
        // El trigger calculará automáticamente fecha_asignacion y tiempo_respuesta
        $query = "UPDATE tickets SET 
                    id_tecnico_responsable = '$id_tecnico',
                    id_estado = 2
                  WHERE id_ticket = '$id_ticket'";

        if (mysqli_query($conexion, $query)) {
            $_SESSION['mensaje'] = 'Técnico asignado correctamente. El ticket pasó a "En Progreso"';
            $_SESSION['tipo_mensaje'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al asignar técnico: ' . mysqli_error($conexion);
            $_SESSION['tipo_mensaje'] = 'error';
        }
    }
    // ACCIÓN 2: AGREGAR CAUSA RAÍZ (En Progreso → En Validación)
    elseif ($accion == 'agregar_causa_raiz' && $estado_actual == 2) {
        // Validar que sea el técnico asignado
        if ($rol == 'Técnico' && $ticket['id_tecnico_responsable'] != $id_usuario) {
            $_SESSION['mensaje'] = 'Solo el técnico asignado puede completar esta acción';
            $_SESSION['tipo_mensaje'] = 'error';
            header("Location: index_tickets.php");
            exit();
        }

        $causa_raiz = mysqli_real_escape_string($conexion, trim($_POST['causa_raiz']));
        $solucion_aplicada = mysqli_real_escape_string($conexion, trim($_POST['solucion_aplicada']));

        if (strlen($causa_raiz) < 20 || strlen($solucion_aplicada) < 20) {
            $_SESSION['mensaje'] = 'La causa raíz y la solución deben tener al menos 20 caracteres';
            $_SESSION['tipo_mensaje'] = 'error';
            header("Location: editar_tickets.php?id=" . $id_ticket);
            exit();
        }

        // Actualizar ticket: agregar causa raíz, solución y cambiar estado a 3 (En Validación)
        // El trigger calculará automáticamente fecha_resolucion y tiempo_resolucion
        $query = "UPDATE tickets SET 
                    causa_raiz = '$causa_raiz',
                    solucion_aplicada = '$solucion_aplicada',
                    id_estado = 3
                  WHERE id_ticket = '$id_ticket'";

        if (mysqli_query($conexion, $query)) {
            $_SESSION['mensaje'] = 'Causa raíz agregada correctamente. El ticket pasó a "En Validación"';
            $_SESSION['tipo_mensaje'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al agregar causa raíz: ' . mysqli_error($conexion);
            $_SESSION['tipo_mensaje'] = 'error';
        }
    }
    // ACCIÓN 3: CONFIRMAR RESOLUCIÓN (En Validación → Finalizado)
    elseif ($accion == 'confirmar_resolucion' && $estado_actual == 3) {
        // Validar que sea Administrador
        if ($rol != 'Administrador') {
            $_SESSION['mensaje'] = 'Solo un administrador puede confirmar la resolución';
            $_SESSION['tipo_mensaje'] = 'error';
            header("Location: index_tickets.php");
            exit();
        }

        $observaciones = isset($_POST['observaciones']) ? mysqli_real_escape_string($conexion, trim($_POST['observaciones'])) : '';

        // Actualizar ticket: cambiar estado a 4 (Finalizado)
        // El trigger calculará automáticamente fecha_cierre y tiempo_total
        if (!empty($observaciones)) {
            $query = "UPDATE tickets SET 
                        observaciones = '$observaciones',
                        id_estado = 4
                      WHERE id_ticket = '$id_ticket'";
        } else {
            $query = "UPDATE tickets SET 
                        id_estado = 4
                      WHERE id_ticket = '$id_ticket'";
        }

        if (mysqli_query($conexion, $query)) {
            $_SESSION['mensaje'] = 'Ticket finalizado correctamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al finalizar ticket: ' . mysqli_error($conexion);
            $_SESSION['tipo_mensaje'] = 'error';
        }
    } else {
        $_SESSION['mensaje'] = 'Acción no válida o estado incorrecto';
        $_SESSION['tipo_mensaje'] = 'error';
    }

    header("Location: index_tickets.php");
} else {
    header("Location: index_tickets.php");
}
exit();
?>
