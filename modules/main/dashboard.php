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
    <style>
        /* ================= Dashboard Container ================= */
        .dashboard-container {
            margin: 30px auto;
            margin-top: 50px;
            margin-left: 100px;
            margin-bottom: 90px;
            padding: 30px;
            border: 1px solid #000;
            /* Degradado y bordes */
            background: white;
            border: 2px solid #adabab;
            border-radius: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);

            /* Dimensiones */
            width: calc(95% - 100px);
            min-height: 95px;
            height: 740px;

            /* Configuración del layout interno */
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .dashboard-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        /* Header del Dashboard */
        .dashboard-header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            flex-shrink: 0;
        }

        .dashboard-title {
            font-size: 2.3em;
            font-weight: 700;
            color: #000000;
            margin: 0;
        }

        /* Grid de estadísticas - 2x2 */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 30px;
            flex: 1;
            width: 100%;
        }

        /* Tarjeta individual de estadística */
        .stat-card {
            background: linear-gradient(135deg, #E63946 0%, #A4161A 100%);
            border-radius: 15px;
            padding: 40px 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
            pointer-events: none;
        }

        .stat-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        }

        /* Ícono de la tarjeta */
        .stat-icon {
            flex-shrink: 0;
            width: 140px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .stat-icon ion-icon {
            width: 130px;
            height: 130px;
            color: rgba(255, 255, 255, 0.95);
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
            stroke-width: 1.5px;
        }

        /* Información de la estadística */
        .stat-info {
            flex: 1;
            color: white;
            text-align: right;
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
        }

        .stat-number {
            font-size: 7em;
            font-weight: 900;
            line-height: 0.9;
            margin-bottom: 8px;
            text-shadow: 4px 4px 8px rgba(0, 0, 0, 0.4);
            letter-spacing: -3px;
        }

        .stat-label {
            font-size: 1.6em;
            font-weight: 700;
            opacity: 0.98;
            letter-spacing: 0.3px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
            max-width: 100%;
        }

        /* Responsive - Tablets y menores */
        @media (max-width: 1200px) {
            .dashboard-container {
                margin-left: 50px;
                width: calc(95% - 50px);
                padding: 35px;
            }

            .stats-grid {
                gap: 20px;
            }

            .stat-card {
                padding: 35px 30px;
                min-height: 200px;
            }

            .stat-icon {
                width: 90px;
                height: 90px;
            }

            .stat-icon ion-icon {
                width: 75px;
                height: 75px;
            }

            .stat-number {
                font-size: 4em;
            }

            .stat-label {
                font-size: 1.2em;
            }
        }

        @media (max-width: 991px) {
            .dashboard-container {
                margin-left: 20px;
                margin-right: 20px;
                width: calc(100% - 40px);
                padding: 30px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .stat-card {
                min-height: 150px;
                padding: 30px 25px;
            }

            .stat-number {
                font-size: 3.5em;
            }

            .stat-label {
                font-size: 1.15em;
            }
        }

        /* Responsive - Móviles */
        @media (max-width: 480px) {
            .dashboard-container {
                margin: 20px 10px;
                width: calc(100% - 20px);
                padding: 25px 20px;
            }

            .dashboard-header {
                margin-bottom: 25px;
                padding-bottom: 15px;
            }

            .dashboard-title {
                font-size: 1.8em;
            }

            .stats-grid {
                gap: 15px;
            }

            .stat-card {
                flex-direction: row;
                padding: 25px 20px;
                min-height: 130px;
                gap: 20px;
            }

            .stat-icon {
                width: 70px;
                height: 70px;
            }

            .stat-icon ion-icon {
                width: 60px;
                height: 60px;
            }

            .stat-info {
                text-align: right;
            }

            .stat-number {
                font-size: 2.8em;
            }

            .stat-label {
                font-size: 0.95em;
            }
        }
    </style>
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

                <?php if ($rol == 'Administrador' || $rol == 'Técnico'): ?>
                    <!-- MÁQUINAS -->
                    <?php $maquinasPages = ['index_maquinas.php', 'crear_maquinas.php', 'editar_maquinas.php', 'ver_maquinas.php']; ?>
                    <li class="<?php echo in_array($currentPage, $maquinasPages) ? 'active' : ''; ?>">
                        <a href="../maquinas/index_maquinas.php" data-tooltip="Máquinas">
                            <span class="icon"><ion-icon name="hardware-chip-outline"></ion-icon></span>
                            <span class="title">Máquinas</span>
                        </a>
                    </li>

                    <!-- LÍNEAS -->
                    <?php $lineasPages = ['index_lineas.php', 'crear_lineas.php', 'editar_lineas.php', 'ver_lineas.php']; ?>
                    <li class="<?php echo in_array($currentPage, $lineasPages) ? 'active' : ''; ?>">
                        <a href="../lineas/index_lineas.php" data-tooltip="Líneas">
                            <span class="icon"><ion-icon name="git-network-outline"></ion-icon></span>
                            <span class="title">Líneas</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($rol == 'Administrador' || $rol == 'Técnico' || $rol == 'Operario'): ?>
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

        <div class="dashboard-container">
            
            <div class="stats-grid">
                <!-- Tarjeta: Máquinas activas -->
                <div class="stat-card">
                    <div class="stat-icon stat-icon-maquinasactivas">
                        <ion-icon name="hardware-chip-outline"></ion-icon>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_maquinas; ?></div>
                        <div class="stat-label">Máquinas activas</div>
                    </div>
                </div>

                <!-- Tarjeta: Tickets activos -->
                <div class="stat-card">
                    <div class="stat-icon stat-icon-ticketactivo">
                        <ion-icon name="document-text-outline"></ion-icon>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_tickets_activos; ?></div>
                        <div class="stat-label">Tickets activos</div>
                    </div>
                </div>

                <!-- Tarjeta: Técnicos activos -->
                <div class="stat-card">
                    <div class="stat-icon stat-icon-tecnicos">
                        <ion-icon name="people-outline"></ion-icon>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_tecnicos; ?></div>
                        <div class="stat-label">Técnicos activos</div>
                    </div>
                </div>

                <!-- Tarjeta: Tickets finalizados -->
                <div class="stat-card">
                    <div class="stat-icon stat-icon-finalizados">
                        <ion-icon name="checkmark-done-outline"></ion-icon>
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
                <h2>Información</h2>
            </div>
            <div class="modal-body">
                <h3><?php echo htmlspecialchars($_SESSION["usuarioingresando"]); ?></h3>
                <p></p>
                <div class="socialMedia">
                    <a class="socialIcon" href="https://github.com/MeetsEvil" target="_blank"><i class="fab fa-github"></i></a>
                    <a class="socialIcon" href="https://www.linkedin.com/in/orlandojgarciap-17a612289/" target="_blank"><i class="fab fa-linkedin"></i></a>
                    <a class="socialIcon" href="mailto:orlandojgarciap@gmail.com" target="_blank"><i class="fas fa-envelope"></i></a>
                </div>
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