<?php
session_start();

// Verificar sesión y permisos (Admin y Técnico)
if (
    !isset($_SESSION['usuarioingresando']) ||
    ($_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Técnico')
) {
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

// Obtener datos del ticket
$query = "SELECT t.*, m.codigo_maquina, l.nombre_linea, p.nombre_planta, pr.nombre_prioridad
          FROM tickets t
          INNER JOIN maquinas m ON t.id_maquina = m.id_maquina
          INNER JOIN lineas l ON m.id_linea = l.id_linea
          INNER JOIN plantas p ON l.id_planta = p.id_planta
          INNER JOIN prioridades pr ON t.id_prioridad = pr.id_prioridad
          WHERE t.id_ticket = '$id_ticket'";
$result = mysqli_query($conexion, $query);
$ticket = mysqli_fetch_assoc($result);

if (!$ticket) {
    header("Location: index_tickets.php");
    exit();
}

// Obtener lista de máquinas activas
$query_maquinas = "SELECT m.id_maquina, m.codigo_maquina, l.nombre_linea
                   FROM maquinas m
                   INNER JOIN lineas l ON m.id_linea = l.id_linea
                   WHERE m.estado = 'Activa'
                   ORDER BY m.codigo_maquina";
$result_maquinas = mysqli_query($conexion, $query_maquinas);
$maquinas = array();
while ($row = mysqli_fetch_assoc($result_maquinas)) {
    $maquinas[] = $row;
}

// Obtener lista de usuarios activos (Técnicos y Administradores)
$query_usuarios = "SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido) as nombre_completo, r.nombre_rol
                   FROM usuarios u
                   INNER JOIN roles r ON u.id_rol = r.id_rol
                   WHERE u.estado = 'Activo' AND (r.nombre_rol = 'Técnico' OR r.nombre_rol = 'Administrador')
                   ORDER BY u.nombre, u.apellido";
$result_usuarios = mysqli_query($conexion, $query_usuarios);
$usuarios = array();
while ($row = mysqli_fetch_assoc($result_usuarios)) {
    $usuarios[] = $row;
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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <style>
        .form-container {
            margin: 30px auto;
            margin-top: 50px;
            margin-left: 100px;
            padding: 30px;
            background: white;
            border: 2px solid #adabab;
            border-radius: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            width: calc(95% - 100px);
            max-width: 1200px;
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
            min-height: 100px;
        }

        .form-group input:disabled,
        .form-group select:disabled {
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

        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #F59E0B;
            color: #856404;
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

        <div class="form-container">
            <div class="form-header">
                <h2 class="form-title">Editar Ticket</h2>
                <a href="index_tickets.php" class="btn-back">
                    <ion-icon name="arrow-back-outline"></ion-icon> Volver
                </a>
            </div>

            <div class="info-box">
                <h3>Información del Ticket</h3>
                <p><strong>Código:</strong> <?php echo htmlspecialchars($ticket['codigo_ticket']); ?></p>
                <p><strong>Estado Actual:</strong>
                    <?php
                    $estado_class = $ticket['id_estado'] == 1 ? 'estado-pendiente' : 'estado-progreso';
                    $estado_texto = $ticket['id_estado'] == 1 ? 'Pendiente' : 'En Progreso';
                    ?>
                    <span class="estado-badge <?php echo $estado_class; ?>"><?php echo $estado_texto; ?></span>
                </p>
            </div>

            <?php if ($ticket['id_estado'] == 1): ?>
                <div class="alert alert-warning">
                    <ion-icon name="information-circle-outline" style="font-size: 24px;"></ion-icon>
                    <span>Al asignar un responsable, el ticket pasará automáticamente a estado "En Progreso"</span>
                </div>
            <?php endif; ?>

            <form action="procesar_editar_ticket.php" method="POST" id="formEditarTicket">
                <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                <input type="hidden" name="estado_actual" value="<?php echo $ticket['id_estado']; ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Código Ticket</label>
                        <input type="text" value="<?php echo htmlspecialchars($ticket['codigo_ticket']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="id_maquina">Máquina <span class="required">*</span></label>
                        <select name="id_maquina" id="id_maquina" required>
                            <option value="">Seleccione una máquina</option>
                            <?php foreach ($maquinas as $maquina): ?>
                                <option value="<?php echo $maquina['id_maquina']; ?>"
                                    <?php echo ($maquina['id_maquina'] == $ticket['id_maquina']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($maquina['codigo_maquina'] . ' (' . $maquina['nombre_linea'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_prioridad">Prioridad <span class="required">*</span></label>
                        <select name="id_prioridad" id="id_prioridad" required>
                            <?php
                            $query_prioridades = "SELECT * FROM prioridades ORDER BY nivel ASC";
                            $result_prioridades = mysqli_query($conexion, $query_prioridades);
                            while ($prioridad = mysqli_fetch_assoc($result_prioridades)):
                            ?>
                                <option value="<?php echo $prioridad['id_prioridad']; ?>" 
                                        <?php echo ($prioridad['id_prioridad'] == $ticket['id_prioridad']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prioridad['nombre_prioridad']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_responsable">Responsable <?php echo ($ticket['id_estado'] == 1) ? '<span class="required">*</span>' : ''; ?></label>
                        <select name="id_responsable" id="id_responsable" <?php echo ($ticket['id_estado'] == 1) ? 'required' : ''; ?>>
                            <option value="">Sin asignar</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?php echo $usuario['id_usuario']; ?>"
                                    <?php echo ($usuario['id_usuario'] == $ticket['id_tecnico_responsable']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($usuario['nombre_completo'] . ' (' . $usuario['nombre_rol'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="descripcion">Descripción del Problema <span class="required">*</span></label>
                        <textarea name="descripcion" id="descripcion" required><?php echo htmlspecialchars($ticket['descripcion_falla']); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index_tickets.php" class="btn-cancel">Cancelar</a>
                    <button type="submit" class="btn-submit">
                        <ion-icon name="save-outline"></ion-icon> Guardar Cambios
                    </button>
                </div>
            </form>
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
        // Validación del formulario
        document.getElementById('formEditarTicket').addEventListener('submit', function(e) {
            const descripcion = document.getElementById('descripcion').value.trim();
            const responsable = document.getElementById('id_responsable').value;
            const estadoActual = document.querySelector('input[name="estado_actual"]').value;

            if (descripcion.length < 10) {
                e.preventDefault();
                alert('La descripción debe tener al menos 10 caracteres');
                return false;
            }

            // Si está en estado Pendiente, debe asignar responsable
            if (estadoActual == '1' && !responsable) {
                e.preventDefault();
                alert('Debe asignar un responsable para continuar');
                return false;
            }
        });
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>