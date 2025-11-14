<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets - SmartRepair</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    
    <style>
        .tickets-container {
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

        .tickets-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-title {
            font-size: 2.3em;
            font-weight: 700;
            color: #000000;
            margin: 0;
        }

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

        /* Tabs de Estados */
        .tabs-estados {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 3px solid #f0f0f0;
        }

        .tab-estado {
            flex: 1;
            padding: 15px 20px;
            text-align: center;
            cursor: pointer;
            font-weight: 700;
            font-size: 1.1em;
            border-radius: 8px 8px 0 0;
            transition: all 0.3s ease;
            position: relative;
            background: #f8f9fa;
            border: 2px solid transparent;
        }

        .tab-estado:hover {
            transform: translateY(-3px);
        }

        .tab-estado.active {
            background: white;
            border-bottom: 4px solid;
        }

        .tab-estado.pendiente {
            color: #DC2626;
        }

        .tab-estado.pendiente.active {
            border-bottom-color: #DC2626;
            box-shadow: 0 -3px 0 #DC2626 inset;
        }

        .tab-estado.progreso {
            color: #F59E0B;
        }

        .tab-estado.progreso.active {
            border-bottom-color: #F59E0B;
            box-shadow: 0 -3px 0 #F59E0B inset;
        }

        .tab-estado.validacion {
            color: #3B82F6;
        }

        .tab-estado.validacion.active {
            border-bottom-color: #3B82F6;
            box-shadow: 0 -3px 0 #3B82F6 inset;
        }

        .tab-estado.finalizado {
            color: #10B981;
        }

        .tab-estado.finalizado.active {
            border-bottom-color: #10B981;
            box-shadow: 0 -3px 0 #10B981 inset;
        }

        .tab-count {
            display: inline-block;
            background: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            margin-left: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Filtros */
        .filters-section {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .filter-input {
            padding: 8px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .filter-input:focus {
            outline: none;
            border-color: #932323;
        }

        .filter-select {
            padding: 8px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 0.9em;
            cursor: pointer;
        }

        /* Lista de Tickets */
        .tickets-list-container {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }

        .tickets-list-container::-webkit-scrollbar {
            width: 8px;
        }

        .tickets-list-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .tickets-list-container::-webkit-scrollbar-thumb {
            background: #932323;
            border-radius: 10px;
        }

        .tickets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }

        /* Tarjeta de Ticket */
        .ticket-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 4px solid;
        }

        .ticket-card.pendiente {
            border-left-color: #DC2626;
        }

        .ticket-card.progreso {
            border-left-color: #F59E0B;
        }

        .ticket-card.validacion {
            border-left-color: #3B82F6;
        }

        .ticket-card.finalizado {
            border-left-color: #10B981;
        }

        .ticket-card:hover {
            box-shadow: 0 4px 12px rgba(147, 35, 35, 0.2);
            transform: translateY(-2px);
        }

        .ticket-id {
            font-weight: 700;
            color: #932323;
            font-size: 0.9em;
            margin-bottom: 8px;
        }

        .ticket-maquina {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 0.95em;
        }

        .ticket-info {
            font-size: 0.85em;
            color: #666;
            margin-bottom: 3px;
        }

        .ticket-prioridad {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
            color: white;
            margin-top: 8px;
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

        .ticket-responsable {
            font-size: 0.85em;
            color: #555;
            margin-top: 8px;
            font-style: italic;
        }

        .ticket-actions {
            display: flex;
            gap: 5px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }

        .btn-action-small {
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 0.8em;
            font-weight: 600;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            transition: all 0.3s ease;
        }

        .btn-ver {
            background: linear-gradient(90deg, #2196F3, #0D47A1);
        }

        .btn-editar {
            background: linear-gradient(90deg, #FF9800, #E65100);
        }

        .btn-eliminar {
            background: linear-gradient(90deg, #F44336, #B71C1C);
        }

        .btn-action-small:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state ion-icon {
            font-size: 64px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1.1em;
            margin: 0;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10B981;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            border-left: 4px solid #DC2626;
            color: #991b1b;
        }

        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #F59E0B;
            color: #92400e;
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

        <div class="tickets-container">
            <div class="header-section">
                <h2 class="section-title">Tickets</h2>
                <a href="crear_tickets.php" class="btn-new">
                    <ion-icon name="add-circle-outline"></ion-icon> Nuevo Ticket
                </a>
            </div>

            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?>" id="mensaje-alerta">
                    <ion-icon name="<?php echo $_SESSION['tipo_mensaje'] == 'success' ? 'checkmark-circle-outline' : 'alert-circle-outline'; ?>" style="font-size: 24px;"></ion-icon>
                    <span><?php echo htmlspecialchars($_SESSION['mensaje']); ?></span>
                </div>
                <?php 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
                ?>
            <?php endif; ?>

            <!-- Tabs de Estados -->
            <div class="tabs-estados">
                <div class="tab-estado pendiente active" data-estado="1" onclick="cambiarEstado(1)">
                    <span>Pendientes</span>
                    <span class="tab-count" id="count-pendiente">0</span>
                </div>
                <div class="tab-estado progreso" data-estado="2" onclick="cambiarEstado(2)">
                    <span>En Progreso</span>
                    <span class="tab-count" id="count-progreso">0</span>
                </div>
                <div class="tab-estado validacion" data-estado="3" onclick="cambiarEstado(3)">
                    <span>En Validación</span>
                    <span class="tab-count" id="count-validacion">0</span>
                </div>
                <div class="tab-estado finalizado" data-estado="4" onclick="cambiarEstado(4)">
                    <span>Finalizados</span>
                    <span class="tab-count" id="count-finalizado">0</span>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters-section">
                <input type="text" id="searchTicket" class="filter-input" placeholder="Buscar por código o máquina...">
                <select id="filterPrioridad" class="filter-select">
                    <option value="">Todas las prioridades</option>
                    <option value="Crítica">Crítica</option>
                    <option value="Alta">Alta</option>
                    <option value="Media">Media</option>
                    <option value="Baja">Baja</option>
                </select>
            </div>

            <!-- Lista de Tickets -->
            <div class="tickets-list-container">
                <div class="tickets-grid" id="tickets-grid">
                    <div class="empty-state">
                        <ion-icon name="document-outline"></ion-icon>
                        <p>Cargando tickets...</p>
                    </div>
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

    <script>
        let ticketsData = [];
        let estadoActual = 1; // Por defecto: Pendientes

        $(document).ready(function() {
            cargarTickets();
            setInterval(cargarTickets, 30000); // Recargar cada 30 segundos

            // Auto-ocultar mensaje después de 5 segundos
            setTimeout(function() {
                $('#mensaje-alerta').fadeOut('slow');
            }, 5000);
        });

        function cargarTickets() {
            $.ajax({
                url: 'get_tickets.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    ticketsData = data;
                    actualizarContadores();
                    renderizarTickets();
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar tickets:', error);
                    $('#tickets-grid').html(`
                        <div class="empty-state">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                            <p>Error al cargar los tickets</p>
                        </div>
                    `);
                }
            });
        }

        function actualizarContadores() {
            const pendientes = ticketsData.filter(t => t.id_estado == 1).length;
            const progreso = ticketsData.filter(t => t.id_estado == 2).length;
            const validacion = ticketsData.filter(t => t.id_estado == 3).length;
            const finalizados = ticketsData.filter(t => t.id_estado == 4).length;

            $('#count-pendiente').text(pendientes);
            $('#count-progreso').text(progreso);
            $('#count-validacion').text(validacion);
            $('#count-finalizado').text(finalizados);
        }

        function cambiarEstado(estado) {
            estadoActual = estado;
            
            // Actualizar tabs activos
            $('.tab-estado').removeClass('active');
            $(`.tab-estado[data-estado="${estado}"]`).addClass('active');
            
            renderizarTickets();
        }

        function aplicarFiltros() {
            const searchTerm = $('#searchTicket').val().toLowerCase();
            const prioridad = $('#filterPrioridad').val();

            let filteredData = ticketsData.filter(ticket => {
                // Filtrar por estado actual
                if (ticket.id_estado != estadoActual) return false;

                // Filtrar por búsqueda
                const matchSearch = searchTerm === '' || 
                    ticket.codigo_ticket.toLowerCase().includes(searchTerm) ||
                    ticket.codigo_maquina.toLowerCase().includes(searchTerm);
                
                // Filtrar por prioridad
                const matchPrioridad = prioridad === '' || ticket.prioridad === prioridad;

                return matchSearch && matchPrioridad;
            });

            renderizarTicketsGrid(filteredData);
        }

        function renderizarTickets() {
            const ticketsFiltrados = ticketsData.filter(t => t.id_estado == estadoActual);
            renderizarTicketsGrid(ticketsFiltrados);
        }

        function renderizarTicketsGrid(tickets) {
            const container = $('#tickets-grid');
            container.empty();

            if (tickets.length === 0) {
                const mensajes = {
                    1: 'No hay tickets pendientes',
                    2: 'No hay tickets en progreso',
                    3: 'No hay tickets en validación',
                    4: 'No hay tickets finalizados'
                };
                
                const iconos = {
                    1: 'time-outline',
                    2: 'construct-outline',
                    3: 'checkmark-done-outline',
                    4: 'checkmark-circle-outline'
                };

                container.html(`
                    <div class="empty-state">
                        <ion-icon name="${iconos[estadoActual]}"></ion-icon>
                        <p>${mensajes[estadoActual]}</p>
                    </div>
                `);
                return;
            }

            const estadoClasses = {
                1: 'pendiente',
                2: 'progreso',
                3: 'validacion',
                4: 'finalizado'
            };

            tickets.forEach(ticket => {
                const prioridadClass = 'prioridad-' + ticket.prioridad.toLowerCase();
                const estadoClass = estadoClasses[ticket.id_estado];
                const rol = '<?php echo $_SESSION['rol']; ?>';
                
                let acciones = `
                    <div class="ticket-actions">
                        <a href="ver_tickets.php?id=${ticket.id_ticket}" class="btn-action-small btn-ver">
                            <ion-icon name="eye-outline"></ion-icon> Ver
                        </a>
                `;

                // Botón Editar (solo Admin y Técnico, y NO en tickets finalizados)
                if ((rol === 'Administrador' || rol === 'Técnico') && estadoActual != 4) {
                    acciones += `
                        <a href="editar_tickets.php?id=${ticket.id_ticket}" class="btn-action-small btn-editar">
                            <ion-icon name="create-outline"></ion-icon> Editar
                        </a>
                    `;
                }

                // Botón Eliminar (solo Admin en Pendientes)
                if (rol === 'Administrador' && estadoActual == 1) {
                    acciones += `
                        <a href="#" onclick="confirmarEliminar(${ticket.id_ticket}); return false;" class="btn-action-small btn-eliminar">
                            <ion-icon name="trash-outline"></ion-icon>
                        </a>
                    `;
                }

                acciones += '</div>';

                const responsable = ticket.responsable ? 
                    `<div class="ticket-responsable"><ion-icon name="person-outline"></ion-icon> ${ticket.responsable}</div>` : '';

                const tieneFoto = ticket.foto_url ? 
                    `<div class="ticket-info" style="color: #3B82F6;"><ion-icon name="image-outline"></ion-icon> Con foto</div>` : '';

                const card = `
                    <div class="ticket-card ${estadoClass}" onclick="window.location.href='ver_tickets.php?id=${ticket.id_ticket}'">
                        <div class="ticket-id">${ticket.codigo_ticket}</div>
                        <div class="ticket-maquina">${ticket.codigo_maquina}</div>
                        <div class="ticket-info"><ion-icon name="business-outline"></ion-icon> ${ticket.planta}</div>
                        <div class="ticket-info"><ion-icon name="git-network-outline"></ion-icon> ${ticket.linea}</div>
                        ${tieneFoto}
                        ${responsable}
                        <span class="ticket-prioridad ${prioridadClass}">${ticket.prioridad}</span>
                        ${acciones}
                    </div>
                `;

                container.append(card);
            });
        }

        function confirmarEliminar(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este ticket?')) {
                window.location.href = 'eliminar_ticket.php?id=' + id;
            }
        }

        // Aplicar filtros al escribir
        $('#searchTicket').on('keyup', function() {
            aplicarFiltros();
        });

        $('#filterPrioridad').on('change', function() {
            aplicarFiltros();
        });
    </script>

    <script src="../../assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>
