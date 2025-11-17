<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

// Solo administradores pueden crear usuarios
if ($_SESSION['rol'] !== 'Administrador') {
    header("Location: index_usuarios.php");
    exit();
}

require_once '../../config/db.php';

// Obtener catálogos para los dropdowns
$roles = mysqli_query($conexion, "SELECT * FROM roles ORDER BY nombre_rol");
$plantas = mysqli_query($conexion, "SELECT * FROM plantas WHERE estado = 'Activa' ORDER BY nombre_planta");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <style>
        /* ================= CONTENEDOR DEL FORMULARIO ================= */
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

        /* Header del formulario */
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

        /* Formulario centrado */
        .form-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Grid de dos columnas */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .form-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
            align-items: center;
        }

        .form-label {
            text-align: right;
            font-weight: 600;
            color: #333;
            font-size: 1em;
        }

        .form-label span.required {
            color: #dc3545;
            margin-left: 3px;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #932323;
            box-shadow: 0 0 0 3px rgba(147, 35, 35, 0.1);
        }

        .form-input:hover,
        .form-select:hover {
            border-color: #932323;
        }

        /* Campo de contraseña con ojito */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            cursor: pointer;
            color: #666;
            font-size: 1.3em;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #932323;
        }

        /* Botones */
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

        /* Modal de éxito */
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

        /* Responsive */
        @media (max-width: 1200px) {
            .form-container {
                left: 320px !important;
                right: 20px !important;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 8px;
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
                <h2 class="form-title">Crear Nuevo Usuario</h2>
                <a href="index_usuarios.php" class="btn-cancel">
                    <ion-icon name="arrow-back-outline"></ion-icon> Regresar
                </a>
            </div>

            <form id="formCrearUsuario" class="form-content" method="POST" action="procesar_crear_usuario.php">
                <div class="form-grid">
                    <!-- COLUMNA IZQUIERDA -->
                    <div class="form-column">
                        <!-- Nombre -->
                        <div class="form-row">
                            <label class="form-label" for="nombre">
                                Nombre<span class="required">*</span>
                            </label>
                            <input type="text" id="nombre" name="nombre" class="form-input" required maxlength="100"
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras">
                        </div>

                        <!-- Apellido -->
                        <div class="form-row">
                            <label class="form-label" for="apellido">
                                Apellido<span class="required">*</span>
                            </label>
                            <input type="text" id="apellido" name="apellido" class="form-input" required maxlength="100"
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras">
                        </div>

                        <!-- Usuario -->
                        <div class="form-row">
                            <label class="form-label" for="usuario">
                                Usuario<span class="required">*</span>
                            </label>
                            <input type="text" id="usuario" name="usuario" class="form-input" required maxlength="50"
                                pattern="[A-Za-z0-9_]+" title="Solo letras, números y guión bajo">
                        </div>

                        <!-- Contraseña -->
                        <div class="form-row">
                            <label class="form-label" for="password">
                                Contraseña<span class="required">*</span>
                            </label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" class="form-input" required minlength="6" maxlength="50">
                                <ion-icon name="eye-outline" class="toggle-password" onclick="togglePassword('password')"></ion-icon>
                            </div>
                        </div>


                        <!-- Confirmar Contraseña -->
                        <div class="form-row">
                            <label class="form-label" for="password_confirm">
                                Confirmar<span class="required">*</span>
                            </label>
                            <div class="password-wrapper">
                                <input type="password" id="password_confirm" name="password_confirm" class="form-input" required minlength="6" maxlength="50">
                                <ion-icon name="eye-outline" class="toggle-password" onclick="togglePassword('password_confirm')"></ion-icon>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA DERECHA -->
                    <div class="form-column">
                        <!-- Email -->
                        <div class="form-row">
                            <label class="form-label" for="email">
                                Correo<span class="required">*</span>
                            </label>
                            <input type="email" id="email" name="email" class="form-input" required maxlength="150">
                        </div>

                        <!-- Teléfono -->
                        <div class="form-row">
                            <label class="form-label" for="telefono">
                                Teléfono
                            </label>
                            <input type="tel" id="telefono" name="telefono" class="form-input" minlength="10" maxlength="10" required pattern="[0-9]{10}"
                            title="Solo números, guiones, paréntesis y espacios">
                        </div>

                        <!-- Rol -->
                        <div class="form-row">
                            <label class="form-label" for="id_rol">
                                Rol<span class="required">*</span>
                            </label>
                            <select id="id_rol" name="id_rol" class="form-select" required>
                                <option value="">Seleccione un rol</option>
                                <?php while ($rol_item = mysqli_fetch_assoc($roles)): ?>
                                    <option value="<?php echo $rol_item['id_rol']; ?>">
                                        <?php echo htmlspecialchars($rol_item['nombre_rol']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Planta -->
                        <div class="form-row">
                            <label class="form-label" for="id_planta">
                                Planta
                            </label>
                            <select id="id_planta" name="id_planta" class="form-select">
                                <option value="">Sin asignar</option>
                                <?php while ($planta = mysqli_fetch_assoc($plantas)): ?>
                                    <option value="<?php echo $planta['id_planta']; ?>">
                                        <?php echo htmlspecialchars($planta['nombre_planta']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="form-row">
                            <label class="form-label" for="estado">
                                Estado<span class="required">*</span>
                            </label>
                            <select id="estado" name="estado" class="form-select" required>
                                <option value="Activo" selected>Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <ion-icon name="save-outline"></ion-icon> Guardar Usuario
                    </button>
                    <a href="index_usuarios.php" class="btn-cancel">
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
                <h2 class="success-title">¡Registro Exitoso!</h2>
                <p>El nuevo usuario ha sido guardado correctamente.</p>
                <p>Serás redirigido a la lista de usuarios en 3 segundos.</p>
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

    <script>
        // Función para mostrar/ocultar contraseña
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;

            if (field.type === 'password') {
                field.type = 'text';
                icon.setAttribute('name', 'eye-off-outline');
            } else {
                field.type = 'password';
                icon.setAttribute('name', 'eye-outline');
            }
        }

        // Validación de contraseñas
        document.getElementById('formCrearUsuario').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const password_confirm = document.getElementById('password_confirm').value;

            if (password !== password_confirm) {
                e.preventDefault();
                alert('Las contraseñas no coinciden. Por favor, verifica.');
                return false;
            }
        });

        // Verificar si hay éxito en la sesión
        <?php if (isset($_SESSION['success']) && $_SESSION['success'] === true): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('successModal').style.display = 'flex';
                setTimeout(function() {
                    window.location.href = 'index_usuarios.php';
                }, 3000);
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        // Mostrar errores si existen
        <?php if (isset($_SESSION['error'])): ?>
            alert('<?php echo addslashes($_SESSION['error']); ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>