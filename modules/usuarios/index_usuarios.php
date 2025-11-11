<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Líneas - SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">


    <style>
        .usuarios-container {
            margin: 30px auto;
            margin-top: 50px;
            margin-left: 100px;
            margin-bottom: 90px;
            padding: 30px;
            border: 1px solid #000;
            /* borde negro */
            /* Degradado y bordes */
            background: white;
            border: 2px solid #adabab;
            border-radius: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);

            /* Dimensiones */
            width: calc(95% - 100px);
            min-height: 95px;
            height: 740px;

            /* Configuración del layout interno */
            display: flex;
            flex-direction: column;
            /* Cambiado para apilar los elementos verticalmente */
            /* Aquí se elimina justify-content: center y align-items: center */
        }

        .usuarios-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        /* Header con título y botones */
        .header-section {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 25px !important;
            padding-bottom: 15px !important;
            border-bottom: 2px solid #f0f0f0 !important;
        }

        .section-title {
            font-size: 2.3em;
            font-weight: 700;
            color: #000000;
            margin: 0;
        }


        /* Botones de acción */
        .btn-new {
            background: rgba(177, 20, 20, 1) !important;
            border: none !important;
            color: white !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px !important;
            padding: 10px 20px !important;
            border-radius: 50px !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2) !important;
            transition: all 0.3s ease !important;
        }

        .btn-new:hover {
            background: rgba(146, 17, 17, 1) !important;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3) !important;
            transform: translateY(-2px) !important;
        }

        /* ================= ESTILOS DE DATATABLES ================= */
        .dataTables_wrapper {
            padding: 20px 0 !important;
            width: 100% !important;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right !important;
            text-align: right !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid rgba(177, 20, 20, 1) !important;
            border-radius: 8px !important;
            padding: 8px 15px !important;
            margin-left: 10px !important;
            font-size: 0.95em !important;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 2px solid rgba(177, 20, 20, 1) !important;
            border-radius: 8px !important;
            padding: 5px 10px !important;
            margin: 0 10px !important;
        }

        /* Botones de DataTables */
        .dt-buttons {
            margin-bottom: 15px !important;
            display: flex !important;
            gap: 8px !important;
        }

        .btn-dt {
            background: rgba(177, 20, 20, 1) !important;
            border: none !important;
            color: white !important;
            padding: 8px 15px !important;
            border-radius: 8px !important;
            cursor: pointer !important;
            font-size: 0.9em !important;
            transition: all 0.3s ease !important;
        }

        .btn-dt:hover {
            background: rgba(146, 19, 19, 1) !important;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3) !important;
            transform: translateY(-2px) !important;
        }


        /* ================= TABLA ================= */
        #tablaUsuarios {
            width: 100% !important;
            border-collapse: collapse !important;
            background: white !important;
        }

        #tablaUsuarios thead {
            background: rgba(177, 20, 20, 1) !important;
        }

        #tablaUsuarios th {
            background: transparent !important;
            color: white !important;
            font-weight: 600 !important;
            padding: 15px 10px !important;
            text-align: center !important;
            font-size: 0.95em !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }

        #tablaUsuarios td {
            padding: 12px 10px !important;
            text-align: center !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f0f0f0 !important;
            font-size: 0.9em !important;
        }

        #tablaUsuarios tbody tr {
            transition: all 0.2s ease !important;
        }

        #tablaUsuarios tbody tr:hover {
            background-color: #fff5f5 !important;
            transform: scale(1.01) !important;
            box-shadow: 0 2px 5px rgba(147, 35, 35, 0.1) !important;
        }

        #tablaUsuarios tbody tr:nth-child(even) {
            background-color: #fafafa !important;
        }

        #tablaUsuarios tbody tr:nth-child(even):hover {
            background-color: #fff5f5 !important;
        }
        /* ================= PAGINACIÓN ================= */
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 20px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 8px 12px !important;
            margin: 0 3px !important;
            border-radius: 8px !important;
            border: 2px solid rgba(177, 20, 20, 1) !important;
            background: white !important;
            color: rgba(177, 20, 20, 1) !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(90deg, rgba(224, 90, 90, 1), rgba(177, 20, 20, 1)) !important;
            border: 2px solid rgba(177, 20, 20, 1) !important;
            transform: translateY(-2px) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(90deg, rgba(224, 90, 90, 1), rgba(177, 20, 20, 1)) !important;
            color: white !important;
            border: 2px solid rgba(177, 20, 20, 1) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
        }

        /* ================= INFO Y CONTROLES ================= */
        .dataTables_wrapper .dataTables_info {
            padding-top: 20px !important;
            color: #666 !important;
            font-size: 0.9em !important;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            font-weight: 500 !important;
            color: #333 !important;
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 1200px) {
            .usuarios-container {
                margin-left: 20px !important;
                margin-right: 20px !important;
                padding: 20px !important;
            }

            .header-section {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 15px !important;
            }
        }
    </style>
</head>

