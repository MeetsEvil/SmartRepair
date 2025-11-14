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
    <link rel="stylesheet" href="../../assets/css/dashboard.css"><!-- Estilo general del submenu -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Extensión Botones -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <!-- Dependencias para exportar -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">
</head>

<body>
    <?php
    // Obtiene el nombre del archivo de la URL
    $currentPage = basename($_SERVER['REQUEST_URI']);
    $rol = $_SESSION['rol'];
    ?>

    <div class="container">
        <div class="navigation">
            <ul>
                <li class="logo">
                    <img src="../../assets/images/logo_mattel.png" alt="logo">
                </li>

                <!-- DASHBOARD -->
                <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                    <a href="../main/dashboard.php" data-tooltip="Inicio">
                        <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="title">Inicio</span>
                    </a>
                </li>

                <?php if ($rol == 'Administrador' || $rol == 'Técnico' || $rol = "Operario"): ?>
                    <!-- MÁQUINAS -->
                    <?php $maquinasPages = ['index_maquinas.php', 'crear_maquinas.php', 'editar_maquinas.php', 'ver_maquinas.php']; ?>
                    <li class="<?php echo in_array($currentPage, $maquinasPages) ? 'active' : ''; ?>">
                        <a href="../maquinas/index_maquinas.php" data-tooltip="Máquinas">
                            <span class="icon"><ion-icon name="hardware-chip-outline"></ion-icon></span>
                            <span class="title">Máquinas</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($rol == 'Administrador' || $rol == 'Técnico'): ?>

                    <!-- LÍNEAS -->
                    <?php $lineasPages = ['index_lineas.php', 'crear_lineas.php', 'editar_lineas.php', 'ver_lineas.php']; ?>
                    <li class="<?php echo in_array($currentPage, $lineasPages) ? 'active' : ''; ?>">
                        <a href="../lineas/index_lineas.php" data-tooltip="Líneas">
                            <span class="icon"><ion-icon name="git-network-outline"></ion-icon></span>
                            <span class="title">Líneas</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($rol == 'Administrador' || $rol == 'Técnico'): ?>
                    <!-- MANTENIMIENTO -->
                    <?php $mantenimientoPages = ['index_mantenimiento.php', 'crear_mantenimiento.php', 'editar_mantenimiento.php', 'ver_mantenimiento.php']; ?>
                    <li class="<?php echo in_array($currentPage, $mantenimientoPages) ? 'active' : ''; ?>">
                        <a href="../mantenimiento/index_mantenimiento.php" data-tooltip="Mantenimiento">
                            <span class="icon"><ion-icon name="construct-outline"></ion-icon></span>
                            <span class="title">Mantenimiento</span>
                        </a>
                    </li>

                    <!-- TICKETS -->
                    <?php $ticketsPages = ['index_tickets.php', 'crear_tickets.php', 'editar_tickets.php', 'ver_tickets.php']; ?>
                    <li class="<?php echo in_array($currentPage, $ticketsPages) ? 'active' : ''; ?>">
                        <a href="../tickets/index_tickets.php" data-tooltip="Tickets">
                            <span class="icon"><ion-icon name="document-text-outline"></ion-icon></span>
                            <span class="title">Tickets</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($rol == 'Administrador'): ?>
                    <!-- USUARIOS -->
                    <?php $usuariosPages = ['index_usuarios.php', 'crear_usuarios.php', 'editar_usuarios.php', 'ver_usuarios.php']; ?>
                    <li class="<?php echo in_array($currentPage, $usuariosPages) ? 'active' : ''; ?>">
                        <a href="../usuarios/index_usuarios.php" data-tooltip="Usuarios">
                            <span class="icon"><ion-icon name="people-outline"></ion-icon></span>
                            <span class="title">Usuarios</span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- CERRAR SESIÓN -->
                <li>
                    <a href="#" onclick="showLogoutModal()" data-tooltip="Cerrar Sesión">
                        <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
                        <span class="title">Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </div>

    </div>



    <div class="main">
        <div class="topbar">
            <div class="toggle">
                <ion-icon name="menu-outline"></ion-icon>
            </div>
            <h2 class="page-title">Smart Repair</h2>
            <div class="user-box">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['usuarioingresando']); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($_SESSION['rol']); ?></div>
                </div>
                <button class="info-btn" onclick="mostrarInfo()">
                    <ion-icon name="information-outline"></ion-icon>
                </button>
            </div>
        </div>

        <?php
        // Incluir conexión a la base de datos
        require_once '../../config/db.php';

        // Inicializar variables con valores por defecto
        $total_maquinas = 0;
        $total_tickets_activos = 0;
        $total_tecnicos = 0;
        $total_tickets_finalizados = 0;

        // Consulta para contar máquinas activas
        $query_maquinas = "SELECT COUNT(*) as total FROM maquinas WHERE estado = 'Activa'";
        $result_maquinas = mysqli_query($conexion, $query_maquinas);
        if ($result_maquinas) {
            $row = mysqli_fetch_assoc($result_maquinas);
            $total_maquinas = $row['total'];
        }

        // Consulta para contar tickets activos (Pendiente o En progreso)
        $query_tickets_activos = "SELECT COUNT(*) as total FROM tickets WHERE id_estado IN (1, 2)";
        $result_tickets_activos = mysqli_query($conexion, $query_tickets_activos);
        if ($result_tickets_activos) {
            $row = mysqli_fetch_assoc($result_tickets_activos);
            $total_tickets_activos = $row['total'];
        }

        // Consulta para contar técnicos activos
        $query_tecnicos = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = 2 AND estado = 'Activo'";
        $result_tecnicos = mysqli_query($conexion, $query_tecnicos);
        if ($result_tecnicos) {
            $row = mysqli_fetch_assoc($result_tecnicos);
            $total_tecnicos = $row['total'];
        }

        // Consulta para contar tickets finalizados
        $query_tickets_finalizados = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = 4";
        $result_tickets_finalizados = mysqli_query($conexion, $query_tickets_finalizados);
        if ($result_tickets_finalizados) {
            $row = mysqli_fetch_assoc($result_tickets_finalizados);
            $total_tickets_finalizados = $row['total'];
        }
        ?>

        <div class="dashboard-content">
            <div class="stats-grid">
                <!-- Tarjeta: Máquinas activas -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="stat-icon-maquinasactivas"><ion-icon
                                name="hardware-chip-outline"></ion-icon></span>
                        <!-- <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                            <path d="M12 8v8m-4-4h8"/>
                        </svg> -->
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_maquinas; ?></div>
                        <div class="stat-label">Máquinas activas</div>
                    </div>
                </div>

                <!-- Tarjeta: Tickets activos -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="stat-icon-ticketactivo"><ion-icon name="document-text-outline"></ion-icon></span>
                        <!-- <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4m0 4h.01"/>
                        </svg> -->
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_tickets_activos; ?></div>
                        <div class="stat-label">Tickets activos</div>
                    </div>
                </div>

                <!-- Tarjeta: Técnicos activos -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                            <path d="M16 11l2 2 4-4" />
                        </svg>

                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_tecnicos; ?></div>
                        <div class="stat-label">Técnicos activos</div>
                    </div>
                </div>

                <!-- Tarjeta: Tickets finalizados -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4" />
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                        </svg>

                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_tickets_finalizados; ?></div>
                        <div class="stat-label">Tickets finalizados</div>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <div id="contactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-btn" id="closeContact">&times;</span>
                <h2>Información de Contacto</h2>
            </div>
            <div class="modal-body">
                <h3><?php echo '' . $_SESSION["usuarioingresando"] . ''; ?></h3>
                <p></p>
                <!-- <div class="socialMedia">
                    <a class="socialIcon" href="https://github.com/MeetsEvil" target="_blank"><i class="fab fa-github"></i></a>
                    <a class="socialIcon" href="https://www.linkedin.com/in/orlandojgarciap-17a612289/" target="_blank"><i class="fab fa-linkedin"></i></a>
                    <a class="socialIcon" href="mailto:orlandojgarciap@gmail.com" target="_blank"><i class="fas fa-envelope"></i></a>
                </div> -->
            </div>
        </div>
    </div>


    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-btn">&times;</span>
                <h2>Cerrar sesión</h2>
            </div>
            <div class="modal-body">
                <p>¿Confirmas que deseas cerrar sesión?</p>
            </div>
            <div class="modal-footer">
                <button id="cancelBtn" class="btn-cancel">Cancelar</button>
                <a href="../main/logout.php" class="btn-confirm">Cerrar Sesión</a>
            </div>
        </div>
    </div>


    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>