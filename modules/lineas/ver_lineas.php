<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

// Admin y Técnico pueden ver líneas
if ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico') {
    header("Location: ../main/dashboard.php");
    exit();
}

require_once '../../config/db.php';

$id_linea = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_linea <= 0) {
    header("Location: index_lineas.php");
    exit();
}

// Consultar datos de la línea
$sql = "SELECT 
            l.*,
            p.nombre_planta,
            pr.nombre_prioridad,
            pr.color as color_prioridad,
            (SELECT COUNT(*) FROM maquinas m WHERE m.id_linea = l.id_linea) as total_maquinas,
            (SELECT COUNT(*) FROM maquinas m WHERE m.id_linea = l.id_linea AND m.estado = 'Activa') as maquinas_activas
        FROM lineas l
        INNER JOIN plantas p ON l.id_planta = p.id_planta
        INNER JOIN prioridades pr ON l.id_prioridad = pr.id_prioridad
        WHERE l.id_linea = $id_linea";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    $_SESSION['error'] = "Línea no encontrada";
    header("Location: index_lineas.php");
    exit();
}

$linea = mysqli_fetch_assoc($resultado);

// Obtener máquinas de esta línea
$maquinas_sql = "SELECT codigo_maquina, marca, modelo, estado FROM maquinas WHERE id_linea = $id_linea ORDER BY codigo_maquina";
$maquinas = mysqli_query($conexion, $maquinas_sql);

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Línea - SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <style>
        .view-container {
            margin: 30px auto;
            margin-top: 50px;
            margin-left: 100px;
            margin-bottom: 90px;
            padding: 40px;
            background: white;
            border: 2px solid #932323;
            border-radius: 25px;
            box-shadow: 0 4px 15px rgba(147, 35, 35, 0.2);
            width: calc(95% - 100px);
            min-height: 95px;
            height: auto;
        }

        .view-container:hover {
            box-shadow: 0 6px 20px rgba(147, 35, 35, 0.3);
            transition: all 0.3s ease;
        }

        .view-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .view-title {
            font-size: 2em;
            font-weight: 700;
            color: #932323;
            margin: 0;
        }

        .view-content {
            max-width: 1000px;
            margin: 0 auto;
        }

        .btn-cancel {
            background: linear-gradient(90deg, #6c757d, #495057);
            color: white;
        }

        .btn-cancel {
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid #932323;
        }

        .info-section h3 {
            color: #932323;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            font-weight: 700;
            color: #555;
            min-width: 150px;
            font-size: 0.95em;
        }

        .info-value {
            color: #333;
            font-size: 0.95em;
            flex: 1;
        }

        .badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
        }

        .badge.activa {
            background: #d4edda;
            color: #155724;
        }

        .badge.inactiva {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-prioridad {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
            color: white;
        }

        .maquinas-section {
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .maquinas-section h3 {
            color: #932323;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .maquinas-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }

        .maquina-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .maquina-item:hover {
            box-shadow: 0 2px 8px rgba(147, 35, 35, 0.2);
            transform: translateY(-2px);
        }

        .maquina-codigo {
            font-weight: 700;
            color: #932323;
            margin-bottom: 5px;
        }

        .maquina-info {
            font-size: 0.9em;
            color: #666;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .btn-action {
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-edit {
            background: linear-gradient(90deg, #FF9800, #E65100);
            color: white;
        }

        .btn-edit:hover {
            background: linear-gradient(90deg, #FB8C00, #D84315);
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
            transform: translateY(-2px);
        }

        .btn-back {
            background: linear-gradient(90deg, #6c757d, #495057);
            color: white;
        }

        .btn-back:hover {
            background: linear-gradient(90deg, #5a6268, #3d4349);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 1200px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php
    // Obtiene el nombre del archivo de la URL sin parámetros
    $currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
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

        <div class="view-container">
            <div class="view-header">
                <h2 class="view-title">Información de la Línea</h2>
                <a href="index_lineas.php" class="btn-cancel">
                    <ion-icon name="arrow-back-outline"></ion-icon> Regresar
                </a>
            </div>

            <div class="view-content">
                <div class="info-grid">
                    <!-- Información General -->
                    <div class="info-section">
                        <h3>
                            <ion-icon name="information-circle-outline" style="vertical-align: middle;"></ion-icon>
                            Información General
                        </h3>

                        <div class="info-row">
                            <span class="info-label">ID:</span>
                            <span class="info-value"><?php echo $linea['id_linea']; ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value"><?php echo htmlspecialchars($linea['nombre_linea']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Planta:</span>
                            <span class="info-value"><?php echo htmlspecialchars($linea['nombre_planta']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Prioridad:</span>
                            <span class="info-value">
                                <span class="badge-prioridad" style="background: <?php echo $linea['color_prioridad']; ?>">
                                    <?php echo htmlspecialchars($linea['nombre_prioridad']); ?>
                                </span>
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Estado:</span>
                            <span class="info-value">
                                <span class="badge <?php echo strtolower($linea['estado']); ?>">
                                    <?php echo $linea['estado']; ?>
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="info-section">
                        <h3>
                            <ion-icon name="stats-chart-outline" style="vertical-align: middle;"></ion-icon>
                            Estadísticas
                        </h3>

                        <div class="info-row">
                            <span class="info-label">Total Máquinas:</span>
                            <span class="info-value" style="font-size: 1.2em; font-weight: 700; color: #932323;">
                                <?php echo $linea['total_maquinas']; ?>
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Máquinas Activas:</span>
                            <span class="info-value" style="font-size: 1.2em; font-weight: 700; color: #28a745;">
                                <?php echo $linea['maquinas_activas']; ?>
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Fecha de Creación:</span>
                            <span class="info-value">
                                <?php echo date('d/m/Y H:i', strtotime($linea['created_at'])); ?>
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Última Actualización:</span>
                            <span class="info-value">
                                <?php echo date('d/m/Y H:i', strtotime($linea['updated_at'])); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Descripción -->
                <?php if (!empty($linea['descripcion'])): ?>
                    <div class="info-section" style="margin-bottom: 30px;">
                        <h3>
                            <ion-icon name="document-text-outline" style="vertical-align: middle;"></ion-icon>
                            Descripción
                        </h3>
                        <p style="color: #666; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($linea['descripcion'])); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Máquinas Asignadas -->
                <div class="maquinas-section">
                    <h3>
                        <ion-icon name="hardware-chip-outline" style="vertical-align: middle;"></ion-icon>
                        Máquinas Asignadas (<?php echo $linea['total_maquinas']; ?>)
                    </h3>

                    <?php if (mysqli_num_rows($maquinas) > 0): ?>
                        <div class="maquinas-list">
                            <?php while ($maquina = mysqli_fetch_assoc($maquinas)): ?>
                                <div class="maquina-item">
                                    <div class="maquina-codigo"><?php echo htmlspecialchars($maquina['codigo_maquina']); ?></div>
                                    <div class="maquina-info">
                                        <?php echo htmlspecialchars($maquina['marca'] . ' ' . $maquina['modelo']); ?>
                                    </div>
                                    <div class="maquina-info">
                                        <span style="color: <?php echo ($maquina['estado'] == 'Activa') ? 'green' : 'red'; ?>; font-weight: bold;">
                                            ● <?php echo $maquina['estado']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <ion-icon name="cube-outline" style="font-size: 48px; color: #ccc;"></ion-icon>
                            <p>No hay máquinas asignadas a esta línea</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Botones de acción -->
                <div class="action-buttons">
                    <?php if ($rol == 'Administrador'): ?>
                        <a href="editar_lineas.php?id=<?php echo $linea['id_linea']; ?>" class="btn-action btn-edit">
                            <ion-icon name="create-outline"></ion-icon> Editar Línea
                        </a>
                    <?php endif; ?>
                    <a href="index_lineas.php" class="btn-action btn-back">
                        <ion-icon name="list-outline"></ion-icon> Ver Todas las Líneas
                    </a>
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