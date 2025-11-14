<?php
session_start();

// Verificar sesión y permisos
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

// Obtener datos del ticket con toda la información necesaria
$query = "SELECT t.*, m.codigo_maquina, l.nombre_linea, p.nombre_planta, 
            pr.nombre_prioridad, e.nombre_estado, tf.nombre_tipo_falla,
            CONCAT(u_reporta.nombre, ' ', u_reporta.apellido) as reportado_por,
            CONCAT(u_tecnico.nombre, ' ', u_tecnico.apellido) as tecnico_responsable
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
$id_estado = $ticket['id_estado'];

// Validar permisos según estado
// Estado 1 (Pendiente): Admin y Técnico pueden asignar
// Estado 2 (En Progreso): Solo Técnico asignado puede agregar causa raíz
// Estado 3 (En Validación): Solo Admin puede confirmar

// Estado 4 (Finalizado): No se puede editar
if ($id_estado == 4) {
    header("Location: ver_tickets.php?id=" . $id_ticket);
    exit();
}

// Validar permisos específicos
if ($id_estado == 2 && $rol == 'Técnico' && $ticket['id_tecnico_responsable'] != $_SESSION['id_usuario']) {
    $_SESSION['mensaje'] = 'Solo el técnico asignado puede editar este ticket';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: index_tickets.php");
    exit();
}

if ($id_estado == 3 && $rol != 'Administrador') {
    $_SESSION['mensaje'] = 'Solo un administrador puede confirmar la resolución';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: index_tickets.php");
    exit();
}

// Obtener lista de técnicos y administradores activos (para estado 1)
$query_tecnicos = "SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido) as nombre_completo, r.nombre_rol
                    FROM usuarios u
                    INNER JOIN roles r ON u.id_rol = r.id_rol
                    WHERE u.estado = 'Activo' AND (r.nombre_rol = 'Técnico' OR r.nombre_rol = 'Administrador')
                    ORDER BY r.nombre_rol, u.nombre, u.apellido";
