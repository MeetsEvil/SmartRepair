<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

$id_mantenimiento = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_mantenimiento <= 0) {
    header("Location: index_mantenimiento.php");
    exit();
}

// Consultar datos del mantenimiento
$sql = "SELECT 
            m.*,
            maq.codigo_maquina,
            maq.marca,
            maq.modelo,
            l.nombre_linea,
            p.nombre_planta,
            tm.nombre_tipo,
            CONCAT(u_tecnico.nombre, ' ', u_tecnico.apellido) as tecnico_responsable,
            CONCAT(u_creador.nombre, ' ', u_creador.apellido) as creado_por
        FROM mantenimientos m
        INNER JOIN maquinas maq ON m.id_maquina = maq.id_maquina
        INNER JOIN lineas l ON maq.id_linea = l.id_linea
        INNER JOIN plantas p ON l.id_planta = p.id_planta
        INNER JOIN tipos_mantenimiento tm ON m.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
        INNER JOIN usuarios u_tecnico ON m.id_tecnico_responsable = u_tecnico.id_usuario
        LEFT JOIN usuarios u_creador ON m.created_by = u_creador.id_usuario
        WHERE m.id_mantenimiento = $id_mantenimiento";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    $_SESSION['mensaje'] = "Mantenimiento no encontrado";
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: index_mantenimiento.php");
    exit();
}

