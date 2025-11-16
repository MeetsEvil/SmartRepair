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
require_once '../../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Crear nuevo Spreadsheet
$spreadsheet = new Spreadsheet();

// ==================== HOJA 1: MÁQUINAS ====================
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Máquinas');

// Encabezados
$headers1 = [
    'ID', 'Código', 'Marca', 'Modelo', 'Número Serie', 'Planta', 'Línea', 
    'Área', 'Estado', 'Fecha Instalación', 'Total Mantenimientos', 
    'Total Tickets', 'Tickets Activos', 'Observaciones', 'Registrado Por'
];

$col = 'A';
foreach ($headers1 as $header) {
    $sheet1->setCellValue($col . '1', $header);
    $sheet1->getStyle($col . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '932323']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
    ]);
    $sheet1->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Consultar máquinas con información completa
$sql_maquinas = "SELECT 
                    m.*,
                    p.nombre_planta,
                    l.nombre_linea,
                    CONCAT(u.nombre, ' ', u.apellido) as creado_por,
                    (SELECT COUNT(*) FROM mantenimientos mt WHERE mt.id_maquina = m.id_maquina) as total_mantenimientos,
                    (SELECT COUNT(*) FROM tickets t WHERE t.id_maquina = m.id_maquina) as total_tickets,
                    (SELECT COUNT(*) FROM tickets t WHERE t.id_maquina = m.id_maquina AND t.id_estado IN (1,2)) as tickets_activos
                FROM maquinas m
                INNER JOIN plantas p ON m.id_planta = p.id_planta
                INNER JOIN lineas l ON m.id_linea = l.id_linea
                LEFT JOIN usuarios u ON m.created_by = u.id_usuario
                ORDER BY m.id_maquina";

$result_maquinas = mysqli_query($conexion, $sql_maquinas);
$row = 2;

while ($maquina = mysqli_fetch_assoc($result_maquinas)) {
    $sheet1->setCellValue('A' . $row, $maquina['id_maquina']);
    $sheet1->setCellValue('B' . $row, $maquina['codigo_maquina']);
    $sheet1->setCellValue('C' . $row, $maquina['marca']);
    $sheet1->setCellValue('D' . $row, $maquina['modelo']);
    $sheet1->setCellValue('E' . $row, $maquina['numero_serie']);
    $sheet1->setCellValue('F' . $row, $maquina['nombre_planta']);
    $sheet1->setCellValue('G' . $row, $maquina['nombre_linea']);
    $sheet1->setCellValue('H' . $row, $maquina['area']);
    $sheet1->setCellValue('I' . $row, $maquina['estado']);
    $sheet1->setCellValue('J' . $row, $maquina['fecha_instalacion'] ? date('d/m/Y', strtotime($maquina['fecha_instalacion'])) : 'N/A');
    $sheet1->setCellValue('K' . $row, $maquina['total_mantenimientos']);
    $sheet1->setCellValue('L' . $row, $maquina['total_tickets']);
    $sheet1->setCellValue('M' . $row, $maquina['tickets_activos']);
    $sheet1->setCellValue('N' . $row, $maquina['observaciones']);
    $sheet1->setCellValue('O' . $row, $maquina['creado_por'] ?? 'N/A');
    
    // Aplicar color según estado
    $colorEstado = 'FFFFFF';
    if ($maquina['estado'] == 'Activa') {
        $colorEstado = 'D4EDDA';
    } elseif ($maquina['estado'] == 'Inactiva') {
        $colorEstado = 'F8D7DA';
    } elseif ($maquina['estado'] == 'Mantenimiento') {
        $colorEstado = 'FFF3CD';
    }
    
    $sheet1->getStyle('I' . $row)->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorEstado]]
    ]);
    
    $row++;
}

// Aplicar bordes a toda la tabla
$sheet1->getStyle('A1:O' . ($row - 1))->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
    ]
]);

// ==================== HOJA 2: MANTENIMIENTOS ====================
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Mantenimientos');

$headers2 = [
    'ID Mantenimiento', 'Código Máquina', 'Marca', 'Modelo', 'Tipo Mantenimiento',
    'Fecha Mantenimiento', 'Técnico Responsable', 'Actividades Realizadas',
    'Repuestos Utilizados', 'Costo', 'Observaciones'
];

$col = 'A';
foreach ($headers2 as $header) {
    $sheet2->setCellValue($col . '1', $header);
    $sheet2->getStyle($col . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '932323']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
    ]);
    $sheet2->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Consultar mantenimientos
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
$row = 2;

