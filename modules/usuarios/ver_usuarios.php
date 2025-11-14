<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

// Solo administradores pueden ver usuarios
if ($_SESSION['rol'] !== 'Administrador') {
    header("Location: index_usuarios.php");
    exit();
}

require_once '../../config/db.php';

// Obtener ID del usuario
$id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_usuario <= 0) {
    header("Location: index_usuarios.php");
    exit();
}

// Consultar datos del usuario
$sql = "SELECT 
            u.*,
            r.nombre_rol,
            p.nombre_planta
        FROM usuarios u
        INNER JOIN roles r ON u.id_rol = r.id_rol
        LEFT JOIN plantas p ON u.id_planta = p.id_planta
        WHERE u.id_usuario = $id_usuario";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    $_SESSION['error'] = "Usuario no encontrado";
    header("Location: index_usuarios.php");
    exit();
}

$usuario = mysqli_fetch_assoc($resultado);
mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Usuario - SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <style>
        /* ================= CONTENEDOR DE VISUALIZACIÓN ================= */
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

        /* Header */
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

        /* Contenido */
        .view-content {
            max-width: 1000px;
            margin: 0 auto;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 35px;
            margin-bottom: 30px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border-left: 4px solid #932323;
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
            padding-right: 5px;
            margin-right: 10px;
        }

        .info-value {
            color: #333;
            font-size: 0.95em;
            flex: 1;
        }

        .info-value.email {
            color: #2196F3;
            text-decoration: none;
        }

        .info-value.email:hover {
            text-decoration: underline;
        }

        /* Badge de estado */
        .badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
        }

        .badge.activo {
            background: #d4edda;
            color: #155724;
        }

        .badge.inactivo {
            background: #f8d7da;
            color: #721c24;
        }

        /* Botones de acción */
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

        .btn-cancel {
            background: linear-gradient(90deg, #6c757d, #495057);
            color: white;
        }

        .btn-cancel:hover {
            background: linear-gradient(90deg, #5a6268, #3d4349);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
            transform: translateY(-2px);
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

        /* Responsive */
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

                <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                    <a href="../main/dashboard.php" data-tooltip="Inicio">
                        <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="title">Inicio</span>
                    </a>
                </li>

                <?php if ($rol == 'Administrador' || $rol == 'Técnico'): ?>
                    <?php $maquinasPages = ['index_maquinas.php', 'crear_maquinas.php', 'editar_maquinas.php', 'ver_maquinas.php']; ?>
                    <li class="<?php echo in_array($currentPage, $maquinasPages) ? 'active' : ''; ?>">
                        <a href="../maquinas/index_maquinas.php" data-tooltip="Máquinas">
                            <span class="icon"><ion-icon name="hardware-chip-outline"></ion-icon></span>
                            <span class="title">Máquinas</span>
                        </a>
                    </li>

                    <?php $lineasPages = ['index_lineas.php', 'crear_lineas.php', 'editar_lineas.php', 'ver_lineas.php']; ?>
                    <li class="<?php echo in_array($currentPage, $lineasPages) ? 'active' : ''; ?>">
                        <a href="../lineas/index_lineas.php" data-tooltip="Líneas">
                            <span class="icon"><ion-icon name="git-network-outline"></ion-icon></span>
                            <span class="title">Líneas</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($rol == 'Administrador' || $rol == 'Técnico' || $rol == 'Operario'): ?>
                    <?php $mantenimientoPages = ['index_mantenimiento.php', 'crear_mantenimiento.php', 'editar_mantenimiento.php', 'ver_mantenimiento.php']; ?>
                    <li class="<?php echo in_array($currentPage, $mantenimientoPages) ? 'active' : ''; ?>">
                        <a href="../mantenimiento/index_mantenimiento.php" data-tooltip="Mantenimiento">
                            <span class="icon"><ion-icon name="construct-outline"></ion-icon></span>
                            <span class="title">Mantenimiento</span>
                        </a>
                    </li>

                    <?php $ticketsPages = ['index_tickets.php', 'crear_tickets.php', 'editar_tickets.php', 'ver_tickets.php']; ?>
                    <li class="<?php echo in_array($currentPage, $ticketsPages) ? 'active' : ''; ?>">
                        <a href="../tickets/index_tickets.php" data-tooltip="Tickets">
                            <span class="icon"><ion-icon name="document-text-outline"></ion-icon></span>
                            <span class="title">Tickets</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($rol == 'Administrador'): ?>
                    <?php $usuariosPages = ['index_usuarios.php', 'crear_usuarios.php', 'editar_usuarios.php', 'ver_usuarios.php']; ?>
                    <li class="<?php echo in_array($currentPage, $usuariosPages) ? 'active' : ''; ?>">
                        <a href="../usuarios/index_usuarios.php" data-tooltip="Usuarios">
                            <span class="icon"><ion-icon name="people-outline"></ion-icon></span>
                            <span class="title">Usuarios</span>
                        </a>
                    </li>
                <?php endif; ?>

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
                <h2 class="view-title">Información del Usuario</h2>
                <a href="index_usuarios.php" class="btn-cancel">
                    <ion-icon name="arrow-back-outline"></ion-icon> Regresar
                </a>

            </div>

            <div class="view-content">
                <div class="info-grid">
                    <!-- SECCIÓN IZQUIERDA: Información Personal -->
                    <div class="info-section">
                        <h3 style="color: #932323; margin-bottom: 20px; font-size: 1.3em;">
                            <ion-icon name="person-outline" style="vertical-align: middle;"></ion-icon>
                            Información Personal
                        </h3>

                        <div class="info-row">
                            <span class="info-label">ID:</span>
                            <span class="info-value"><?php echo $usuario['id_usuario']; ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Nombre Completo:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Correo:</span>
                            <a href="mailto:<?php echo $usuario['email']; ?>" class="info-value email">
                                <?php echo htmlspecialchars($usuario['email']); ?>
                            </a>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-value">
                                <?php echo $usuario['telefono'] ? htmlspecialchars($usuario['telefono']) : 'No especificado'; ?>
                            </span>
                        </div>
                    </div>

                    <!-- SECCIÓN DERECHA: Información del Sistema -->
                    <div class="info-section">
                        <h3 style="color: #932323; margin-bottom: 20px; font-size: 1.3em;">
                            <ion-icon name="settings-outline" style="vertical-align: middle;"></ion-icon>
                            Información del Sistema
                        </h3>

                        <div class="info-row">
                            <span class="info-label">Usuario:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['usuario']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Rol:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['nombre_rol']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Planta Asignada:</span>
                            <span class="info-value">
                                <?php echo $usuario['nombre_planta'] ? htmlspecialchars($usuario['nombre_planta']) : 'Sin asignar'; ?>
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Estado:</span>
                            <span class="info-value">
                                <span class="badge <?php echo strtolower($usuario['estado']); ?>">
                                    <?php echo $usuario['estado']; ?>
                                </span>
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Fecha de Registro:</span>
                            <span class="info-value">
                                <?php echo date('d/m/Y H:i', strtotime($usuario['created_at'])); ?>
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Última Actualización:</span>
                            <span class="info-value">
                                <?php echo date('d/m/Y H:i', strtotime($usuario['updated_at'])); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="action-buttons">
                    <a href="editar_usuarios.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn-action btn-edit">
                        <ion-icon name="create-outline"></ion-icon> Editar Usuario
                    </a>
                    <a href="index_usuarios.php" class="btn-action btn-cancel">
                        <ion-icon name="list-outline"></ion-icon> Ver Todos los Usuarios
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de cerrar sesión -->
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