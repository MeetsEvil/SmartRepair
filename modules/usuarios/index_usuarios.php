<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css"><!--  Barra lateral de submenus -->
    <link rel="stylesheet" href="../../assets/css/usuarios.css"><!-- Estilo general del submenu -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <?php
    // Obtiene el nombre del archivo de la URL
    $currentPage = basename($_SERVER['REQUEST_URI']);
    ?>
    <div class="container">
        <div class="navigation">
            <ul>
                <li class="logo">
                    <img src="../../assets/images/logo_mattel.png" alt="logo">
                </li>
                <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                    <a href="../main/dashboard.php" data-tooltip="Inicio">
                        <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="title">Inicio</span>
                    </a>
                </li>
                <?php
                // usuarios
                $usuariosPages = ['index_usuarios.php', 'crear_usuarios.php', 'editar_usuarios.php', 'ver_usuarios.php'];
                ?>
                <li class="<?php echo in_array($currentPage, $usuariosPages) ? 'active' : ''; ?>">
                    <a href="../usuarios/index_usuarios.php" data-tooltip="Usuario">
                        <span class="icon"><ion-icon name="people-outline"></ion-icon></span>
                        <span class="title">Usuarios</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="showLogoutModal()" data-tooltip="Cerrar Sesión">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </div>

    </div>


    <!-- Agregar barra superior -->
<div style="position:absolute; display:flex; width:100%; height:100%;">
        <div style="margin: auto;">
            <h1>USUARIOS</h1>
        </div>
    </div>

    <!-- Agregar info de tabla -->


</body>

</html>