<body>
    <?php
    // Obtiene el nombre del archivo de la URL
    $currentPage = basename($_SERVER['REQUEST_URI']);
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

                <?php if ($rol == 'Administrador' || $rol == 'Técnico'): ?>
                    <!-- MÁQUINAS -->
                    <?php $maquinasPages = ['index_maquinas.php', 'crear_maquinas.php', 'editar_maquinas.php', 'ver_maquinas.php']; ?>
                    <li class="<?php echo in_array($currentPage, $maquinasPages) ? 'active' : ''; ?>">
                        <a href="../maquinas/index_maquinas.php" data-tooltip="Máquinas">
                            <span class="icon"><ion-icon name="hardware-chip-outline"></ion-icon></span>
                            <span class="title">Máquinas</span>
                        </a>
                    </li>

                    <!-- LÍNEAS -->
                    <?php $lineasPages = ['index_lineas.php', 'crear_lineas.php', 'editar_lineas.php', 'ver_lineas.php']; ?>
                    <li class="<?php echo in_array($currentPage, $lineasPages) ? 'active' : ''; ?>">
                        <a href="../lineas/index_lineas.php" data-tooltip="Líneas">
                            <span class="icon"><ion-icon name="git-network-outline"></ion-icon></span>
                            <span class="title">Líneas</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($rol == 'Administrador' || $rol == 'Técnico' || $rol == 'Operario'): ?>
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

        <div class="usuarios-container">
            <div class="header-section">
                <h2 class="section-title">Usuarios</h2>
                <div style="display: flex; gap: 10px;">
                    <a href="crear_usuarios.php" class="btn-new">
                        <ion-icon name="add-circle-outline"></ion-icon> Nuevo
                    </a>
                </div>
            </div>
            <!-- Tabla HTML -->
            <table id="tablaUsuarios" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Correo</th>
                        <th>Usuario</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Planta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se cargarán dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>





    <div id="contactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-btn" id="closeContact">&times;</span>
                <h2>Información</h2>
            </div>
            <div class="modal-body">
                <h3><?php echo '' . $_SESSION["usuarioingresando"] . ''; ?></h3>
                <p></p>
                <a class="socialIcon" href="https://github.com/MeetsEvil" target="_blank"><i class="fab fa-github"></i></a>
                <a class="socialIcon" href="https://www.linkedin.com/in/orlandojgarciap-17a612289/" target="_blank"><i class="fab fa-linkedin"></i></a>
                <a class="socialIcon" href="mailto:orlandojgarciap@gmail.com" target="_blank"><i class="fas fa-envelope"></i></a>
            </div>
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


    <!-- Modal de confirmación de eliminación -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-btn" onclick="cerrarModalEliminar()">&times;</span>
                <h2>Desactivar Usuario</h2>
            </div>
            <div class="modal-body">
                <p style="color: #000000ff; font-size: 1em;">¿Estás seguro de que deseas desactivar este usuario?</p>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="cerrarModalEliminar()">Cancelar</button>
                <a href="#" id="btnConfirmarEliminar" class="btn-confirm" style="background: rgba(177, 20, 20, 1);">Desactivar</a>
            </div>
        </div>
    </div>

    <!-- Modal de éxito al eliminar -->
    <div id="successDeleteModal" class="modal">
        <div class="modal-content success">
            <div class="modal-body">
                <h2 class="success-title">¡Usuario Desactivado!</h2>
                <p style="margin-top: 8px;">El usuario ha sido desactivado correctamente.</p>
            </div>
        </div>
    </div>

    <script>
        let tablaUsuarios;
        let mostrandoInactivos = false;

        $(document).ready(function() {
            cargarTabla(false);

            // Verificar si hay éxito al eliminar
            <?php if (isset($_SESSION['success_delete']) && $_SESSION['success_delete'] === true): ?>
                document.getElementById('successDeleteModal').style.display = 'flex';
                setTimeout(function() {
                    document.getElementById('successDeleteModal').style.display = 'none';
                }, 2000);
                <?php unset($_SESSION['success_delete']); ?>
            <?php endif; ?>
        });

        function cargarTabla(inactivos) {
            if (tablaUsuarios) {
                tablaUsuarios.destroy();
            }

            const url = inactivos ? 'get_usuarios.php?inactivos=1' : 'get_usuarios.php';

            tablaUsuarios = $('#tablaUsuarios').DataTable({
                "ajax": {
                    "url": url,
                    "dataSrc": ""
                },
                "columns": [{
                        "data": "id_usuario"
                    },
                    {
                        "data": "nombre"
                    },
                    {
                        "data": "apellido"
                    },
                    {
                        "data": "email"
                    },
                    {
                        "data": "usuario"
                    },
                    {
                        "data": "telefono"
                    },
                    {
                        "data": "rol"
                    },
                    {
                        "data": "planta"
                    },
                    {
                        "data": "estado",
                        "render": function(data) {
                            if (data === 'Activo') {
                                return '<span style="color: green; font-weight: bold;">●</span> Activo';
                            } else {
                                return '<span style="color: red; font-weight: bold;">●</span> Inactivo';
                            }
                        }
                    },
                    {
                        "data": "acciones"
                    }
                ],
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "dom": 'Bfrtip',
                "buttons": [{
                        extend: 'copyHtml5',
                        text: '<i class="fas fa-copy"></i> Copiar',
                        className: 'btn-dt'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn-dt'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn-dt',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    },
                    {
                        text: inactivos ? '<i class="fas fa-eye"></i> Ver Activos' : '<i class="fas fa-eye-slash"></i> Ver Inactivos',
                        className: 'btn-dt',
                        action: function() {
                            mostrandoInactivos = !mostrandoInactivos;
                            cargarTabla(mostrandoInactivos);
                        }
                    }
                ],
                "order": [
                    [0, 'asc']
                ]
            });
        }

        function confirmarEliminar(id) {
            document.getElementById('deleteModal').style.display = 'flex';
            document.getElementById('btnConfirmarEliminar').href = 'eliminar_usuario.php?id=' + id;
        }

        function cerrarModalEliminar() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Cerrar modal al hacer click fuera
        window.onclick = function(event) {
            const deleteModal = document.getElementById('deleteModal');
            if (event.target == deleteModal) {
                cerrarModalEliminar();
            }
        }
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>