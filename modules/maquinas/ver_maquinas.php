<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

// Admin y Técnico pueden ver máquinas
// if ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico') {
//     header("Location: ../main/dashboard.php");
//     exit();
// }

require_once '../../config/db.php';

$id_maquina = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_maquina <= 0) {
    header("Location: index_maquinas.php");
    exit();
}

// Consultar datos de la máquina
$sql = "SELECT 
            m.*,
            p.nombre_planta,
            l.nombre_linea,
            CONCAT(u.nombre, ' ', u.apellido) as creado_por,
            COALESCE(m.imagen, 'imgMaquinas/no-maquina.png') as imagen,
            (SELECT COUNT(*) FROM mantenimientos mt WHERE mt.id_maquina = m.id_maquina) as total_mantenimientos,
            (SELECT COUNT(*) FROM tickets t WHERE t.id_maquina = m.id_maquina) as total_tickets,
            (SELECT COUNT(*) FROM tickets t WHERE t.id_maquina = m.id_maquina AND t.id_estado IN (1,2)) as tickets_activos
        FROM maquinas m
        INNER JOIN plantas p ON m.id_planta = p.id_planta
        INNER JOIN lineas l ON m.id_linea = l.id_linea
        LEFT JOIN usuarios u ON m.created_by = u.id_usuario
        WHERE m.id_maquina = $id_maquina";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    $_SESSION['error'] = "Máquina no encontrada";
    header("Location: index_maquinas.php");
    exit();
}

$maquina = mysqli_fetch_assoc($resultado);

// Obtener últimos mantenimientos
$mantenimientos_sql = "SELECT 
                        mt.*,
                        tm.nombre_tipo,
                        CONCAT(u.nombre, ' ', u.apellido) as tecnico
                    FROM mantenimientos mt
                    INNER JOIN tipos_mantenimiento tm ON mt.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
                    INNER JOIN usuarios u ON mt.id_tecnico_responsable = u.id_usuario
                    WHERE mt.id_maquina = $id_maquina
                    ORDER BY mt.fecha_mantenimiento DESC
                    LIMIT 5";
$mantenimientos = mysqli_query($conexion, $mantenimientos_sql);

// Obtener tickets recientes
$tickets_sql = "SELECT 
                    t.*,
                    e.nombre_estado,
                    pr.nombre_prioridad,
                    pr.color as color_prioridad
                FROM tickets t
                INNER JOIN estados_ticket e ON t.id_estado = e.id_estado
                INNER JOIN prioridades pr ON t.id_prioridad = pr.id_prioridad
                WHERE t.id_maquina = $id_maquina
                ORDER BY t.fecha_creacion DESC
                LIMIT 5";