$mantenimiento = mysqli_fetch_assoc($resultado);
$rol = $_SESSION['rol'];

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Mantenimiento - SmartRepair</title>
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

        .btn-back {
            background: linear-gradient(90deg, #6c757d, #495057);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: linear-gradient(90deg, #5a6268, #3d4349);
            transform: translateY(-2px);
        }

        .btn-edit {
            background: linear-gradient(90deg, #FF9800, #E65100);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            margin-right: 10px;
        }

        .btn-edit:hover {
            background: linear-gradient(90deg, #F57C00, #D84315);
            transform: translateY(-2px);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid #932323;
        }

        .info-section.full-width {
            grid-column: 1 / -1;
        }

        .info-section h3 {
            margin: 0 0 20px 0;
            color: #932323;
            font-size: 1.3em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            display: block;
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        .info-value {
            color: #333;
            font-size: 1.05em;
        }

        .tipo-badge {
            padding: 6px 15px;
            border-radius: 12px;
            font-size: 0.9em;
            font-weight: 600;
            color: white;
            display: inline-block;
        }

        .tipo-preventivo {
            background: #10B981;
        }

        .tipo-correctivo {
            background: #F59E0B;
        }

        .tipo-predictivo {
            background: #3B82F6;
        }

        .tipo-otro {
            background: #6B7280;
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

                <?php if ($rol == 'Administrador' || $rol == 'Técnico' || $rol == 'Operario'): ?>
                    <!-- MÁQUINAS -->
                    <?php $maquinasPages = ['index_maquinas.php', 'crear_maquinas.php', 'editar_maquinas.php', 'ver_maquinas.php']; ?>
                    <li class="<?php echo in_array($currentPage, $maquinasPages) ? 'active' : ''; ?>">
                        <a href="../maquinas/index_maquinas.php" data-tooltip="Máquinas">
                            <span class="icon"><ion-icon name="hardware-chip-outline"></ion-icon></span>
                            <span class="title">Máquinas</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($rol == 'Administrador' || $rol == 'Técnico') : ?>
                    <!-- LÍNEAS -->
                    <?php $lineasPages = ['index_lineas.php', 'crear_lineas.php', 'editar_lineas.php', 'ver_lineas.php']; ?>
                    <li class="<?php echo in_array($currentPage, $lineasPages) ? 'active' : ''; ?>">
                        <a href="../lineas/index_lineas.php" data-tooltip="Líneas">
                            <span class="icon"><ion-icon name="git-network-outline"></ion-icon></span>
                            <span class="title">Líneas</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($rol == 'Administrador' || $rol == 'Técnico') : ?>
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
                <h2 class="view-title">Detalle del Mantenimiento</h2>
                <div>
                    <?php if ($rol == 'Administrador' || $rol == 'Técnico'): ?>
                        <a href="editar_mantenimiento.php?id=<?php echo $mantenimiento['id_mantenimiento']; ?>" class="btn-edit">
                            <ion-icon name="create-outline"></ion-icon> Editar
                        </a>
                    <?php endif; ?>
                    <?php if ($rol == 'Operario'): ?>
                        <a href="../maquinas/ver_maquinas.php?id=<?php echo $mantenimiento['id_maquina']; ?>" class="btn-back">
                            <ion-icon name="arrow-back-outline"></ion-icon> Volver a Máquina
                        </a>
                    <?php else: ?>
                        <a href="index_mantenimiento.php" class="btn-back">
                            <ion-icon name="arrow-back-outline"></ion-icon> Volver
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-grid">
                <!-- Información General -->
                <div class="info-section">
                    <h3><ion-icon name="information-circle-outline"></ion-icon> Información General</h3>
                    <div class="info-item">
                        <span class="info-label">ID Mantenimiento:</span>
                        <span class="info-value">#<?php echo htmlspecialchars($mantenimiento['id_mantenimiento']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tipo de Mantenimiento:</span>
                        <?php
                        $tipo_class = 'tipo-otro';
                        if ($mantenimiento['nombre_tipo'] == 'Preventivo') $tipo_class = 'tipo-preventivo';
                        elseif ($mantenimiento['nombre_tipo'] == 'Correctivo') $tipo_class = 'tipo-correctivo';
                        elseif ($mantenimiento['nombre_tipo'] == 'Predictivo') $tipo_class = 'tipo-predictivo';
                        ?>
                        <span class="tipo-badge <?php echo $tipo_class; ?>"><?php echo htmlspecialchars($mantenimiento['nombre_tipo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha y Hora:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($mantenimiento['fecha_mantenimiento'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Técnico Responsable:</span>
                        <span class="info-value"><?php echo htmlspecialchars($mantenimiento['tecnico_responsable']); ?></span>
                    </div>
                </div>

                <!-- Información de la Máquina -->
                <div class="info-section">
                    <h3><ion-icon name="hardware-chip-outline"></ion-icon> Información de la Máquina</h3>
                    <div class="info-item">
                        <span class="info-label">Código de Máquina:</span>
                        <span class="info-value"><?php echo htmlspecialchars($mantenimiento['codigo_maquina']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Marca:</span>
                        <span class="info-value"><?php echo htmlspecialchars($mantenimiento['marca']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Modelo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($mantenimiento['modelo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Línea:</span>
                        <span class="info-value"><?php echo htmlspecialchars($mantenimiento['nombre_linea']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Planta:</span>
                        <span class="info-value"><?php echo htmlspecialchars($mantenimiento['nombre_planta']); ?></span>
                    </div>
                </div>

                <!-- Actividades Realizadas -->
                <div class="info-section">
                    <h3><ion-icon name="construct-outline"></ion-icon> Actividades Realizadas</h3>
                    <div class="info-item">
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($mantenimiento['actividades_realizadas'])); ?></span>
                    </div>
                </div>

                <!-- Información de Registro -->
                <div class="info-section">
                    <h3><ion-icon name="time-outline"></ion-icon> Información de Registro</h3>
                    <div class="info-item">
                        <span class="info-label">Registrado por:</span>
                        <span class="info-value"><?php echo htmlspecialchars($mantenimiento['creado_por'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha de Registro:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($mantenimiento['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Última Actualización:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($mantenimiento['updated_at'])); ?></span>
                    </div>
                </div>

                <!-- Repuestos Utilizados -->
                <?php if (!empty($mantenimiento['repuestos_utilizados'])): ?>
                    <div class="info-section full-width">
                        <h3><ion-icon name="cube-outline"></ion-icon> Repuestos Utilizados</h3>
                        <div class="info-item">
                            <span class="info-value"><?php echo nl2br(htmlspecialchars($mantenimiento['repuestos_utilizados'])); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Observaciones -->
                <?php if (!empty($mantenimiento['observaciones'])): ?>
                    <div class="info-section full-width">
                        <h3><ion-icon name="document-text-outline"></ion-icon> Observaciones</h3>
                        <div class="info-item">
                            <span class="info-value"><?php echo nl2br(htmlspecialchars($mantenimiento['observaciones'])); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
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
