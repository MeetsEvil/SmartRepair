<?php
session_start();
if (!isset($_SESSION['usuarioingresando']) || ($_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Técnico')) {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

// Obtener ID del mantenimiento
$id_mantenimiento = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_mantenimiento <= 0) {
    header("Location: index_mantenimiento.php");
    exit();
}

// Obtener datos del mantenimiento
$query_mantenimiento = "SELECT * FROM mantenimientos WHERE id_mantenimiento = $id_mantenimiento";
$result_mantenimiento = mysqli_query($conexion, $query_mantenimiento);

if (!$result_mantenimiento || mysqli_num_rows($result_mantenimiento) == 0) {
    $_SESSION['mensaje'] = 'Mantenimiento no encontrado';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: index_mantenimiento.php");
    exit();
}

$mantenimiento = mysqli_fetch_assoc($result_mantenimiento);

// Obtener máquinas activas
$query_maquinas = "SELECT m.id_maquina, m.codigo_maquina, l.nombre_linea
                   FROM maquinas m
                   INNER JOIN lineas l ON m.id_linea = l.id_linea
                   WHERE m.estado = 'Activa'
                   ORDER BY m.codigo_maquina";
$result_maquinas = mysqli_query($conexion, $query_maquinas);

// Obtener tipos de mantenimiento
$query_tipos = "SELECT * FROM tipos_mantenimiento ORDER BY nombre_tipo";
$result_tipos = mysqli_query($conexion, $query_tipos);

// Obtener técnicos activos
$query_tecnicos = "SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido) as nombre_completo
                   FROM usuarios u
                   INNER JOIN roles r ON u.id_rol = r.id_rol
                   WHERE u.estado = 'Activo' AND (r.nombre_rol = 'Técnico' OR r.nombre_rol = 'Administrador')
                   ORDER BY u.nombre";
$result_tecnicos = mysqli_query($conexion, $query_tecnicos);

$rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mantenimiento - SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

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
            min-height: 100px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
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

        /* ================= MODALES ================= */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 450px;
            text-align: center;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .modal-header {
            background: linear-gradient(90deg, #932323, #4d0d0d);
            color: white;
            padding: 15px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            position: relative;
        }

        .modal-header h2 {
            font-size: 1.5em;
            margin: 0;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-body p {
            font-size: 1.1em;
            color: #333;
            margin: 0;
        }

        .modal-footer {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .close-btn {
            position: absolute;
            right: 20px;
            top: 5px;
            font-size: 2em;
            font-weight: bold;
            color: white;
            cursor: pointer;
        }

        .btn-confirm {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
            font-size: 1.1em;
            background-color: #dc3545;
            color: white;
        }

        .btn-confirm:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <?php $currentPage = basename($_SERVER['REQUEST_URI']); ?>

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
                <h2 class="form-title">Editar Mantenimiento</h2>
                <a href="index_mantenimiento.php" class="btn-back">
                    <ion-icon name="arrow-back-outline"></ion-icon> Volver
                </a>
            </div>

            <form action="procesar_editar_mantenimiento.php" method="POST" id="formEditarMantenimiento">
                <input type="hidden" name="id_mantenimiento" value="<?php echo $mantenimiento['id_mantenimiento']; ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="id_maquina">Máquina <span class="required">*</span></label>
                        <select name="id_maquina" id="id_maquina" required>
                            <option value="">Seleccione una máquina</option>
                            <?php while ($maquina = mysqli_fetch_assoc($result_maquinas)): ?>
                                <option value="<?php echo $maquina['id_maquina']; ?>" <?php echo ($maquina['id_maquina'] == $mantenimiento['id_maquina']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($maquina['codigo_maquina'] . ' - ' . $maquina['nombre_linea']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_tipo_mantenimiento">Tipo de Mantenimiento <span class="required">*</span></label>
                        <select name="id_tipo_mantenimiento" id="id_tipo_mantenimiento" required>
                            <option value="">Seleccione un tipo</option>
                            <?php while ($tipo = mysqli_fetch_assoc($result_tipos)): ?>
                                <option value="<?php echo $tipo['id_tipo_mantenimiento']; ?>" <?php echo ($tipo['id_tipo_mantenimiento'] == $mantenimiento['id_tipo_mantenimiento']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo['nombre_tipo']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_tecnico_responsable">Técnico Responsable <span class="required">*</span></label>
                        <select name="id_tecnico_responsable" id="id_tecnico_responsable" required>
                            <option value="">Seleccione un técnico</option>
                            <?php while ($tecnico = mysqli_fetch_assoc($result_tecnicos)): ?>
                                <option value="<?php echo $tecnico['id_usuario']; ?>" <?php echo ($tecnico['id_usuario'] == $mantenimiento['id_tecnico_responsable']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tecnico['nombre_completo']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha_mantenimiento">Fecha y Hora <span class="required">*</span></label>
                        <input type="datetime-local" name="fecha_mantenimiento" id="fecha_mantenimiento" value="<?php echo date('Y-m-d\TH:i', strtotime($mantenimiento['fecha_mantenimiento'])); ?>" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="actividades_realizadas">Actividades Realizadas <span class="required">*</span></label>
                        <textarea name="actividades_realizadas" id="actividades_realizadas" required placeholder="Describa las actividades realizadas durante el mantenimiento..."><?php echo htmlspecialchars($mantenimiento['actividades_realizadas']); ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="repuestos_utilizados">Repuestos Utilizados</label>
                        <textarea name="repuestos_utilizados" id="repuestos_utilizados" placeholder="Liste los repuestos o materiales utilizados (opcional)..."><?php echo htmlspecialchars($mantenimiento['repuestos_utilizados'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="observaciones">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" placeholder="Observaciones adicionales (opcional)..."><?php echo htmlspecialchars($mantenimiento['observaciones'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index_mantenimiento.php" class="btn-cancel">Cancelar</a>
                    <button type="submit" class="btn-submit">
                        <ion-icon name="save-outline"></ion-icon> Actualizar Mantenimiento
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
        document.getElementById('formEditarMantenimiento').addEventListener('submit', function(e) {
            const actividades = document.getElementById('actividades_realizadas').value.trim();
            
            if (actividades.length < 10) {
                e.preventDefault();
                alert('Las actividades realizadas deben tener al menos 10 caracteres');
                return false;
            }
        });
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>
