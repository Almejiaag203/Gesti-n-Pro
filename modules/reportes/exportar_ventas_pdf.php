<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../dompdf/autoload.inc.php'; // Cargar Dompdf
use Dompdf\Dompdf;

if (!isset($_SESSION['id_usuario']) || !tienePermiso('reportes_ver_ventas')) { exit; }

$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');

// Obtener datos (mismas consultas que en el de Excel)
$stmt = $conexion->prepare("SELECT COUNT(*) as num_ventas, SUM(total_venta) as total_ingresos FROM ventas WHERE estado = 'completada' AND DATE(fecha_venta) BETWEEN ? AND ?");
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$resumen = $stmt->get_result()->fetch_assoc();
$stmt_top = $conexion->prepare("SELECT p.nombre, SUM(dv.cantidad) as total_cantidad FROM detalle_ventas dv JOIN ventas v ON dv.id_venta = v.id_venta JOIN productos p ON dv.id_producto = p.id_producto WHERE v.estado = 'completada' AND DATE(v.fecha_venta) BETWEEN ? AND ? GROUP BY p.nombre ORDER BY total_cantidad DESC LIMIT 5");
$stmt_top->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt_top->execute();
$top_productos = $stmt_top->get_result();

// Construir el HTML para el PDF
$html = '<h1>Reporte de Ventas</h1>';
$html .= '<p>Periodo: ' . $fecha_inicio . ' al ' . $fecha_fin . '</p>';
$html .= '<h2>Resumen General</h2>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
$html .= '<tr><td>Total de Ingresos</td><td>' . getMoneda() . number_format($resumen['total_ingresos'] ?? 0, 2) . '</td></tr>';
$html .= '<tr><td>Numero de Ventas</td><td>' . $resumen['num_ventas'] ?? 0 . '</td></tr>';
$html .= '</table>';
$html .= '<h2>Top 5 Productos Mas Vendidos</h2>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
$html .= '<tr><th>Producto</th><th>Cantidad Vendida</th></tr>';
while($p = $top_productos->fetch_assoc()){
    $html .= '<tr><td>' . htmlspecialchars($p['nombre']) . '</td><td>' . $p['total_cantidad'] . '</td></tr>';
}
$html .= '</table>';

// Instanciar Dompdf y generar el PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("reporte_ventas_" . date('Y-m-d') . ".pdf", ["Attachment" => true]); // Attachment => true para descargar, false para ver en navegador