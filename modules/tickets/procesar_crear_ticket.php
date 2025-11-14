<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar que el id_usuario esté en la sesión
    if (!isset($_SESSION['id_usuario'])) {
        // Obtener el id_usuario desde la base de datos usando el nombre de usuario
        $usuario = $_SESSION['usuarioingresando'];
        $query_usuario = "SELECT id_usuario FROM usuarios WHERE usuario = '$usuario'";
        $result_usuario = mysqli_query($conexion, $query_usuario);
        if ($result_usuario && mysqli_num_rows($result_usuario) > 0) {
            $row_usuario = mysqli_fetch_assoc($result_usuario);
            $_SESSION['id_usuario'] = $row_usuario['id_usuario'];
        } else {
            $_SESSION['mensaje'] = 'Error: No se pudo identificar al usuario';
            $_SESSION['tipo_mensaje'] = 'error';
            header("Location: crear_tickets.php");
            exit();
        }
    }

    $id_maquina = mysqli_real_escape_string($conexion, $_POST['id_maquina']);
    $id_prioridad = mysqli_real_escape_string($conexion, $_POST['id_prioridad']);
    $id_tipo_falla = mysqli_real_escape_string($conexion, $_POST['id_tipo_falla']);
    $descripcion_falla = mysqli_real_escape_string($conexion, trim($_POST['descripcion_falla']));
    $id_usuario_reporta = $_SESSION['id_usuario'];

    // Validaciones
    if (empty($id_maquina) || empty($id_prioridad) || empty($id_tipo_falla) || empty($descripcion_falla)) {
        $_SESSION['mensaje'] = 'Todos los campos obligatorios deben ser completados';
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: crear_tickets.php");
        exit();
    }

    if (strlen($descripcion_falla) < 20) {
        $_SESSION['mensaje'] = 'La descripción debe tener al menos 20 caracteres';
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: crear_tickets.php");
        exit();
    }

    // Procesar foto si se subió
    $foto_url = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/jfif'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        $file_extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($_FILES['foto']['type'], $allowed_types) && !in_array($file_extension, $allowed_extensions)) {
            $_SESSION['mensaje'] = 'Solo se permiten imágenes (JPG, JPEG, PNG, GIF, JFIF)';
            $_SESSION['tipo_mensaje'] = 'error';
            header("Location: crear_tickets.php");
            exit();
        }

        if ($_FILES['foto']['size'] > $max_size) {
            $_SESSION['mensaje'] = 'La imagen no debe superar los 5MB';
            $_SESSION['tipo_mensaje'] = 'error';
            header("Location: crear_tickets.php");
            exit();
        }

        // Crear directorio si no existe
        $upload_dir = '../../uploads/tickets/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generar nombre único para la foto (siempre guardar como JPG)
        $nombre_archivo = 'ticket_' . date('YmdHis') . '_' . uniqid() . '.jpg';
        $ruta_destino = $upload_dir . $nombre_archivo;

        // Convertir la imagen a JPG para compatibilidad
        $imagen_temp = $_FILES['foto']['tmp_name'];
        $extension_original = strtolower($file_extension);
        
        // Crear imagen desde el archivo subido
        $imagen = null;
        switch ($extension_original) {
            case 'jpg':
            case 'jpeg':
            case 'jfif':
                $imagen = @imagecreatefromjpeg($imagen_temp);
                break;
            case 'png':
                $imagen = @imagecreatefrompng($imagen_temp);
                break;
            case 'gif':
                $imagen = @imagecreatefromgif($imagen_temp);
                break;
        }

        if ($imagen !== false && $imagen !== null) {
            // Guardar como JPG con calidad 90
            if (imagejpeg($imagen, $ruta_destino, 90)) {
                $foto_url = 'uploads/tickets/' . $nombre_archivo;
            }
            imagedestroy($imagen);
        } else {
            // Si falla la conversión, intentar mover el archivo directamente
            if (move_uploaded_file($imagen_temp, $ruta_destino)) {
                $foto_url = 'uploads/tickets/' . $nombre_archivo;
            }
        }
    }

    // Insertar ticket (el trigger generará el código automáticamente)
    // Estado inicial: 1 (Pendiente)
    if ($foto_url) {
        $query = "INSERT INTO tickets (id_maquina, id_tipo_falla, id_prioridad, id_estado, 
                  id_usuario_reporta, descripcion_falla, foto_url) 
                  VALUES ('$id_maquina', '$id_tipo_falla', '$id_prioridad', 1, 
                  '$id_usuario_reporta', '$descripcion_falla', '$foto_url')";
    } else {
        $query = "INSERT INTO tickets (id_maquina, id_tipo_falla, id_prioridad, id_estado, 
                  id_usuario_reporta, descripcion_falla) 
                  VALUES ('$id_maquina', '$id_tipo_falla', '$id_prioridad', 1, 
                  '$id_usuario_reporta', '$descripcion_falla')";
    }

    if (mysqli_query($conexion, $query)) {
        $_SESSION['mensaje'] = 'Ticket creado correctamente';
        $_SESSION['tipo_mensaje'] = 'success';
        header("Location: index_tickets.php");
    } else {
        $_SESSION['mensaje'] = 'Error al crear el ticket: ' . mysqli_error($conexion);
        $_SESSION['tipo_mensaje'] = 'error';
        header("Location: crear_tickets.php");
    }
} else {
    header("Location: crear_tickets.php");
}
exit();
?>
