<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

// Todos los roles pueden reportar fallas
if ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico' && $_SESSION['rol'] !== 'Operario') {
    header("Location: ../main/dashboard.php");
    exit();
}

require_once '../../config/db.php';

// Obtener ID de la máquina
$id_maquina = isset($_GET['id_maquina']) ? intval($_GET['id_maquina']) : 0;

if ($id_maquina <= 0) {
    $_SESSION['mensaje'] = 'Máquina no especificada';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: ../maquinas/index_maquinas.php");
    exit();
}

// Obtener información de la máquina y su línea (para la prioridad)
$sql_maquina = "SELECT 
                    m.id_maquina,
                    m.codigo_maquina,
                    m.marca,
                    m.modelo,
                    p.nombre_planta,
                    l.nombre_linea,
                    l.id_prioridad,
                    pr.nombre_prioridad,
                    pr.color as color_prioridad
                FROM maquinas m
                INNER JOIN plantas p ON m.id_planta = p.id_planta
                INNER JOIN lineas l ON m.id_linea = l.id_linea
                INNER JOIN prioridades pr ON l.id_prioridad = pr.id_prioridad
                WHERE m.id_maquina = $id_maquina";

$resultado_maquina = mysqli_query($conexion, $sql_maquina);

if (!$resultado_maquina || mysqli_num_rows($resultado_maquina) == 0) {
    $_SESSION['mensaje'] = 'Máquina no encontrada';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: ../maquinas/index_maquinas.php");
    exit();
}

$maquina = mysqli_fetch_assoc($resultado_maquina);

