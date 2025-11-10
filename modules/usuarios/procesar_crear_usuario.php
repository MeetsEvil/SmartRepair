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
    header("Location: crear_usuarios.php");
    exit();
}

// Obtener y limpiar datos del formulario
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

// Validar que las contraseñas coincidan
if ($password !== $password_confirm) {
    $errores[] = "Las contraseñas no coinciden";
}

// Validar longitud de contraseña
if (strlen($password) < 6) {
    $errores[] = "La contraseña debe tener al menos 6 caracteres";
}

// Verificar que el email no exista
$check_email = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE email = '$email'");
if (mysqli_num_rows($check_email) > 0) {
    $errores[] = "El correo electrónico ya está registrado";
}

// Verificar que el usuario no exista
$check_usuario = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE usuario = '$usuario'");
if (mysqli_num_rows($check_usuario) > 0) {
    $errores[] = "El nombre de usuario ya está en uso";
}

// Si hay errores, redirigir con mensaje
if (!empty($errores)) {
    $_SESSION['error'] = implode(", ", $errores);
    header("Location: crear_usuarios.php");
    exit();
}

// Encriptar contraseña (usando MD5 como en tu sistema actual)
// NOTA: En producción se recomienda usar password_hash() con BCRYPT
$password_hash = md5($password);

// Preparar query de inserción
if ($id_planta !== NULL) {
    $sql = "INSERT INTO usuarios (nombre, apellido, email, usuario, password, telefono, id_rol, id_planta, estado) 
            VALUES ('$nombre', '$apellido', '$email', '$usuario', '$password_hash', '$telefono', $id_rol, $id_planta, '$estado')";
} else {
    $sql = "INSERT INTO usuarios (nombre, apellido, email, usuario, password, telefono, id_rol, estado) 
            VALUES ('$nombre', '$apellido', '$email', '$usuario', '$password_hash', '$telefono', $id_rol, '$estado')";
}

// Ejecutar inserción
if (mysqli_query($conexion, $sql)) {
    $_SESSION['success'] = true;
    mysqli_close($conexion);
    header("Location: crear_usuarios.php");
    exit();
} else {
    $_SESSION['error'] = "Error al crear el usuario: " . mysqli_error($conexion);
    mysqli_close($conexion);
    header("Location: crear_usuarios.php");
    exit();
}