while ($mant = mysqli_fetch_assoc($result_mantenimientos)) {
    $sheet2->setCellValue('A' . $row, $mant['id_mantenimiento']);
    $sheet2->setCellValue('B' . $row, $mant['codigo_maquina']);
    $sheet2->setCellValue('C' . $row, $mant['marca']);
    $sheet2->setCellValue('D' . $row, $mant['modelo']);
    $sheet2->setCellValue('E' . $row, $mant['nombre_tipo']);
    $sheet2->setCellValue('F' . $row, date('d/m/Y H:i', strtotime($mant['fecha_mantenimiento'])));
    $sheet2->setCellValue('G' . $row, $mant['tecnico']);
    $sheet2->setCellValue('H' . $row, $mant['actividades_realizadas']);
    $sheet2->setCellValue('I' . $row, $mant['repuestos_utilizados']);
    $sheet2->setCellValue('J' . $row, $mant['costo'] ? '$' . number_format($mant['costo'], 2) : 'N/A');
    $sheet2->setCellValue('K' . $row, $mant['observaciones']);
    $row++;
}

$sheet2->getStyle('A1:K' . ($row - 1))->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
    ]
]);

// ==================== HOJA 3: TICKETS ====================
$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('Tickets');

$headers3 = [
    'ID Ticket', 'Código Ticket', 'Código Máquina', 'Marca', 'Modelo',
    'Prioridad', 'Estado', 'Descripción Falla', 'Fecha Creación',
    'Fecha Resolución', 'Reportado Por', 'Técnico Asignado', 'Solución Aplicada'
];

$col = 'A';
foreach ($headers3 as $header) {
    $sheet3->setCellValue($col . '1', $header);
    $sheet3->getStyle($col . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '932323']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
    ]);
    $sheet3->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Consultar tickets
$sql_tickets = "SELECT 
                    t.*,
                    m.codigo_maquina,
                    m.marca,
                    m.modelo,
                    pr.nombre_prioridad,
                    pr.color as color_prioridad,
                    e.nombre_estado,
                    CONCAT(u1.nombre, ' ', u1.apellido) as reportado_por,
                    CONCAT(u2.nombre, ' ', u2.apellido) as tecnico_asignado
                FROM tickets t
                INNER JOIN maquinas m ON t.id_maquina = m.id_maquina
                INNER JOIN prioridades pr ON t.id_prioridad = pr.id_prioridad
                INNER JOIN estados_ticket e ON t.id_estado = e.id_estado
                LEFT JOIN usuarios u1 ON t.id_usuario_reporta = u1.id_usuario
                LEFT JOIN usuarios u2 ON t.id_tecnico_asignado = u2.id_usuario
                WHERE t.visible = 1
                ORDER BY t.fecha_creacion DESC";

$result_tickets = mysqli_query($conexion, $sql_tickets);
$row = 2;

while ($ticket = mysqli_fetch_assoc($result_tickets)) {
    $sheet3->setCellValue('A' . $row, $ticket['id_ticket']);
    $sheet3->setCellValue('B' . $row, $ticket['codigo_ticket']);
    $sheet3->setCellValue('C' . $row, $ticket['codigo_maquina']);
    $sheet3->setCellValue('D' . $row, $ticket['marca']);
    $sheet3->setCellValue('E' . $row, $ticket['modelo']);
    $sheet3->setCellValue('F' . $row, $ticket['nombre_prioridad']);
    $sheet3->setCellValue('G' . $row, $ticket['nombre_estado']);
    $sheet3->setCellValue('H' . $row, $ticket['descripcion_falla']);
    $sheet3->setCellValue('I' . $row, date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])));
    $sheet3->setCellValue('J' . $row, $ticket['fecha_resolucion'] ? date('d/m/Y H:i', strtotime($ticket['fecha_resolucion'])) : 'Pendiente');
    $sheet3->setCellValue('K' . $row, $ticket['reportado_por'] ?? 'N/A');
    $sheet3->setCellValue('L' . $row, $ticket['tecnico_asignado'] ?? 'Sin asignar');
    $sheet3->setCellValue('M' . $row, $ticket['solucion_aplicada'] ?? 'Pendiente');
    
    // Aplicar color según prioridad
    $colorPrioridad = 'FFFFFF';
    if (strpos($ticket['color_prioridad'], '#') === 0) {
        $colorPrioridad = substr($ticket['color_prioridad'], 1);
    }
    
    $sheet3->getStyle('F' . $row)->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorPrioridad]],
        'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]
    ]);
    
    $row++;
}

$sheet3->getStyle('A1:M' . ($row - 1))->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
    ]
]);

// ==================== HOJA 4: RESUMEN POR MÁQUINA ====================
$sheet4 = $spreadsheet->createSheet();
$sheet4->setTitle('Resumen por Máquina');

