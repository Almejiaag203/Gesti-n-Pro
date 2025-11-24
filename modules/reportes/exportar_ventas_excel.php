<?php
require_once __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['id_usuario']) || !tienePermiso('reportes_ver_ventas')) { exit; }

$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');

// Obtener datos del resumen
$stmt = $conexion->prepare("SELECT COUNT(*) as num_ventas, SUM(total_venta) as total_ingresos FROM ventas WHERE estado = 'completada' AND DATE(fecha_venta) BETWEEN ? AND ?");
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$resumen = $stmt->get_result()->fetch_assoc();

// Obtener top productos
$stmt_top = $conexion->prepare("SELECT p.nombre, SUM(dv.cantidad) as total_cantidad FROM detalle_ventas dv JOIN ventas v ON dv.id_venta = v.id_venta JOIN productos p ON dv.id_producto = p.id_producto WHERE v.estado = 'completada' AND DATE(v.fecha_venta) BETWEEN ? AND ? GROUP BY p.nombre ORDER BY total_cantidad DESC LIMIT 5");
$stmt_top->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt_top->execute();
$top_productos = $stmt_top->get_result();

// Encabezados para forzar la descarga en formato Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_ventas_" . date('Y-m-d') . ".xls");

?>
<h3>Reporte de Ventas (<?php echo $fecha_inicio . " al " . $fecha_fin; ?>)</h3>
<table border="1">
    <tr>
        <th colspan="2">Resumen General</th>
    </tr>
    <tr>
        <td>Total de Ingresos</td>
        <td><?php echo getMoneda() . number_format($resumen['total_ingresos'] ?? 0, 2); ?></td>
    </tr>
    <tr>
        <td>Numero de Ventas</td>
        <td><?php echo $resumen['num_ventas'] ?? 0; ?></td>
    </tr>
</table>
<br>
<table border="1">
    <tr>
        <th colspan="2">Top 5 Productos Mas Vendidos</th>
    </tr>
    <tr>
        <th>Producto</th>
        <th>Cantidad Vendida</th>
    </tr>
    <?php while($p = $top_productos->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($p['nombre']); ?></td>
        <td><?php echo $p['total_cantidad']; ?></td>
    </tr>
    <?php endwhile; ?>
</table>