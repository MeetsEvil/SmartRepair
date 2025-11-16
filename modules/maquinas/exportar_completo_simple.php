<?php
session_start();
if (!isset($_SESSION['usuarioingresando'])) {
    header("Location: ../main/index.php");
    exit();
}

// Solo Admin y Técnico pueden exportar
if ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Técnico') {
    header("Location: index_maquinas.php");
    exit();
}

require_once '../../config/db.php';

// Nombre del archivo
$filename = 'Reporte_Completo_Maquinas_' . date('Y-m-d_His') . '.xls';

// Headers para descarga de Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Iniciar salida HTML para Excel
echo "\xEF\xBB\xBF"; // UTF-8 BOM

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 30px;
        }
        th {
            background-color: #932323;
            color: white;
            font-weight: bold;
            padding: 10px;
            border: 1px solid black;
            text-align: center;
        }
        td {
            padding: 8px;
            border: 1px solid black;
            text-align: left;
        }
        .titulo {
            font-size: 18px;
            font-weight: bold;
            color: #932323;
            margin: 20px 0 10px 0;
        }
        .activa {
            background-color: #d4edda;
        }
        .inactiva {
            background-color: #f8d7da;
        }
        .mantenimiento {
            background-color: #fff3cd;
        }
        .alta {
            background-color: #dc3545;
            color: white;
            font-weight: bold;
        }
        .media {
            background-color: #ffc107;
            font-weight: bold;
        }
        .baja {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- ==================== SECCIÓN 1: MÁQUINAS ==================== -->
<h2 class="titulo">REPORTE COMPLETO DE MÁQUINAS</h2>
<p><strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
<p><strong>Generado por:</strong> <?php echo htmlspecialchars($_SESSION['usuarioingresando']); ?></p>

<h3 class="titulo">1. INFORMACIÓN DE MÁQUINAS</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Número Serie</th>
            <th>Planta</th>
            <th>Línea</th>
            <th>Área</th>
            <th>Estado</th>
            <th>Fecha Instalación</th>
            <th>Total Mantenimientos</th>
            <th>Total Tickets</th>
            <th>Tickets Activos</th>
            <th>Observaciones</th>
            <th>Registrado Por</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql_maquinas = "SELECT 
                            m.*,
                            p.nombre_planta,
                            l.nombre_linea,
                            CONCAT(u.nombre, ' ', u.apellido) as creado_por,
                            (SELECT COUNT(*) FROM mantenimientos mt WHERE mt.id_maquina = m.id_maquina) as total_mantenimientos,
                            (SELECT COUNT(*) FROM tickets t WHERE t.id_maquina = m.id_maquina AND t.visible=1) as total_tickets,
                            (SELECT COUNT(*) FROM tickets t WHERE t.id_maquina = m.id_maquina AND t.id_estado IN (1,2)) as tickets_activos
                        FROM maquinas m
                        INNER JOIN plantas p ON m.id_planta = p.id_planta
                        INNER JOIN lineas l ON m.id_linea = l.id_linea
                        LEFT JOIN usuarios u ON m.created_by = u.id_usuario
                        ORDER BY m.id_maquina";
        
        $result_maquinas = mysqli_query($conexion, $sql_maquinas);
        
        if ($result_maquinas && mysqli_num_rows($result_maquinas) > 0) {
            while ($maquina = mysqli_fetch_assoc($result_maquinas)) {
            $clase_estado = strtolower($maquina['estado']);
            echo "<tr>";
            echo "<td>" . $maquina['id_maquina'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($maquina['codigo_maquina']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($maquina['marca']) . "</td>";
            echo "<td>" . htmlspecialchars($maquina['modelo']) . "</td>";
            echo "<td>" . htmlspecialchars($maquina['numero_serie']) . "</td>";
            echo "<td>" . htmlspecialchars($maquina['nombre_planta']) . "</td>";
            echo "<td>" . htmlspecialchars($maquina['nombre_linea']) . "</td>";
            echo "<td>" . htmlspecialchars($maquina['area']) . "</td>";
            echo "<td class='$clase_estado'>" . $maquina['estado'] . "</td>";
            echo "<td>" . ($maquina['fecha_instalacion'] ? date('d/m/Y', strtotime($maquina['fecha_instalacion'])) : 'N/A') . "</td>";
            echo "<td style='text-align: center;'>" . $maquina['total_mantenimientos'] . "</td>";
            echo "<td style='text-align: center;'>" . $maquina['total_tickets'] . "</td>";
            echo "<td style='text-align: center;'>" . $maquina['tickets_activos'] . "</td>";
            echo "<td>" . htmlspecialchars($maquina['observaciones']) . "</td>";
            echo "<td>" . htmlspecialchars($maquina['creado_por'] ?? 'N/A') . "</td>";
            echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='15' style='text-align: center; padding: 20px;'>No hay máquinas registradas</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- ==================== SECCIÓN 2: MANTENIMIENTOS ==================== -->
<h3 class="titulo">2. HISTORIAL DE MANTENIMIENTOS</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Código Máquina</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Tipo Mantenimiento</th>
            <th>Fecha</th>
            <th>Técnico Responsable</th>
            <th>Actividades Realizadas</th>
            <th>Repuestos Utilizados</th>
            <!-- <th>Costo</th> -->
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql_mantenimientos = "SELECT 
                                mt.*,
                                m.codigo_maquina,
                                m.marca,
                                m.modelo,
                                tm.nombre_tipo,
                                CONCAT(u.nombre, ' ', u.apellido) as tecnico
                            FROM mantenimientos mt
                            INNER JOIN maquinas m ON mt.id_maquina = m.id_maquina
                            INNER JOIN tipos_mantenimiento tm ON mt.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
                            INNER JOIN usuarios u ON mt.id_tecnico_responsable = u.id_usuario
                            ORDER BY mt.fecha_mantenimiento DESC";
        
        $result_mantenimientos = mysqli_query($conexion, $sql_mantenimientos);
        
        if ($result_mantenimientos && mysqli_num_rows($result_mantenimientos) > 0) {
            while ($mant = mysqli_fetch_assoc($result_mantenimientos)) {
            echo "<tr>";
            echo "<td>" . $mant['id_mantenimiento'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($mant['codigo_maquina']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($mant['marca']) . "</td>";
            echo "<td>" . htmlspecialchars($mant['modelo']) . "</td>";
            echo "<td>" . $mant['nombre_tipo'] . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($mant['fecha_mantenimiento'])) . "</td>";
            echo "<td>" . htmlspecialchars($mant['tecnico']) . "</td>";
            echo "<td>" . htmlspecialchars($mant['actividades_realizadas']) . "</td>";
            echo "<td>" . htmlspecialchars($mant['repuestos_utilizados']) . "</td>";
            // echo "<td>" . (isset($mant['costo']) && $mant['costo'] ? '$' . number_format($mant['costo'], 2) : 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($mant['observaciones'] ?? '') . "</td>";
            echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='11' style='text-align: center; padding: 20px;'>No hay mantenimientos registrados</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- ==================== SECCIÓN 3: TICKETS ==================== -->
<h3 class="titulo">3. HISTORIAL DE TICKETS</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Código Ticket</th>
            <th>Código Máquina</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Descripción Falla</th>
            <th>Fecha Creación</th>
            <th>Fecha Resolución</th>
            <th>Reportado Por</th>
            <th>Técnico Asignado</th>
            <th>Solución Aplicada</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql_tickets = "SELECT 
                            t.*,
                            m.codigo_maquina,
                            m.marca,
                            m.modelo,
                            pr.nombre_prioridad,
                            e.nombre_estado,
                            CONCAT(u1.nombre, ' ', u1.apellido) as reportado_por,
                            CONCAT(u2.nombre, ' ', u2.apellido) as tecnico_asignado
                        FROM tickets t
                        INNER JOIN maquinas m ON t.id_maquina = m.id_maquina
                        INNER JOIN prioridades pr ON t.id_prioridad = pr.id_prioridad
                        INNER JOIN estados_ticket e ON t.id_estado = e.id_estado
                        LEFT JOIN usuarios u1 ON t.id_usuario_reporta = u1.id_usuario
                        LEFT JOIN usuarios u2 ON t.id_tecnico_responsable = u2.id_usuario
                        WHERE t.visible = 1
                        ORDER BY t.fecha_creacion DESC";
        
        $result_tickets = mysqli_query($conexion, $sql_tickets);
        
        if ($result_tickets && mysqli_num_rows($result_tickets) > 0) {
            while ($ticket = mysqli_fetch_assoc($result_tickets)) {
            $clase_prioridad = '';
            if ($ticket['id_prioridad'] == 1) $clase_prioridad = 'alta';
            elseif ($ticket['id_prioridad'] == 2) $clase_prioridad = 'media';
            elseif ($ticket['id_prioridad'] == 3) $clase_prioridad = 'baja';
            
            echo "<tr>";
            echo "<td>" . $ticket['id_ticket'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($ticket['codigo_ticket']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($ticket['codigo_maquina']) . "</td>";
            echo "<td>" . htmlspecialchars($ticket['marca']) . "</td>";
            echo "<td>" . htmlspecialchars($ticket['modelo']) . "</td>";
            echo "<td class='$clase_prioridad'>" . $ticket['nombre_prioridad'] . "</td>";
            echo "<td>" . $ticket['nombre_estado'] . "</td>";
            echo "<td>" . htmlspecialchars($ticket['descripcion_falla']) . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) . "</td>";
            echo "<td>" . ($ticket['fecha_resolucion'] ? date('d/m/Y H:i', strtotime($ticket['fecha_resolucion'])) : 'Pendiente') . "</td>";
            echo "<td>" . htmlspecialchars($ticket['reportado_por'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($ticket['tecnico_asignado'] ?? 'Sin asignar') . "</td>";
            echo "<td>" . htmlspecialchars($ticket['solucion_aplicada'] ?? 'Pendiente') . "</td>";
            echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='13' style='text-align: center; padding: 20px;'>No hay tickets registrados</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- ==================== SECCIÓN 4: RESUMEN POR MÁQUINA ==================== -->
<h3 class="titulo">4. RESUMEN POR MÁQUINA</h3>
<table>
    <thead>
        <tr>
            <th>Código Máquina</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Estado</th>
            <th>Total Mantenimientos</th>
            <th>Mant. Preventivos</th>
            <th>Mant. Correctivos</th>
            <th>Total Tickets</th>
            <th>Tickets Activos</th>
            <th>Tickets Completados</th>
            <th>Tickets Alta Prioridad</th>
            <th>Última Falla</th>
            <th>Último Mantenimiento</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql_resumen = "SELECT 
                            m.codigo_maquina,
                            m.marca,
                            m.modelo,
                            m.estado,
                            COUNT(DISTINCT mt.id_mantenimiento) as total_mantenimientos,
                            COUNT(DISTINCT CASE WHEN tm.nombre_tipo = 'Preventivo' THEN mt.id_mantenimiento END) as mant_preventivos,
                            COUNT(DISTINCT CASE WHEN tm.nombre_tipo = 'Correctivo' THEN mt.id_mantenimiento END) as mant_correctivos,
                            COUNT(DISTINCT t.id_ticket) as total_tickets,
                            COUNT(DISTINCT CASE WHEN t.id_estado IN (1,2) THEN t.id_ticket END) as tickets_activos,
                            COUNT(DISTINCT CASE WHEN t.id_estado = 4 THEN t.id_ticket END) as tickets_completados,
                            COUNT(DISTINCT CASE WHEN t.id_prioridad = 1 THEN t.id_ticket END) as tickets_alta_prioridad,
                            MAX(t.fecha_creacion) as ultima_falla,
                            MAX(mt.fecha_mantenimiento) as ultimo_mantenimiento
                        FROM maquinas m
                        LEFT JOIN mantenimientos mt ON m.id_maquina = mt.id_maquina
                        LEFT JOIN tipos_mantenimiento tm ON mt.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
                        LEFT JOIN tickets t ON m.id_maquina = t.id_maquina AND t.visible = 1
                        GROUP BY m.id_maquina, m.codigo_maquina, m.marca, m.modelo, m.estado
                        ORDER BY m.codigo_maquina";
        
        $result_resumen = mysqli_query($conexion, $sql_resumen);
        
        if ($result_resumen && mysqli_num_rows($result_resumen) > 0) {
            while ($resumen = mysqli_fetch_assoc($result_resumen)) {
            $clase_estado = strtolower($resumen['estado']);
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($resumen['codigo_maquina']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($resumen['marca']) . "</td>";
            echo "<td>" . htmlspecialchars($resumen['modelo']) . "</td>";
            echo "<td class='$clase_estado'>" . $resumen['estado'] . "</td>";
            echo "<td style='text-align: center;'>" . $resumen['total_mantenimientos'] . "</td>";
            echo "<td style='text-align: center;'>" . $resumen['mant_preventivos'] . "</td>";
            echo "<td style='text-align: center;'>" . $resumen['mant_correctivos'] . "</td>";
            echo "<td style='text-align: center;'>" . $resumen['total_tickets'] . "</td>";
            echo "<td style='text-align: center;'>" . $resumen['tickets_activos'] . "</td>";
            echo "<td style='text-align: center;'>" . $resumen['tickets_completados'] . "</td>";
            echo "<td style='text-align: center;'>" . $resumen['tickets_alta_prioridad'] . "</td>";
            echo "<td>" . ($resumen['ultima_falla'] ? date('d/m/Y', strtotime($resumen['ultima_falla'])) : 'Sin fallas') . "</td>";
            echo "<td>" . ($resumen['ultimo_mantenimiento'] ? date('d/m/Y', strtotime($resumen['ultimo_mantenimiento'])) : 'Sin mantenimientos') . "</td>";
            echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='13' style='text-align: center; padding: 20px;'>No hay datos de resumen disponibles</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- ==================== SECCIÓN 5: ESTADÍSTICAS GENERALES ==================== -->
<h3 class="titulo">5. ESTADÍSTICAS GENERALES DEL SISTEMA</h3>
<table style="width: 50%;">
    <thead>
        <tr>
            <th>Métrica</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_maquinas = mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM maquinas"));
        $maquinas_activas = mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM maquinas WHERE estado = 'Activa'"));
        $maquinas_mantenimiento = mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM maquinas WHERE estado = 'Mantenimiento'"));
        $total_mantenimientos = mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM mantenimientos"));
        $total_tickets = mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM tickets WHERE visible = 1"));
        $tickets_activos = mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM tickets WHERE id_estado IN (1,2) AND visible = 1"));
        $tickets_completados = mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM tickets WHERE id_estado = 4 AND visible = 1"));
        ?>
        <tr>
            <td><strong>Total de Máquinas</strong></td>
            <td style="text-align: center;"><?php echo $total_maquinas; ?></td>
        </tr>
        <tr>
            <td><strong>Máquinas Activas</strong></td>
            <td style="text-align: center;"><?php echo $maquinas_activas; ?></td>
        </tr>
        <!-- <tr>
            <td><strong>Máquinas en Mantenimiento</strong></td>
            <td style="text-align: center;"><?php echo $maquinas_mantenimiento; ?></td>
        </tr> -->
        <tr>
            <td><strong>Total Mantenimientos Realizados</strong></td>
            <td style="text-align: center;"><?php echo $total_mantenimientos; ?></td>
        </tr>
        <tr>
            <td><strong>Total Tickets Generados</strong></td>
            <td style="text-align: center;"><?php echo $total_tickets; ?></td>
        </tr>
        <tr>
            <td><strong>Tickets Activos</strong></td>
            <td style="text-align: center;"><?php echo $tickets_activos; ?></td>
        </tr>
        <tr>
            <td><strong>Tickets Completados</strong></td>
            <td style="text-align: center;"><?php echo $tickets_completados; ?></td>
        </tr>
    </tbody>
</table>

<br><br>
<p><strong>Fin del Reporte</strong></p>
<p><em>Este reporte fue generado automáticamente por el Sistema SmartRepair</em></p>

</body>
</html>

<?php
mysqli_close($conexion);
?>
