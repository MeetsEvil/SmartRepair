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
            background: linear-gradient(90deg, #932323, #4d0d0d);
            border: none;
            color: white;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }

        .btn-new:hover {
            background: linear-gradient(90deg, #7b1f1f, #430c0c);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
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

        /* Kanban Board */
        .kanban-board {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            height: calc(100% - 150px);
            overflow: hidden;
        }

        .kanban-column {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .kanban-column.pendiente {
            border: 3px solid #DC2626;
        }

        .kanban-column.progreso {
            border: 3px solid #F59E0B;
        }

        .kanban-column.validacion {
            border: 3px solid #3B82F6;
        }

        .kanban-column.finalizado {
            border: 3px solid #10B981;
        }

        .column-header {
            font-weight: 700;
            font-size: 1.1em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .column-header .count {
            background: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.9em;
        }

        .pendiente .column-header {
            color: #DC2626;
        }

        .progreso .column-header {
            color: #F59E0B;
        }

        .validacion .column-header {
            color: #3B82F6;
        }

        .finalizado .column-header {
            color: #10B981;
        }

        .tickets-list {
            flex: 1;
            overflow-y: auto;
            padding-right: 5px;
        }

        .tickets-list::-webkit-scrollbar {
            width: 6px;
        }

        .tickets-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .tickets-list::-webkit-scrollbar-thumb {
            background: #932323;
            border-radius: 10px;
        }

        /* Tarjeta de Ticket */
        .ticket-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
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

        .empty-column {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-column ion-icon {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.5;
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
                <button onclick="aplicarFiltros()" class="btn-new" style="padding: 8px 20px;">
                    <ion-icon name="filter-outline"></ion-icon> Filtrar
                </button>
            </div>

            <!-- Kanban Board -->
            <div class="kanban-board">
                <!-- Columna: Pendientes -->
                <div class="kanban-column pendiente">
                    <div class="column-header">
                        <span>Pendientes</span>
                        <span class="count" id="count-pendiente">0</span>
                    </div>
                    <div class="tickets-list" id="tickets-pendiente">
                        <div class="empty-column">
                            <ion-icon name="time-outline"></ion-icon>
                            <p>No hay tickets pendientes</p>
                        </div>
                    </div>
                </div>

                <!-- Columna: En Progreso -->
                <div class="kanban-column progreso">
                    <div class="column-header">
                        <span>En Progreso</span>
                        <span class="count" id="count-progreso">0</span>
                    </div>
                    <div class="tickets-list" id="tickets-progreso">
                        <div class="empty-column">
                            <ion-icon name="construct-outline"></ion-icon>
                            <p>No hay tickets en progreso</p>
                        </div>
                    </div>
                </div>

                <!-- Columna: En Validación -->
                <div class="kanban-column validacion">
                    <div class="column-header">
                        <span>En Validación</span>
                        <span class="count" id="count-validacion">0</span>
                    </div>
                    <div class="tickets-list" id="tickets-validacion">
                        <div class="empty-column">
                            <ion-icon name="checkmark-done-outline"></ion-icon>
                            <p>No hay tickets en validación</p>
                        </div>
                    </div>
                </div>

                <!-- Columna: Finalizados -->
                <div class="kanban-column finalizado">
                    <div class="column-header">
                        <span>Finalizados</span>
                        <span class="count" id="count-finalizado">0</span>
                    </div>
                    <div class="tickets-list" id="tickets-finalizado">
                        <div class="empty-column">
                            <ion-icon name="checkmark-circle-outline"></ion-icon>
                            <p>No hay tickets finalizados</p>
                        </div>
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
        let filteredData = [];

        $(document).ready(function() {
            cargarTickets();
            setInterval(cargarTickets, 30000); // Recargar cada 30 segundos
        });

        function cargarTickets() {
            $.ajax({
                url: 'get_tickets.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    ticketsData = data;
                    filteredData = data;
                    renderizarTickets();
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar tickets:', error);
                }
            });
        }

        function aplicarFiltros() {
            const searchTerm = $('#searchTicket').val().toLowerCase();
            const prioridad = $('#filterPrioridad').val();

            filteredData = ticketsData.filter(ticket => {
                const matchSearch = searchTerm === '' || 
                    ticket.codigo_ticket.toLowerCase().includes(searchTerm) ||
                    ticket.codigo_maquina.toLowerCase().includes(searchTerm);
                
                const matchPrioridad = prioridad === '' || ticket.prioridad === prioridad;

                return matchSearch && matchPrioridad;
            });

            renderizarTickets();
        }

        function renderizarTickets() {
            // Limpiar columnas
            $('#tickets-pendiente, #tickets-progreso, #tickets-validacion, #tickets-finalizado').empty();

            // Agrupar por estado
            const pendientes = filteredData.filter(t => t.id_estado == 1);
            const progreso = filteredData.filter(t => t.id_estado == 2);
            const validacion = filteredData.filter(t => t.id_estado == 3);
            const finalizados = filteredData.filter(t => t.id_estado == 4);

            // Actualizar contadores
            $('#count-pendiente').text(pendientes.length);
            $('#count-progreso').text(progreso.length);
            $('#count-validacion').text(validacion.length);
            $('#count-finalizado').text(finalizados.length);

            // Renderizar cada columna
            renderizarColumna(pendientes, '#tickets-pendiente', 'pendiente');
            renderizarColumna(progreso, '#tickets-progreso', 'progreso');
            renderizarColumna(validacion, '#tickets-validacion', 'validacion');
            renderizarColumna(finalizados, '#tickets-finalizado', 'finalizado');
        }

        function renderizarColumna(tickets, selector, tipo) {
            const container = $(selector);

            if (tickets.length === 0) {
                const mensajes = {
                    'pendiente': 'No hay tickets pendientes',
                    'progreso': 'No hay tickets en progreso',
                    'validacion': 'No hay tickets en validación',
                    'finalizado': 'No hay tickets finalizados'
                };
                container.html(`
                    <div class="empty-column">
                        <ion-icon name="document-outline"></ion-icon>
                        <p>${mensajes[tipo]}</p>
                    </div>
                `);
                return;
            }

            tickets.forEach(ticket => {
                const prioridadClass = 'prioridad-' + ticket.prioridad.toLowerCase();
                const rol = '<?php echo $_SESSION['rol']; ?>';
                
                let acciones = `
                    <div class="ticket-actions">
                        <a href="ver_tickets.php?id=${ticket.id_ticket}" class="btn-action-small btn-ver">
                            <ion-icon name="eye-outline"></ion-icon> Ver
                        </a>
                `;

                // Botón Editar (solo Admin y Técnico)
                if (rol === 'Administrador' || rol === 'Técnico') {
                    acciones += `
                        <a href="editar_tickets.php?id=${ticket.id_ticket}" class="btn-action-small btn-editar">
                            <ion-icon name="create-outline"></ion-icon> Editar
                        </a>
                    `;
                }

                // Botón Eliminar (solo Admin en Pendientes)
                if (rol === 'Administrador' && tipo === 'pendiente') {
                    acciones += `
                        <a href="#" onclick="confirmarEliminar(${ticket.id_ticket}); return false;" class="btn-action-small btn-eliminar">
                            <ion-icon name="trash-outline"></ion-icon>
                        </a>
                    `;
                }

                acciones += '</div>';

                const responsable = ticket.responsable ? 
                    `<div class="ticket-responsable"><ion-icon name="person-outline"></ion-icon> ${ticket.responsable}</div>` : '';

                const card = `
                    <div class="ticket-card" onclick="window.location.href='ver_tickets.php?id=${ticket.id_ticket}'">
                        <div class="ticket-id">${ticket.codigo_ticket}</div>
                        <div class="ticket-maquina">${ticket.codigo_maquina}</div>
                        <div class="ticket-info"><ion-icon name="business-outline"></ion-icon> ${ticket.planta}</div>
                        <div class="ticket-info"><ion-icon name="git-network-outline"></ion-icon> ${ticket.linea}</div>
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