// Obtener tipos de falla
$tipos_falla = mysqli_query($conexion, "SELECT * FROM tipos_falla ORDER BY nombre_tipo_falla");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Falla - SmartRepair</title>
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
            height: auto;
            display: flex;
            flex-direction: column;
        }

        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .form-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 30px !important;
            padding-bottom: 20px !important;
            border-bottom: 2px solid #f0f0f0 !important;
        }

        .form-title {
            font-size: 2em !important;
            font-weight: 700 !important;
            color: #932323 !important;
            margin: 0 !important;
        }

        .machine-info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #932323;
            margin-bottom: 30px;
        }

        .machine-info-box h3 {
            color: #932323;
            margin-bottom: 15px;
            font-size: 1.2em;
        }

        .machine-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .machine-info-item {
            display: flex;
            gap: 10px;
        }

        .machine-info-label {
            font-weight: 700;
            color: #555;
        }

        .machine-info-value {
            color: #333;
        }

        .priority-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
            color: white;
        }

        .form-content {
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .form-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 15px;
            margin-bottom: 20px;
            align-items: start;
        }

        .form-label {
            text-align: right;
            font-weight: 600;
            color: #333;
            font-size: 1em;
            padding-top: 10px;
        }

        .form-label span.required {
            color: #dc3545;
            margin-left: 3px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s ease;
            font-family: Arial, sans-serif;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #932323;
            box-shadow: 0 0 0 3px rgba(147, 35, 35, 0.1);
        }

        .form-input:hover,
        .form-select:hover,
        .form-textarea:hover {
            border-color: #932323;
        }

        .form-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .btn-submit,
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

        .btn-submit {
            background: linear-gradient(90deg, #FF5722, #D84315);
            color: white;
        }

        .btn-submit:hover {
            background: linear-gradient(90deg, #F4511E, #BF360C);
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
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

        .image-preview {
            margin-top: 10px;
            display: none;
        }

        .image-preview img {
            max-width: 300px;
            max-height: 200px;
            border: 2px solid #932323;
            border-radius: 8px;
        }

        @media (max-width: 992px) {
            .machine-info-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-label {
                text-align: left;
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

        <div class="form-container">
            <div class="form-header">
                <h2 class="form-title">Reportar Falla de Máquina</h2>
                <a href="../maquinas/ver_maquinas.php?id=<?php echo $maquina['id_maquina']; ?>" class="btn-cancel">
                    <ion-icon name="arrow-back-outline"></ion-icon> Regresar
                </a>
            </div>

            <!-- Información de la Máquina -->
            <div class="machine-info-box">
                <h3>
                    <ion-icon name="hardware-chip-outline" style="vertical-align: middle;"></ion-icon>
                    Información de la Máquina
                </h3>
                <div class="machine-info-grid">
                    <div class="machine-info-item">
                        <span class="machine-info-label">Código:</span>
                        <span class="machine-info-value"><?php echo htmlspecialchars($maquina['codigo_maquina']); ?></span>
                    </div>
                    <div class="machine-info-item">
                        <span class="machine-info-label">Marca/Modelo:</span>
                        <span class="machine-info-value"><?php echo htmlspecialchars($maquina['marca'] . ' ' . $maquina['modelo']); ?></span>
                    </div>
                    <div class="machine-info-item">
                        <span class="machine-info-label">Planta:</span>
                        <span class="machine-info-value"><?php echo htmlspecialchars($maquina['nombre_planta']); ?></span>
                    </div>
                    <div class="machine-info-item">
                        <span class="machine-info-label">Línea:</span>
                        <span class="machine-info-value"><?php echo htmlspecialchars($maquina['nombre_linea']); ?></span>
                    </div>
                    <div class="machine-info-item">
                        <span class="machine-info-label">Prioridad:</span>
                        <span class="machine-info-value">
                            <span class="priority-badge" style="background: <?php echo $maquina['color_prioridad']; ?>">
                                <?php echo htmlspecialchars($maquina['nombre_prioridad']); ?>
                            </span>
                        </span>
                    </div>
                </div>
                <p style="margin-top: 15px; color: #666; font-size: 0.9em;">
                    <ion-icon name="information-circle-outline" style="vertical-align: middle;"></ion-icon>
                    La prioridad del ticket se asignará automáticamente según la prioridad de la línea.
                </p>
            </div>

            <form id="formReportarFalla" class="form-content" method="POST" action="procesar_ticket_maquina.php" enctype="multipart/form-data">
                <input type="hidden" name="id_maquina" value="<?php echo $maquina['id_maquina']; ?>">
                <input type="hidden" name="id_prioridad" value="<?php echo $maquina['id_prioridad']; ?>">

                <!-- Tipo de Falla -->
                <div class="form-row">
                    <label class="form-label" for="id_tipo_falla">
                        Tipo de Falla<span class="required">*</span>
                    </label>
                    <select id="id_tipo_falla" name="id_tipo_falla" class="form-select" required>
                        <option value="">Seleccione el tipo de falla</option>
                        <?php while ($tipo = mysqli_fetch_assoc($tipos_falla)): ?>
                            <option value="<?php echo $tipo['id_tipo_falla']; ?>">
                                <?php echo htmlspecialchars($tipo['nombre_tipo_falla']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Descripción de la Falla -->
                <div class="form-row">
                    <label class="form-label" for="descripcion_falla">
                        Descripción<span class="required">*</span>
                    </label>
                    <div style="width: 100%;">
                        <textarea id="descripcion_falla" name="descripcion_falla" class="form-textarea" required
                            placeholder="Describa detalladamente la falla observada (mínimo 20 caracteres)"></textarea>
                        <small style="color: #666; font-size: 0.85em;">
                            Mínimo 20 caracteres. Sea específico sobre el problema.
                        </small>
                    </div>
                </div>

                <!-- Foto de la Falla (Opcional) -->
                <div class="form-row">
                    <label class="form-label" for="foto">
                        Fotografía
                    </label>
                    <div style="width: 100%;">
                        <input type="file" id="foto" name="foto" class="form-input" 
                               accept="image/png, image/jpeg, image/jpg, image/gif"
                               onchange="previewImage(event)">
                        <small style="color: #666; font-size: 0.85em;">
                            Opcional. Formatos: PNG, JPG, JPEG, GIF. Tamaño máximo: 5MB
                        </small>
                        <div id="imagePreview" class="image-preview">
                            <img id="preview" src="" alt="Vista previa">
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <ion-icon name="alert-circle-outline"></ion-icon> Reportar Falla
                    </button>
                    <a href="../maquinas/ver_maquinas.php?id=<?php echo $maquina['id_maquina']; ?>" class="btn-cancel">
                        <ion-icon name="close-outline"></ion-icon> Cancelar
                    </a>
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
        // Previsualización de imagen
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('imagePreview');

            if (file) {
                // Validar tamaño (5MB máximo)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen es demasiado grande. El tamaño máximo es 5MB.');
                    event.target.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }

                // Validar tipo de archivo
                const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Formato de imagen no válido. Use PNG, JPG, JPEG o GIF.');
                    event.target.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }

                // Mostrar previsualización
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        }

        // Validar formulario antes de enviar
        document.getElementById('formReportarFalla').addEventListener('submit', function(e) {
            const descripcion = document.getElementById('descripcion_falla').value.trim();
            
            if (descripcion.length < 20) {
                e.preventDefault();
                alert('La descripción debe tener al menos 20 caracteres.');
                return false;
            }
        });
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>
