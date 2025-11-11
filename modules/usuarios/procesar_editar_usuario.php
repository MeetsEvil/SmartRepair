<?php
session_start();
require_once '../../config/db.php';

// Verificar que el usuario esté logueado y sea administrador
if (!isset($_SESSION['usuarioingresando']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index_usuarios.php");
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index_usuarios.php");
    exit();
}

// Obtener y limpiar datos del formulario
$id_usuario = intval($_POST['id_usuario']);
$nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
$apellido = mysqli_real_escape_string($conexion, trim($_POST['apellido']));
$email = mysqli_real_escape_string($conexion, trim($_POST['email']));
$usuario = mysqli_real_escape_string($conexion, trim($_POST['usuario']));
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];
$telefono = mysqli_real_escape_string($conexion, trim($_POST['telefono']));
$id_rol = intval($_POST['id_rol']);
$id_planta = !empty($_POST['id_planta']) ? intval($_POST['id_planta']) : NULL;
$estado = mysqli_real_escape_string($conexion, $_POST['estado']);

// Validaciones
$errores = [];

// Validar que las contraseñas coincidan (solo si se ingresó una)
if (!empty($password) || !empty($password_confirm)) {
    if ($password !== $password_confirm) {
        $errores[] = "Las contraseñas no coinciden";
    }
    
    if (strlen($password) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
    }
}

// Verificar que el email no exista en otro usuario
$check_email = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE email = '$email' AND id_usuario != $id_usuario");
if (mysqli_num_rows($check_email) > 0) {
    $errores[] = "El correo electrónico ya está registrado en otro usuario";
}

// Verificar que el usuario no exista en otro usuario
$check_usuario = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE usuario = '$usuario' AND id_usuario != $id_usuario");
if (mysqli_num_rows($check_usuario) > 0) {
    $errores[] = "El nombre de usuario ya está en uso por otro usuario";
}

// Si hay errores, redirigir con mensaje
if (!empty($errores)) {
    $_SESSION['error'] = implode(", ", $errores);
    header("Location: editar_usuarios.php?id=$id_usuario");
    exit();
}

// Preparar query de actualización
if (!empty($password)) {
    // Si se ingresó una nueva contraseña, actualizarla
    $password_hash = md5($password);
    
    if ($id_planta !== NULL) {
        $sql = "UPDATE usuarios SET 
                nombre = '$nombre', 
                apellido = '$apellido', 
                email = '$email', 
                usuario = '$usuario', 
                password = '$password_hash',
                telefono = '$telefono', 
                id_rol = $id_rol, 
                id_planta = $id_planta, 
                estado = '$estado'
                WHERE id_usuario = $id_usuario";
    } else {
        $sql = "UPDATE usuarios SET 
                nombre = '$nombre', 
                apellido = '$apellido', 
                email = '$email', 
                usuario = '$usuario', 
                password = '$password_hash',
                telefono = '$telefono', 
                id_rol = $id_rol, 
                id_planta = NULL, 
                estado = '$estado'
                WHERE id_usuario = $id_usuario";
    }
} else {
    // Si NO se ingresó contraseña, no actualizarla
    if ($id_planta !== NULL) {
        $sql = "UPDATE usuarios SET 
                nombre = '$nombre', 
                apellido = '$apellido', 
                email = '$email', 
                usuario = '$usuario', 
                telefono = '$telefono', 
                id_rol = $id_rol, 
                id_planta = $id_planta, 
                estado = '$estado'
                WHERE id_usuario = $id_usuario";
    } else {
        $sql = "UPDATE usuarios SET 
                nombre = '$nombre', 
                apellido = '$apellido', 
                email = '$email', 
                usuario = '$usuario', 
                telefono = '$telefono', 
                id_rol = $id_rol, 
                id_planta = NULL, 
                estado = '$estado'
                WHERE id_usuario = $id_usuario";
    }
}

// Ejecutar actualización
if (mysqli_query($conexion, $sql)) {
    $_SESSION['success'] = true;
    mysqli_close($conexion);
    header("Location: editar_usuarios.php?id=$id_usuario");
    exit();
} else {
    $_SESSION['error'] = "Error al actualizar el usuario: " . mysqli_error($conexion);
    mysqli_close($conexion);
    header("Location: editar_usuarios.php?id=$id_usuario");
    exit();
}
