<?php
session_start();         // Iniciar 
session_unset();         // Eliminar variables 
session_destroy();       // Destruir sesión actual

// Redirigir a login.html
header("Location: index.php");
exit();
?>