$result_tecnicos = mysqli_query($conexion, $query_tecnicos);
$tecnicos = array();
while ($row = mysqli_fetch_assoc($result_tecnicos)) {
    $tecnicos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ticket - SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .form-container {
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
            height: 740px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-title {
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .form-group label .required {
            color: #DC2626;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #932323;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input:disabled,
        .form-group select:disabled,
        .form-group textarea:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #932323;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .info-box h3 {
            margin: 0 0 10px 0;
            color: #932323;
            font-size: 1.1em;
        }

        .info-box p {
            margin: 5px 0;
            color: #666;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .btn-submit {
            background: linear-gradient(90deg, #932323, #4d0d0d);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: linear-gradient(90deg, #7b1f1f, #430c0c);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .btn-cancel {
            background: linear-gradient(90deg, #6c757d, #495057);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1em;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: linear-gradient(90deg, #5a6268, #3d4349);
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-info {
            background: #dbeafe;
            border-left: 4px solid #3B82F6;
            color: #1e40af;
        }

        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #F59E0B;
            color: #92400e;
        }

        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10B981;
            color: #065f46;
        }

        .estado-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
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

        .foto-preview {
            margin-top: 15px;
            text-align: center;
        }

        .foto-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #ddd;
            cursor: pointer;
            transition: all 0.3s;
        }

        .foto-preview img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-ver-foto {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 16px;
            background: linear-gradient(90deg, #3B82F6, #1e40af);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
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

        <div class="form-container">
            <div class="form-header">
                <h2 class="form-title">
                    <?php
                    if ($id_estado == 1) echo "Asignar Técnico";
                    elseif ($id_estado == 2) echo "Agregar Causa Raíz y Solución";
                    elseif ($id_estado == 3) echo "Confirmar Resolución";
                    ?>
                </h2>
                <a href="index_tickets.php" class="btn-back">
                    <ion-icon name="arrow-back-outline"></ion-icon> Volver
                </a>
            </div>

            <div class="info-box">
                <h3>Información del Ticket</h3>
                <p><strong>Código:</strong> <?php echo htmlspecialchars($ticket['codigo_ticket']); ?></p>
                <p><strong>Máquina:</strong> <?php echo htmlspecialchars($ticket['codigo_maquina']); ?></p>
                <p><strong>Línea:</strong> <?php echo htmlspecialchars($ticket['nombre_linea']); ?></p>
                <p><strong>Planta:</strong> <?php echo htmlspecialchars($ticket['nombre_planta']); ?></p>
                <p><strong>Tipo de Falla:</strong> <?php echo htmlspecialchars($ticket['nombre_tipo_falla']); ?></p>
                <p><strong>Prioridad:</strong> <?php echo htmlspecialchars($ticket['nombre_prioridad']); ?></p>
                <p><strong>Reportado por:</strong> <?php echo htmlspecialchars($ticket['reportado_por']); ?></p>
                <p><strong>Estado Actual:</strong>
                    <?php
                    $estado_classes = [1 => 'estado-pendiente', 2 => 'estado-progreso', 3 => 'estado-validacion', 4 => 'estado-finalizado'];
                    $estado_class = $estado_classes[$id_estado];
                    ?>
                    <span class="estado-badge <?php echo $estado_class; ?>"><?php echo htmlspecialchars($ticket['nombre_estado']); ?></span>
                </p>
                <?php if (!empty($ticket['foto_url'])): ?>
                    <div class="foto-preview">
                        <p><strong>Foto de la Falla:</strong></p>
                        <a href="#" onclick="mostrarFoto('<?php echo htmlspecialchars($ticket['foto_url']); ?>'); return false;" class="btn-ver-foto">
                            <ion-icon name="image-outline"></ion-icon> Ver Foto
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($id_estado == 1): ?>
                <!-- FORMULARIO ESTADO 1: PENDIENTE → EN PROGRESO (Asignar Técnico) -->
                <div class="alert alert-info">
                    <ion-icon name="information-circle-outline" style="font-size: 24px;"></ion-icon>
                    <span>Al asignar un técnico, el ticket pasará automáticamente a estado "En Progreso"</span>
                </div>

                <form action="procesar_editar_ticket.php" method="POST" id="formAsignarTecnico">
                    <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                    <input type="hidden" name="accion" value="asignar_tecnico">

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Descripción de la Falla</label>
                            <textarea disabled><?php echo htmlspecialchars($ticket['descripcion_falla']); ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="id_tecnico">Técnico Responsable <span class="required">*</span></label>
                            <select name="id_tecnico" id="id_tecnico" required>
                                <option value="">Seleccione un técnico</option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?php echo $tecnico['id_usuario']; ?>">
                                        <?php echo htmlspecialchars($tecnico['nombre_completo'] . ' (' . $tecnico['nombre_rol'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="index_tickets.php" class="btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-submit">
                            <ion-icon name="person-add-outline"></ion-icon> Asignar Técnico
                        </button>
                    </div>
                </form>

            <?php elseif ($id_estado == 2): ?>
                <!-- FORMULARIO ESTADO 2: EN PROGRESO → EN VALIDACIÓN (Agregar Causa Raíz) -->
                <div class="alert alert-warning">
                    <ion-icon name="construct-outline" style="font-size: 24px;"></ion-icon>
                    <span>Complete la causa raíz y la solución aplicada para enviar el ticket a validación</span>
                </div>

                <form action="procesar_editar_ticket.php" method="POST" id="formCausaRaiz">
                    <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                    <input type="hidden" name="accion" value="agregar_causa_raiz">

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Descripción de la Falla</label>
                            <textarea disabled><?php echo htmlspecialchars($ticket['descripcion_falla']); ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label>Técnico Asignado</label>
                            <input type="text" value="<?php echo htmlspecialchars($ticket['tecnico_responsable']); ?>" disabled>
                        </div>

                        <div class="form-group full-width">
                            <label for="causa_raiz">Causa Raíz <span class="required">*</span></label>
                            <textarea name="causa_raiz" id="causa_raiz" required placeholder="Describa la causa raíz identificada del problema..."><?php echo htmlspecialchars($ticket['causa_raiz'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="solucion_aplicada">Solución Aplicada <span class="required">*</span></label>
                            <textarea name="solucion_aplicada" id="solucion_aplicada" required placeholder="Describa la solución que se aplicó para resolver el problema..."><?php echo htmlspecialchars($ticket['solucion_aplicada'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="index_tickets.php" class="btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-submit">
                            <ion-icon name="checkmark-done-outline"></ion-icon> Enviar a Validación
                        </button>
                    </div>
                </form>

            <?php elseif ($id_estado == 3): ?>
                <!-- FORMULARIO ESTADO 3: EN VALIDACIÓN → FINALIZADO (Confirmar Resolución) -->
                <div class="alert alert-success">
                    <ion-icon name="checkmark-circle-outline" style="font-size: 24px;"></ion-icon>
                    <span>Revise la información y confirme la resolución del ticket para finalizarlo</span>
                </div>

                <form action="procesar_editar_ticket.php" method="POST" id="formConfirmarResolucion">
                    <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                    <input type="hidden" name="accion" value="confirmar_resolucion">

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Descripción de la Falla</label>
                            <textarea disabled><?php echo htmlspecialchars($ticket['descripcion_falla']); ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label>Técnico Responsable</label>
                            <input type="text" value="<?php echo htmlspecialchars($ticket['tecnico_responsable']); ?>" disabled>
                        </div>

                        <div class="form-group full-width">
                            <label>Causa Raíz Identificada</label>
                            <textarea disabled><?php echo htmlspecialchars($ticket['causa_raiz'] ?? 'No especificada'); ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label>Solución Aplicada</label>
                            <textarea disabled><?php echo htmlspecialchars($ticket['solucion_aplicada'] ?? 'No especificada'); ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="observaciones">Observaciones Finales (Opcional)</label>
                            <textarea name="observaciones" id="observaciones" placeholder="Agregue observaciones adicionales si es necesario..."><?php echo htmlspecialchars($ticket['observaciones'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="index_tickets.php" class="btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-submit">
                            <ion-icon name="checkmark-circle-outline"></ion-icon> Confirmar y Finalizar
                        </button>
                    </div>
                </form>

            <?php endif; ?>
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

    <script>
        // Validación del formulario según el estado
        <?php if ($id_estado == 1): ?>
            document.getElementById('formAsignarTecnico').addEventListener('submit', function(e) {
                const tecnico = document.getElementById('id_tecnico').value;
                if (!tecnico) {
                    e.preventDefault();
                    alert('Debe seleccionar un técnico responsable');
                    return false;
                }
            });
        <?php elseif ($id_estado == 2): ?>
            document.getElementById('formCausaRaiz').addEventListener('submit', function(e) {
                const causaRaiz = document.getElementById('causa_raiz').value.trim();
                const solucion = document.getElementById('solucion_aplicada').value.trim();

                if (causaRaiz.length < 20) {
                    e.preventDefault();
                    alert('La causa raíz debe tener al menos 20 caracteres');
                    return false;
                }

                if (solucion.length < 20) {
                    e.preventDefault();
                    alert('La solución aplicada debe tener al menos 20 caracteres');
                    return false;
                }
            });
        <?php elseif ($id_estado == 3): ?>
            document.getElementById('formConfirmarResolucion').addEventListener('submit', function(e) {
                if (!confirm('¿Está seguro de que desea finalizar este ticket? Esta acción no se puede deshacer.')) {
                    e.preventDefault();
                    return false;
                }
            });
        <?php endif; ?>
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>