$tickets = mysqli_query($conexion, $tickets_sql);

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Máquina - SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

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
            max-width: 1200px;
            margin: 0 auto;
        }

        .btn-cancel {
            background: linear-gradient(90deg, #6c757d, #495057);
            color: white;
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
            min-width: 180px;
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

        .badge.mantenimiento {
            background: #fff3cd;
            color: #856404;
        }

        .badge-prioridad {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
            color: white;
        }

        .history-section {
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .history-section h3 {
            color: #932323;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .history-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #932323;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .history-item:hover {
            box-shadow: 0 2px 8px rgba(147, 35, 35, 0.2);
            transform: translateX(5px);
        }

        .history-date {
            font-weight: 700;
            color: #932323;
            margin-bottom: 5px;
        }

        .history-info {
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

        .machine-image-section {
            margin-bottom: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .machine-image-section h3 {
            color: #932323;
            margin-bottom: 20px;
            font-size: 1.3em;
            text-align: center;
        }

        .image-qr-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }

        .machine-image-container {
            text-align: center;
        }

        .machine-image-container img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            border: 3px solid #932323;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(147, 35, 35, 0.2);
            background: white;
            padding: 10px;
            max-height: 400px;
            object-fit: contain;
        }

        .qr-code-container {
            text-align: center;
            padding: 20px;
            background: white;
            border: 3px solid #932323;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(147, 35, 35, 0.2);
        }

        .qr-code-container h4 {
            color: #932323;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        #qrcode {
            display: inline-block;
            padding: 15px;
            background: white;
            border-radius: 8px;
        }

        .qr-code-text {
            margin-top: 15px;
            padding: 10px;
            /* background: #f8f9fa; */
            background: transparent;
            border-radius: 8px;
            font-family: monospace;
            font-size: 1.1em;
            color: #932323;
            font-weight: bold;
            word-break: break-all;
        }

        .qr-download-btn {
            margin-top: 15px;
            padding: 10px 20px;
            background: linear-gradient(90deg, #2196F3, #0D47A1);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .qr-download-btn:hover {
            background: linear-gradient(90deg, #1976D2, #0A3A7A);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
            transform: translateY(-2px);
        }

        @media (max-width: 992px) {
            .image-qr-container {
                grid-template-columns: 1fr;
            }
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

        <div class="view-container">
            <div class="view-header">
                <h2 class="view-title">Información de la Máquina</h2>
                <a href="index_maquinas.php" class="btn-cancel">
                    <ion-icon name="arrow-back-outline"></ion-icon> Regresar
                </a>
            </div>

            <div class="view-content">
                <!-- Imagen y Código QR de la Máquina -->
                <div class="machine-image-section">
                    <h3>
                        <ion-icon name="camera-outline" style="vertical-align: middle;"></ion-icon>
                        Fotografía y Código QR
                    </h3>
                    <div class="image-qr-container">
                        <!-- Imagen de la Máquina -->
                        <div class="machine-image-container">
                            <h4 style="color: #932323; margin-bottom: 15px;">Fotografía</h4>
                            <img src="../../<?php echo htmlspecialchars($maquina['imagen']); ?>" 
                                 alt="Imagen de <?php echo htmlspecialchars($maquina['codigo_maquina']); ?>"
                                 onerror="this.src='../../imgMaquinas/no-maquina.png'">
                        </div>

                        <!-- Código QR -->
                        <div class="qr-code-container">
                            <h4>
                                <ion-icon name="qr-code-outline" style="vertical-align: middle;"></ion-icon>
                                Código QR
                            </h4>
                            <div id="qrcode"></div>
                            <div class="qr-code-text">
                                <?php echo htmlspecialchars( $maquina['codigo_maquina']); ?>
                            </div>
                            <button class="qr-download-btn" onclick="descargarQR()">
                                <ion-icon name="download-outline"></ion-icon>
                                Descargar QR
                            </button>
                        </div>
                    </div>
                </div>

                <div class="info-grid">
                    <!-- Información General -->
                    <div class="info-section">
                        <h3>
                            <ion-icon name="information-circle-outline" style="vertical-align: middle;"></ion-icon>
                            Información General
                        </h3>

                        <div class="info-row">
                            <span class="info-label">ID:</span>
                            <span class="info-value"><?php echo $maquina['id_maquina']; ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Código:</span>
                            <span class="info-value"><strong><?php echo htmlspecialchars($maquina['codigo_maquina']); ?></strong></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Marca:</span>
                            <span class="info-value"><?php echo htmlspecialchars($maquina['marca']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Modelo:</span>
                            <span class="info-value"><?php echo htmlspecialchars($maquina['modelo']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Número de Serie:</span>
                            <span class="info-value"><?php echo htmlspecialchars($maquina['numero_serie']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Estado:</span>
                            <span class="info-value">
                                <span class="badge <?php echo strtolower($maquina['estado']); ?>">
                                    <?php echo $maquina['estado']; ?>
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="info-section">
                        <h3>
                            <ion-icon name="location-outline" style="vertical-align: middle;"></ion-icon>
                            Ubicación
                        </h3>

                        <div class="info-row">
                            <span class="info-label">Planta:</span>
                            <span class="info-value"><?php echo htmlspecialchars($maquina['nombre_planta']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Línea:</span>
                            <span class="info-value"><?php echo htmlspecialchars($maquina['nombre_linea']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Área:</span>
                            <span class="info-value"><?php echo htmlspecialchars($maquina['area']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Fecha Instalación:</span>
                            <span class="info-value">
                                <?php echo $maquina['fecha_instalacion'] ? date('d/m/Y', strtotime($maquina['fecha_instalacion'])) : 'N/A'; ?>
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Registrado por:</span>
                            <span class="info-value"><?php echo htmlspecialchars($maquina['creado_por'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="info-section" style="margin-bottom: 30px;">
                    <h3>
                        <ion-icon name="stats-chart-outline" style="vertical-align: middle;"></ion-icon>
                        Estadísticas
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                            <div style="font-size: 2em; font-weight: 700; color: #932323;"><?php echo $maquina['total_mantenimientos']; ?></div>
                            <div style="color: #666; font-size: 0.9em;">Mantenimientos</div>
                        </div>
                        <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                            <div style="font-size: 2em; font-weight: 700; color: #FF9800;"><?php echo $maquina['tickets_activos']; ?></div>
                            <div style="color: #666; font-size: 0.9em;">Tickets Activos</div>
                        </div>
                        <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                            <div style="font-size: 2em; font-weight: 700; color: #28a745;"><?php echo $maquina['total_tickets']; ?></div>
                            <div style="color: #666; font-size: 0.9em;">Total Tickets</div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <?php if (!empty($maquina['observaciones'])): ?>
                    <div class="info-section" style="margin-bottom: 30px;">
                        <h3>
                            <ion-icon name="document-text-outline" style="vertical-align: middle;"></ion-icon>
                            Observaciones
                        </h3>
                        <p style="color: #666; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($maquina['observaciones'])); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Últimos Mantenimientos -->
                <div class="history-section">
                    <h3>
                        <ion-icon name="construct-outline" style="vertical-align: middle;"></ion-icon>
                        Últimos Mantenimientos (<?php echo mysqli_num_rows($mantenimientos); ?>)
                    </h3>

                    <?php if (mysqli_num_rows($mantenimientos) > 0): ?>
                        <?php while ($mant = mysqli_fetch_assoc($mantenimientos)): ?>
                            <div class="history-item">
                                <div class="history-date">
                                    <?php echo date('d/m/Y H:i', strtotime($mant['fecha_mantenimiento'])); ?> - 
                                    <span style="color: #666;"><?php echo $mant['nombre_tipo']; ?></span>
                                </div>
                                <div class="history-info">
                                    <strong>Técnico:</strong> <?php echo htmlspecialchars($mant['tecnico']); ?>
                                </div>
                                <div class="history-info">
                                    <?php echo htmlspecialchars(substr($mant['actividades_realizadas'], 0, 100)) . '...'; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <ion-icon name="construct-outline" style="font-size: 48px; color: #ccc;"></ion-icon>
                            <p>No hay mantenimientos registrados</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tickets Recientes -->
                <div class="history-section">
                    <h3>
                        <ion-icon name="document-text-outline" style="vertical-align: middle;"></ion-icon>
                        Tickets Recientes (<?php echo mysqli_num_rows($tickets); ?>)
                    </h3>

                    <?php if (mysqli_num_rows($tickets) > 0): ?>
                        <?php while ($ticket = mysqli_fetch_assoc($tickets)): ?>
                            <div class="history-item">
                                <div class="history-date">
                                    <?php echo $ticket['codigo_ticket']; ?> - 
                                    <span class="badge-prioridad" style="background: <?php echo $ticket['color_prioridad']; ?>; padding: 3px 10px; font-size: 0.8em;">
                                        <?php echo $ticket['nombre_prioridad']; ?>
                                    </span>
                                </div>
                                <div class="history-info">
                                    <strong>Estado:</strong> <?php echo $ticket['nombre_estado']; ?> | 
                                    <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?>
                                </div>
                                <div class="history-info">
                                    <?php echo htmlspecialchars(substr($ticket['descripcion_falla'], 0, 100)) . '...'; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <ion-icon name="document-text-outline" style="font-size: 48px; color: #ccc;"></ion-icon>
                            <p>No hay tickets registrados</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Botones de acción -->
                <div class="action-buttons">
                    <?php if ($rol == 'Administrador' || $rol == 'Técnico'): ?>
                        <a href="editar_maquinas.php?id=<?php echo $maquina['id_maquina']; ?>" class="btn-action btn-edit">
                            <ion-icon name="create-outline"></ion-icon> Editar Máquina
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($rol == 'Administrador' || $rol == 'Técnico' || $rol == 'Operario'): ?>
                        <a href="../tickets/crear_ticket_maquina.php?id_maquina=<?php echo $maquina['id_maquina']; ?>" class="btn-action" style="background: linear-gradient(90deg, #FF5722, #D84315); color: white;">
                            <ion-icon name="alert-circle-outline"></ion-icon> Reportar Falla
                        </a>
                    <?php endif; ?>
                    
                    <a href="index_maquinas.php" class="btn-action btn-back">
                        <ion-icon name="list-outline"></ion-icon> Ver Todas las Máquinas
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

    <script>
        // Generar código QR al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const codigoQR = '<?php echo htmlspecialchars($maquina['codigoQR'] ?? $maquina['id_maquina'] . $maquina['codigo_maquina']); ?>';
            
            // Generar QR con QRCode.js
            new QRCode(document.getElementById('qrcode'), {
                text: codigoQR,
                width: 200,
                height: 200,
                colorDark: '#932323',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        });

        // Función para descargar el código QR
        function descargarQR() {
            const qrcodeElement = document.getElementById('qrcode');
            const canvas = qrcodeElement.querySelector('canvas');
            const img = qrcodeElement.querySelector('img');
            
            if (canvas) {
                // Si es canvas, convertir a imagen
                const url = canvas.toDataURL('image/png');
                const a = document.createElement('a');
                a.href = url;
                a.download = 'QR-<?php echo htmlspecialchars($maquina['codigo_maquina']); ?>.png';
                a.click();
            } else if (img) {
                // Si es imagen, descargar directamente
                const a = document.createElement('a');
                a.href = img.src;
                a.download = 'QR-<?php echo htmlspecialchars($maquina['codigo_maquina']); ?>.png';
                a.click();
            } else {
                alert('No se pudo generar el código QR para descargar.');
            }
        }
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>
