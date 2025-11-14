<?php
session_start();

// Verificar sesión (Todos los roles pueden crear tickets)
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

require_once '../../config/db.php';

// Obtener lista de máquinas activas
$query_maquinas = "SELECT m.id_maquina, m.codigo_maquina, l.nombre_linea, p.nombre_planta
                   FROM maquinas m
                   INNER JOIN lineas l ON m.id_linea = l.id_linea
                   INNER JOIN plantas p ON l.id_planta = p.id_planta
                   WHERE m.estado = 'Activa'
                   ORDER BY p.nombre_planta, l.nombre_linea, m.codigo_maquina";
$result_maquinas = mysqli_query($conexion, $query_maquinas);
$maquinas = array();
while ($row = mysqli_fetch_assoc($result_maquinas)) {
    $maquinas[] = $row;
}

// Obtener lista de prioridades
$query_prioridades = "SELECT * FROM prioridades ORDER BY nivel ASC";
$result_prioridades = mysqli_query($conexion, $query_prioridades);
$prioridades = array();
while ($row = mysqli_fetch_assoc($result_prioridades)) {
    $prioridades[] = $row;
}

// Obtener lista de tipos de falla
$query_tipos_falla = "SELECT * FROM tipos_falla ORDER BY nombre_tipo_falla ASC";
$result_tipos_falla = mysqli_query($conexion, $query_tipos_falla);
$tipos_falla = array();
while ($row = mysqli_fetch_assoc($result_tipos_falla)) {
    $tipos_falla[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Ticket - SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
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

        .form-group input[type="file"] {
            padding: 8px;
        }

        .file-preview {
            margin-top: 10px;
            max-width: 300px;
        }

        .file-preview img {
            width: 100%;
            border-radius: 8px;
            border: 2px solid #ddd;
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
                <h2 class="form-title">Crear Nuevo Ticket</h2>
                <a href="index_tickets.php" class="btn-back">
                    <ion-icon name="arrow-back-outline"></ion-icon> Volver
                </a>
            </div>

            <div class="alert alert-info">
                <ion-icon name="information-circle-outline" style="font-size: 24px;"></ion-icon>
                <span>Complete el formulario para reportar una falla en una máquina. El ticket se creará en estado "Pendiente".</span>
            </div>

            <form action="procesar_crear_ticket.php" method="POST" enctype="multipart/form-data" id="formCrearTicket">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="id_maquina">Máquina <span class="required">*</span></label>
                        <select name="id_maquina" id="id_maquina" required>
                            <option value="">Seleccione una máquina</option>
                            <?php foreach ($maquinas as $maquina): ?>
                                <option value="<?php echo $maquina['id_maquina']; ?>">
                                    <?php echo htmlspecialchars($maquina['codigo_maquina'] . ' - ' . $maquina['nombre_linea'] . ' (' . $maquina['nombre_planta'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_prioridad">Prioridad <span class="required">*</span></label>
                        <select name="id_prioridad" id="id_prioridad" required>
                            <option value="">Seleccione la prioridad</option>
                            <?php foreach ($prioridades as $prioridad): ?>
                                <option value="<?php echo $prioridad['id_prioridad']; ?>">
                                    <?php echo htmlspecialchars($prioridad['nombre_prioridad']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_tipo_falla">Tipo de Falla <span class="required">*</span></label>
                        <select name="id_tipo_falla" id="id_tipo_falla" required>
                            <option value="">Seleccione el tipo de falla</option>
                            <?php foreach ($tipos_falla as $tipo): ?>
                                <option value="<?php echo $tipo['id_tipo_falla']; ?>">
                                    <?php echo htmlspecialchars($tipo['nombre_tipo_falla']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="descripcion_falla">Descripción de la Falla <span class="required">*</span></label>
                        <textarea name="descripcion_falla" id="descripcion_falla" required placeholder="Describa detalladamente el problema que presenta la máquina..."></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="foto">Foto de la Falla (Opcional)</label>
                        <input type="file" name="foto" id="foto" accept="image/*" onchange="previewImage(event)">
                        <div id="preview" class="file-preview" style="display: none;">
                            <img id="preview-img" src="" alt="Vista previa">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index_tickets.php" class="btn-cancel">Cancelar</a>
                    <button type="submit" class="btn-submit">
                        <ion-icon name="add-circle-outline"></ion-icon> Crear Ticket
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
        // Vista previa de imagen
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('preview').style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                document.getElementById('preview').style.display = 'none';
            }
        }

        // Validación del formulario
        document.getElementById('formCrearTicket').addEventListener('submit', function(e) {
            const descripcion = document.getElementById('descripcion_falla').value.trim();
            
            if (descripcion.length < 20) {
                e.preventDefault();
                alert('La descripción debe tener al menos 20 caracteres');
                return false;
            }
        });
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>