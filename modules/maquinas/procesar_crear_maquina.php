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

// Insertar la máquina primero (sin imagen)
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
    // Obtener el ID de la máquina recién creada
    $id_maquina = mysqli_insert_id($conexion);
    
    // Procesar la imagen si se subió
    $ruta_imagen = 'imgMaquinas/no-maquina.png'; // Valor por defecto
    
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
            header("Location: crear_maquinas.php");
            exit();
        }
        
        // Validar tamaño (5MB máximo)
        if ($tamano_archivo > 5 * 1024 * 1024) {
            $_SESSION['error'] = "La imagen es demasiado grande. Tamaño máximo: 5MB.";
            mysqli_close($conexion);
            header("Location: crear_maquinas.php");
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
        
        // Crear carpeta si no existe
        $carpeta_destino = '../../imgMaquinas/';
        if (!file_exists($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }
        
        // Nombre del archivo: ID de la máquina + extensión
        $nombre_archivo = $id_maquina . '.' . $extension;
        $ruta_completa = $carpeta_destino . $nombre_archivo;
        
        // Mover archivo a la carpeta de destino
        if (move_uploaded_file($nombre_temporal, $ruta_completa)) {
            $ruta_imagen = 'imgMaquinas/' . $nombre_archivo;
        } else {
            // Si falla la subida, usar imagen por defecto
            $ruta_imagen = 'imgMaquinas/no-maquina.png';
        }
    }
    
    // Actualizar la máquina con la ruta de la imagen
    $sql_update = "UPDATE maquinas SET imagen = '$ruta_imagen' WHERE id_maquina = $id_maquina";
    mysqli_query($conexion, $sql_update);
    
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
