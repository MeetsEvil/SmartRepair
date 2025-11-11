<?php
session_start();
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = mysqli_real_escape_string($conexion, $_POST["txtusuario"]);
    $pass    = mysqli_real_escape_string($conexion, $_POST["txtpassword"]);

    // Consulta que ahora verifica los tres campos: usuario, contraseña y rol
    $query = "SELECT * FROM usuarios WHERE BINARY usuario= '$usuario' AND BINARY password = MD5('$pass') AND estado='Activo'";
    $resultado = mysqli_query($conexion, $query);

    if ($resultado && mysqli_num_rows($resultado) === 1) {
        $fila = mysqli_fetch_assoc($resultado);
        $_SESSION['usuarioingresando'] = $fila['usuario'];
        if($fila['id_rol'] == 1){
            $_SESSION['rol'] = "Administrador";
        }elseif($fila['id_rol'] == 2){
            $_SESSION['rol'] = "Técnico";
        }else{
            $_SESSION['rol'] = "Operario";
        }

        // $_SESSION['rol'] = $fila['id_rol']; // Se guarda el rol en la sesión
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<script>alert('Usuario o contraseña incorrectos'); window.location='index.php';</script>";
        exit();
    }
} else {
    // Si se intenta acceder a login.php directamente, se redirige a index
    header("Location: index.php");
    exit();
}
?>