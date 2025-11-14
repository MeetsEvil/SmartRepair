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

// Procesar cambio de imagen si se subió una nueva
$actualizar_imagen = false;
$nueva_ruta_imagen = '';

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['imagen'];
    $nombre_temporal = $archivo['tmp_name'];
    $tipo_archivo = $archivo['type'];
    $tamano_archivo = $archivo['size'];
    
    // Validar tipo de archivo
    $tipos_permitidos = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
    if (!in_array($tipo_archivo, $tipos_permitidos)) {
        $_SESSION['error'] = "Tipo de archivo no permitido. Use PNG, JPG, JPEG o GIF.";
        mysqli_close($conexion);
        header("Location: editar_maquinas.php?id=$id_maquina");
        exit();
    }
    
    // Validar tamaño (5MB máximo)
    if ($tamano_archivo > 5 * 1024 * 1024) {
        $_SESSION['error'] = "La imagen es demasiado grande. Tamaño máximo: 5MB.";
        mysqli_close($conexion);
        header("Location: editar_maquinas.php?id=$id_maquina");
        exit();
    }
    
    // Obtener extensión del archivo
    $extension = '';
    switch ($tipo_archivo) {
        case 'image/png':
            $extension = 'png';
            break;
        case 'image/jpeg':
        case 'image/jpg':
            $extension = 'jpg';
            break;
        case 'image/gif':
            $extension = 'gif';
            break;
    }
    
    // Carpeta de destino
    $carpeta_destino = '../../imgMaquinas/';
    
    // Eliminar imagen anterior (todas las extensiones posibles)
    $extensiones_posibles = ['png', 'jpg', 'jpeg', 'gif'];
    foreach ($extensiones_posibles as $ext) {
        $archivo_anterior = $carpeta_destino . $id_maquina . '.' . $ext;
        if (file_exists($archivo_anterior)) {
            unlink($archivo_anterior);
        }
    }
    
    // Nombre del nuevo archivo: ID de la máquina + extensión
    $nombre_archivo = $id_maquina . '.' . $extension;
    $ruta_completa = $carpeta_destino . $nombre_archivo;
    
    // Mover archivo a la carpeta de destino
    if (move_uploaded_file($nombre_temporal, $ruta_completa)) {
        $nueva_ruta_imagen = 'imgMaquinas/' . $nombre_archivo;
        $actualizar_imagen = true;
    } else {
        $_SESSION['error'] = "Error al subir la imagen. Intente nuevamente.";
        mysqli_close($conexion);
        header("Location: editar_maquinas.php?id=$id_maquina");
        exit();
    }
}

// Construir consulta SQL
if ($actualizar_imagen) {
    // Actualizar con nueva imagen
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
            observaciones = '$observaciones',
            imagen = '$nueva_ruta_imagen'
            WHERE id_maquina = $id_maquina";
} else {
    // Actualizar sin cambiar imagen
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
}

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