$headers4 = [
    'Código Máquina', 'Marca', 'Modelo', 'Estado', 'Total Mantenimientos',
    'Mantenimientos Preventivos', 'Mantenimientos Correctivos', 'Total Tickets',
    'Tickets Activos', 'Tickets Completados', 'Tickets Alta Prioridad',
    'Última Falla', 'Último Mantenimiento'
];

$col = 'A';
foreach ($headers4 as $header) {
    $sheet4->setCellValue($col . '1', $header);
    $sheet4->getStyle($col . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '932323']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
    ]);
    $sheet4->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Consultar resumen por máquina
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
                    COUNT(DISTINCT CASE WHEN t.id_estado = 3 THEN t.id_ticket END) as tickets_completados,
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
$row = 2;

while ($resumen = mysqli_fetch_assoc($result_resumen)) {
    $sheet4->setCellValue('A' . $row, $resumen['codigo_maquina']);
    $sheet4->setCellValue('B' . $row, $resumen['marca']);
    $sheet4->setCellValue('C' . $row, $resumen['modelo']);
    $sheet4->setCellValue('D' . $row, $resumen['estado']);
    $sheet4->setCellValue('E' . $row, $resumen['total_mantenimientos']);
    $sheet4->setCellValue('F' . $row, $resumen['mant_preventivos']);
    $sheet4->setCellValue('G' . $row, $resumen['mant_correctivos']);
    $sheet4->setCellValue('H' . $row, $resumen['total_tickets']);
    $sheet4->setCellValue('I' . $row, $resumen['tickets_activos']);
    $sheet4->setCellValue('J' . $row, $resumen['tickets_completados']);
    $sheet4->setCellValue('K' . $row, $resumen['tickets_alta_prioridad']);
    $sheet4->setCellValue('L' . $row, $resumen['ultima_falla'] ? date('d/m/Y', strtotime($resumen['ultima_falla'])) : 'Sin fallas');
    $sheet4->setCellValue('M' . $row, $resumen['ultimo_mantenimiento'] ? date('d/m/Y', strtotime($resumen['ultimo_mantenimiento'])) : 'Sin mantenimientos');
    $row++;
}

$sheet4->getStyle('A1:M' . ($row - 1))->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
    ]
]);

// ==================== HOJA 5: ESTADÍSTICAS GENERALES ====================
$sheet5 = $spreadsheet->createSheet();
$sheet5->setTitle('Estadísticas Generales');

// Título
$sheet5->setCellValue('A1', 'ESTADÍSTICAS GENERALES DEL SISTEMA');
$sheet5->mergeCells('A1:D1');
$sheet5->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '932323']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
]);
$sheet5->getRowDimension('1')->setRowHeight(30);

// Estadísticas
$stats = [
    ['Métrica', 'Valor', '', ''],
    ['Total de Máquinas', mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM maquinas")), '', ''],
    ['Máquinas Activas', mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM maquinas WHERE estado = 'Activa'")), '', ''],
    ['Máquinas en Mantenimiento', mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM maquinas WHERE estado = 'Mantenimiento'")), '', ''],
    ['Total Mantenimientos Realizados', mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM mantenimientos")), '', ''],
    ['Total Tickets Generados', mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM tickets WHERE visible = 1")), '', ''],
    ['Tickets Activos', mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM tickets WHERE id_estado IN (1,2) AND visible = 1")), '', ''],
    ['Tickets Completados', mysqli_num_rows(mysqli_query($conexion, "SELECT * FROM tickets WHERE id_estado = 3 AND visible = 1")), '', ''],
];

$row = 3;
foreach ($stats as $stat) {
    $sheet5->setCellValue('A' . $row, $stat[0]);
    $sheet5->setCellValue('B' . $row, $stat[1]);
    
    if ($row == 3) {
        $sheet5->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
    }
    
    $row++;
}

$sheet5->getColumnDimension('A')->setWidth(40);
$sheet5->getColumnDimension('B')->setWidth(20);

$sheet5->getStyle('A3:B' . ($row - 1))->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
    ]
]);

// Fecha de generación
$sheet5->setCellValue('A' . ($row + 2), 'Reporte generado el: ' . date('d/m/Y H:i:s'));
$sheet5->setCellValue('A' . ($row + 3), 'Generado por: ' . $_SESSION['usuarioingresando']);

mysqli_close($conexion);

// Configurar la primera hoja como activa
$spreadsheet->setActiveSheetIndex(0);

// Generar archivo Excel
$filename = 'Reporte_Completo_Maquinas_' . date('Y-m-d_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
