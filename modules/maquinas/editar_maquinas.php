<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

// Solo administradores y técnicos pueden editar máquinas
if ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico') {
    header("Location: index_maquinas.php");
    exit();
}

require_once '../../config/db.php';

// Obtener ID de la máquina
$id_maquina = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_maquina <= 0) {
    header("Location: index_maquinas.php");
    exit();
}

// Consultar datos de la máquina
$sql = "SELECT * FROM maquinas WHERE id_maquina = $id_maquina";
$resultado = mysqli_query($conexion, $sql);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    $_SESSION['error'] = "Máquina no encontrada";
    header("Location: index_maquinas.php");
    exit();
}

$maquina = mysqli_fetch_assoc($resultado);

// Obtener catálogos
$plantas = mysqli_query($conexion, "SELECT * FROM plantas WHERE estado = 'Activa' ORDER BY nombre_planta");
$lineas = mysqli_query($conexion, "SELECT l.*, p.nombre_planta FROM lineas l INNER JOIN plantas p ON l.id_planta = p.id_planta WHERE l.estado = 'Activa' ORDER BY p.nombre_planta, l.nombre_linea");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Máquina - SmartRepair</title>
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
            min-height: 100px;
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
            background: linear-gradient(90deg, #932323, #4d0d0d);
            color: white;
        }

        .btn-submit:hover {
            background: linear-gradient(90deg, #7b1f1f, #430c0c);
            box-shadow: 0 4px 12px rgba(147, 35, 35, 0.3);
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

        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content.success {
            background-color: #fff;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            max-width: 450px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .success-title {
            font-size: 1.8em;
            color: #932323;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .modal-content.success p {
            font-size: 1.1em;
            color: #666;
            margin: 10px 0;
            line-height: 1.6;
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
                <h2 class="form-title">Editar Máquina</h2>
                <a href="index_maquinas.php" class="btn-cancel">
                    <ion-icon name="arrow-back-outline"></ion-icon> Regresar
                </a>
            </div>

            <form id="formEditarMaquina" class="form-content" method="POST" action="procesar_editar_maquina.php" enctype="multipart/form-data">
                <input type="hidden" name="id_maquina" value="<?php echo $maquina['id_maquina']; ?>">

                <!-- Código de Máquina -->
                <div class="form-row">
                    <label class="form-label" for="codigo_maquina">
                        Código de Máquina<span class="required">*</span>
                    </label>
                    <input type="text" id="codigo_maquina" name="codigo_maquina" class="form-input" required maxlength="50"
                        value="<?php echo htmlspecialchars($maquina['codigo_maquina']); ?>">
                </div>

                <!-- Marca -->
                <div class="form-row">
                    <label class="form-label" for="marca">
                        Marca<span class="required">*</span>
                    </label>
                    <input type="text" id="marca" name="marca" class="form-input" required maxlength="100"
                        value="<?php echo htmlspecialchars($maquina['marca']); ?>">
                </div>

                <!-- Modelo -->
                <div class="form-row">
                    <label class="form-label" for="modelo">
                        Modelo<span class="required">*</span>
                    </label>
                    <input type="text" id="modelo" name="modelo" class="form-input" required maxlength="100"
                        value="<?php echo htmlspecialchars($maquina['modelo']); ?>">
                </div>

                <!-- Número de Serie -->
                <div class="form-row">
                    <label class="form-label" for="numero_serie">
                        Número de Serie
                    </label>
                    <input type="text" id="numero_serie" name="numero_serie" class="form-input" maxlength="100"
                        value="<?php echo htmlspecialchars($maquina['numero_serie']); ?>">
                </div>

                <!-- Planta -->
                <div class="form-row">
                    <label class="form-label" for="id_planta">
                        Planta<span class="required">*</span>
                    </label>
                    <select id="id_planta" name="id_planta" class="form-select" required onchange="filtrarLineas()">
                        <option value="">Seleccione una planta</option>
                        <?php 
                        mysqli_data_seek($plantas, 0);
                        while ($planta = mysqli_fetch_assoc($plantas)): ?>
                            <option value="<?php echo $planta['id_planta']; ?>" 
                                <?php echo ($planta['id_planta'] == $maquina['id_planta']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($planta['nombre_planta']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Línea -->
                <div class="form-row">
                    <label class="form-label" for="id_linea">
                        Línea<span class="required">*</span>
                    </label>
                    <select id="id_linea" name="id_linea" class="form-select" required>
                        <option value="">Seleccione una línea</option>
                        <?php while ($linea = mysqli_fetch_assoc($lineas)): ?>
                            <option value="<?php echo $linea['id_linea']; ?>" 
                                data-planta="<?php echo $linea['id_planta']; ?>"
                                <?php echo ($linea['id_linea'] == $maquina['id_linea']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($linea['nombre_planta'] . ' - ' . $linea['nombre_linea']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Área -->
                <div class="form-row">
                    <label class="form-label" for="area">
                        Área
                    </label>
                    <input type="text" id="area" name="area" class="form-input" maxlength="100"
                        value="<?php echo htmlspecialchars($maquina['area']); ?>">
                </div>

                <!-- Fecha de Instalación -->
                <div class="form-row">
                    <label class="form-label" for="fecha_instalacion">
                        Fecha de Instalación
                    </label>
                    <input type="date" id="fecha_instalacion" name="fecha_instalacion" class="form-input"
                        value="<?php echo $maquina['fecha_instalacion']; ?>">
                </div>

                <!-- Imagen de la Máquina -->
                <div class="form-row">
                    <label class="form-label" for="imagen">
                        Fotografía
                    </label>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <!-- Imagen actual -->
                        <div style="margin-bottom: 10px;">
                            <strong style="color: #666; font-size: 0.9em;">Imagen actual:</strong>
                            <div style="margin-top: 8px; border: 2px solid #ddd; border-radius: 8px; padding: 10px; display: inline-block;">
                                <img src="../../<?php echo htmlspecialchars($maquina['imagen'] ?? 'imgMaquinas/no-maquina.png'); ?>" 
                                     alt="Imagen actual" 
                                     style="max-width: 200px; max-height: 150px; display: block;"
                                     onerror="this.src='../../imgMaquinas/no-maquina.png'">
                            </div>
                        </div>
                        
                        <!-- Campo para nueva imagen -->
                        <input type="file" id="imagen" name="imagen" class="form-input" 
                               accept="image/png, image/jpeg, image/jpg, image/gif"
                               onchange="previewImage(event)">
                        <small style="color: #666; font-size: 0.85em;">
                            Formatos: PNG, JPG, JPEG, GIF. Tamaño máximo: 5MB<br>
                            <strong>Nota:</strong> Si subes una nueva imagen, reemplazará la actual.
                        </small>
                        
                        <!-- Previsualización de nueva imagen -->
                        <div id="imagePreview" style="display: none; margin-top: 10px;">
                            <strong style="color: #932323; font-size: 0.9em;">Nueva imagen:</strong>
                            <div style="margin-top: 8px; border: 2px solid #932323; border-radius: 8px; padding: 10px; display: inline-block;">
                                <img id="preview" src="" alt="Vista previa" 
                                     style="max-width: 200px; max-height: 150px; display: block;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado -->
                <div class="form-row">
                    <label class="form-label" for="estado">
                        Estado<span class="required">*</span>
                    </label>
                    <select id="estado" name="estado" class="form-select" required>
                        <option value="Activa" <?php echo ($maquina['estado'] == 'Activa') ? 'selected' : ''; ?>>Activa</option>
                        <option value="Inactiva" <?php echo ($maquina['estado'] == 'Inactiva') ? 'selected' : ''; ?>>Inactiva</option>
                        <option value="Mantenimiento" <?php echo ($maquina['estado'] == 'Mantenimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                        <option value="Fuera de servicio" <?php echo ($maquina['estado'] == 'Fuera de servicio') ? 'selected' : ''; ?>>Fuera de servicio</option>
                    </select>
                </div>

                <!-- Observaciones -->
                <div class="form-row">
                    <label class="form-label" for="observaciones">
                        Observaciones
                    </label>
                    <textarea id="observaciones" name="observaciones" class="form-textarea"><?php echo htmlspecialchars($maquina['observaciones']); ?></textarea>
                </div>

                <!-- Botones de acción -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <ion-icon name="save-outline"></ion-icon> Actualizar Máquina
                    </button>
                    <a href="index_maquinas.php" class="btn-cancel">
                        <ion-icon name="close-outline"></ion-icon> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de éxito -->
    <div id="successModal" class="modal">
        <div class="modal-content success">
            <div class="modal-body">
                <ion-icon name="checkmark-circle-outline" class="success-icon"></ion-icon>
                <h2 class="success-title">¡Actualización Exitosa!</h2>
                <p>La máquina ha sido actualizada correctamente.</p>
                <p>Serás redirigido a la lista de máquinas en 3 segundos.</p>
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
        function filtrarLineas() {
            const plantaId = document.getElementById('id_planta').value;
            const lineaSelect = document.getElementById('id_linea');
            const opciones = lineaSelect.getElementsByTagName('option');

            for (let i = 1; i < opciones.length; i++) {
                const opcion = opciones[i];
                if (plantaId === '' || opcion.getAttribute('data-planta') === plantaId) {
                    opcion.style.display = '';
                } else {
                    opcion.style.display = 'none';
                }
            }

            // Reset línea si no pertenece a la planta seleccionada
            if (lineaSelect.value) {
                const selectedOption = lineaSelect.options[lineaSelect.selectedIndex];
                if (selectedOption.getAttribute('data-planta') !== plantaId) {
                    lineaSelect.value = '';
                }
            }
        }

        // Filtrar líneas al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            filtrarLineas();
        });

        <?php if (isset($_SESSION['success']) && $_SESSION['success'] === true): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('successModal').style.display = 'flex';
                setTimeout(function() {
                    window.location.href = 'index_maquinas.php';
                }, 3000);
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            alert('<?php echo addslashes($_SESSION['error']); ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

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
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>
