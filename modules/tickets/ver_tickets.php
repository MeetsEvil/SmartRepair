<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

// Obtener ID del ticket
$id_ticket = isset($_GET['id']) ? mysqli_real_escape_string($conexion, $_GET['id']) : null;

if (!$id_ticket) {
    header("Location: index_tickets.php");
    exit();
}

// Obtener datos completos del ticket
$query = "SELECT t.*, 
            m.codigo_maquina, m.marca, m.modelo,
            l.nombre_linea, 
            p.nombre_planta,
            pr.nombre_prioridad,
            e.nombre_estado,
            tf.nombre_tipo_falla, tf.descripcion as descripcion_tipo_falla,
            CONCAT(u_reporta.nombre, ' ', u_reporta.apellido) as reportado_por,
            u_reporta.email as email_reporta,
            CONCAT(u_tecnico.nombre, ' ', u_tecnico.apellido) as tecnico_responsable,
            u_tecnico.email as email_tecnico
            FROM tickets t
            INNER JOIN maquinas m ON t.id_maquina = m.id_maquina
            INNER JOIN lineas l ON m.id_linea = l.id_linea
            INNER JOIN plantas p ON l.id_planta = p.id_planta
            INNER JOIN prioridades pr ON t.id_prioridad = pr.id_prioridad
            INNER JOIN estados_ticket e ON t.id_estado = e.id_estado
            INNER JOIN tipos_falla tf ON t.id_tipo_falla = tf.id_tipo_falla
            INNER JOIN usuarios u_reporta ON t.id_usuario_reporta = u_reporta.id_usuario
            LEFT JOIN usuarios u_tecnico ON t.id_tecnico_responsable = u_tecnico.id_usuario
            WHERE t.id_ticket = '$id_ticket'";
$result = mysqli_query($conexion, $query);
$ticket = mysqli_fetch_assoc($result);

if (!$ticket) {
    header("Location: index_tickets.php");
    exit();
}

$rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Ticket - SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        .view-container {
            margin: 30px auto;
            margin-top: 50px;
            margin-left: 100px;
            margin-bottom: 90px;
            padding: 30px;
            background: white;
            border: 2px solid #adabab;
            border-radius: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            width: calc(95% - 100px);
            min-height: 95px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .view-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .view-title {
            font-size: 2.3em;
            font-weight: 700;
            color: #000000;
            margin: 0;
        }

        .btn-back {
            background: linear-gradient(90deg, #6c757d, #495057);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
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
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .btn-edit:hover {
            background: linear-gradient(90deg, #F57C00, #D84315);
            transform: translateY(-2px);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #932323;
        }

        .info-section.full-width {
            grid-column: 1 / -1;
        }

        .info-section h3 {
            margin: 0 0 15px 0;
            color: #932323;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-item {
            margin-bottom: 12px;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            display: block;
            margin-bottom: 4px;
        }

        .info-value {
            color: #333;
            font-size: 1.05em;
        }

        .estado-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.95em;
        }

        .estado-pendiente {
            background: #fee2e2;
            color: #DC2626;
        }

        .estado-progreso {
            background: #fef3c7;
            color: #F59E0B;
        }

        .estado-validacion {
            background: #dbeafe;
            color: #3B82F6;
        }

        .estado-finalizado {
            background: #d1fae5;
            color: #10B981;
        }

        .prioridad-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.95em;
            color: white;
        }

        .prioridad-critica {
            background: #DC2626;
        }

        .prioridad-alta {
            background: #F59E0B;
        }

        .prioridad-media {
            background: #3B82F6;
        }

        .prioridad-baja {
            background: #10B981;
        }

        .foto-container {
            margin-top: 15px;
        }

        .foto-container img {
            max-width: 100%;
            max-height: 400px;
            border-radius: 12px;
            border: 2px solid #ddd;
            cursor: pointer;
            transition: all 0.3s;
        }

        .foto-container img:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ddd;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #932323;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #932323;
        }

        .timeline-item.completed::before {
            background: #10B981;
            box-shadow: 0 0 0 2px #10B981;
        }

        .timeline-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 4px;
        }

        .timeline-value {
            color: #333;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-ver-foto {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background: linear-gradient(90deg, #3B82F6, #1e40af);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }

        .btn-ver-foto:hover {
            background: linear-gradient(90deg, #2563eb, #1e3a8a);
            transform: translateY(-2px);
        }

        .modal-foto {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
        }

        .modal-foto-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .modal-foto-close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-foto-close:hover {
            color: #bbb;
        }
    </style>
</head>

<body>
    <?php
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
                <h2 class="view-title">Detalle del Ticket</h2>
                <div class="action-buttons">
                    <?php if ($ticket['id_estado'] != 4 && ($rol == 'Administrador' || $rol == 'Técnico')): ?>
                        <a href="editar_tickets.php?id=<?php echo $ticket['id_ticket']; ?>" class="btn-edit">
                            <ion-icon name="create-outline"></ion-icon> Editar
                        </a>
                    <?php endif; ?>
                    <a href="index_tickets.php" class="btn-back">
                        <ion-icon name="arrow-back-outline"></ion-icon> Volver
                    </a>
                </div>
            </div>

            <div class="info-grid">
                <!-- Información General -->
                <div class="info-section">
                    <h3><ion-icon name="information-circle-outline"></ion-icon> Información General</h3>
                    <div class="info-item">
                        <span class="info-label">Código del Ticket:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ticket['codigo_ticket']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estado:</span>
                        <?php
                        $estado_classes = [
                            1 => 'estado-pendiente',
                            2 => 'estado-progreso',
                            3 => 'estado-validacion',
                            4 => 'estado-finalizado'
                        ];
                        $estado_class = $estado_classes[$ticket['id_estado']];
                        ?>
                        <span class="estado-badge <?php echo $estado_class; ?>">
                            <?php echo htmlspecialchars($ticket['nombre_estado']); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Prioridad:</span>
                        <?php
                        $prioridad_class = 'prioridad-' . strtolower($ticket['nombre_prioridad']);
                        ?>
                        <span class="prioridad-badge <?php echo $prioridad_class; ?>">
                            <?php echo htmlspecialchars($ticket['nombre_prioridad']); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha de Creación:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?></span>
                    </div>
                </div>

                <!-- Información de la Máquina -->
                <div class="info-section">
                    <h3><ion-icon name="hardware-chip-outline"></ion-icon> Información de la Máquina</h3>
                    <div class="info-item">
                        <span class="info-label">Código de Máquina:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ticket['codigo_maquina']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Marca:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ticket['marca']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Modelo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ticket['modelo']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Línea:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ticket['nombre_linea']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Planta:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ticket['nombre_planta']); ?></span>
                    </div>
                </div>

                <!-- Información del Problema -->
                <div class="info-section">
                    <h3><ion-icon name="alert-circle-outline"></ion-icon> Información del Problema</h3>
                    <div class="info-item">
                        <span class="info-label">Tipo de Falla:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ticket['nombre_tipo_falla']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Descripción de la Falla:</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($ticket['descripcion_falla'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Reportado por:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ticket['reportado_por']); ?></span>
                    </div>
                    <?php if (!empty($ticket['foto_url'])): ?>
                        <div class="foto-container">
                            <span class="info-label">Foto de la Falla:</span>
                            <button onclick="mostrarFoto('<?php echo htmlspecialchars($ticket['foto_url']); ?>')" class="btn-ver-foto">
                                <ion-icon name="image-outline"></ion-icon> Ver Foto
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Historial del Ticket -->
                <div class="info-section">
                    <h3><ion-icon name="time-outline"></ion-icon> Historial del Ticket</h3>
                    <div class="timeline">
                        <div class="timeline-item completed">
                            <div class="timeline-label">Ticket Creado</div>
                            <div class="timeline-value"><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?></div>
                        </div>
                        <?php if ($ticket['fecha_asignacion']): ?>
                            <div class="timeline-item completed">
                                <div class="timeline-label">Asignado a Técnico</div>
                                <div class="timeline-value"><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_asignacion'])); ?></div>
                                <?php if ($ticket['tiempo_respuesta']): ?>
                                    <div class="timeline-value" style="font-size: 0.9em; color: #666;">
                                        Tiempo de respuesta: <?php echo $ticket['tiempo_respuesta']; ?> minutos
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($ticket['fecha_resolucion']): ?>
                            <div class="timeline-item completed">
                                <div class="timeline-label">Enviado a Validación</div>
                                <div class="timeline-value"><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_resolucion'])); ?></div>
                                <?php if ($ticket['tiempo_resolucion']): ?>
                                    <div class="timeline-value" style="font-size: 0.9em; color: #666;">
                                        Tiempo de resolución: <?php echo $ticket['tiempo_resolucion']; ?> minutos
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($ticket['fecha_cierre']): ?>
                            <div class="timeline-item completed">
                                <div class="timeline-label">Ticket Finalizado</div>
                                <div class="timeline-value"><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_cierre'])); ?></div>
                                <?php if ($ticket['tiempo_total']): ?>
                                    <div class="timeline-value" style="font-size: 0.9em; color: #666;">
                                        Tiempo total: <?php echo $ticket['tiempo_total']; ?> minutos
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Información de Resolución (si existe) -->
                <?php if ($ticket['id_estado'] >= 2): ?>
                    <div class="info-section full-width">
                        <h3><ion-icon name="construct-outline"></ion-icon> Información de Resolución</h3>
                        <div class="info-item">
                            <span class="info-label">Técnico Responsable:</span>
                            <span class="info-value"><?php echo htmlspecialchars($ticket['tecnico_responsable'] ?? 'No asignado'); ?></span>
                        </div>
                        <?php if ($ticket['id_estado'] >= 3 && !empty($ticket['causa_raiz'])): ?>
                            <div class="info-item">
                                <span class="info-label">Causa Raíz:</span>
                                <span class="info-value"><?php echo nl2br(htmlspecialchars($ticket['causa_raiz'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Solución Aplicada:</span>
                                <span class="info-value"><?php echo nl2br(htmlspecialchars($ticket['solucion_aplicada'])); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($ticket['id_estado'] == 4 && !empty($ticket['observaciones'])): ?>
                            <div class="info-item">
                                <span class="info-label">Observaciones Finales:</span>
                                <span class="info-value"><?php echo nl2br(htmlspecialchars($ticket['observaciones'])); ?></span>
                            </div>
                        <?php endif; ?>
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

    <!-- Modal para mostrar foto -->
    <div id="modalFoto" class="modal-foto">
        <span class="modal-foto-close" onclick="cerrarFoto()">&times;</span>
        <img class="modal-foto-content" id="imgFoto">
    </div>

    <script>
        function mostrarFoto(url) {
            document.getElementById('modalFoto').style.display = 'block';
            document.getElementById('imgFoto').src = '../../' + url;
        }

        function cerrarFoto() {
            document.getElementById('modalFoto').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera de la imagen
        window.onclick = function(event) {
            const modal = document.getElementById('modalFoto');
            if (event.target == modal) {
                cerrarFoto();
            }
        }
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>