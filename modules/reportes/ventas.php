<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_SESSION['id_usuario']) || !tienePermiso('reportes_ver_ventas')) {
    echo "<div class='alert alert-danger'>No tienes permiso para acceder.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Establecer fechas por defecto: el mes actual
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');

// 1. Resumen general
$stmt = $conexion->prepare("SELECT 
                                COUNT(*) as num_ventas, 
                                SUM(total_venta) as total_ingresos 
                            FROM ventas 
                            WHERE estado = 'completada' AND DATE(fecha_venta) BETWEEN ? AND ?");
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$resumen = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Productos más vendidos
$stmt_top = $conexion->prepare("SELECT p.nombre, SUM(dv.cantidad) as total_cantidad
                                FROM detalle_ventas dv
                                JOIN ventas v ON dv.id_venta = v.id_venta
                                JOIN productos p ON dv.id_producto = p.id_producto
                                WHERE v.estado = 'completada' AND DATE(v.fecha_venta) BETWEEN ? AND ?
                                GROUP BY p.nombre
                                ORDER BY total_cantidad DESC
                                LIMIT 5");
$stmt_top->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt_top->execute();
$top_productos = $stmt_top->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_top->close();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4"><i class="fas fa-chart-bar"></i> Reporte de Ventas</h1>
        <a href="index.php" class="btn btn-secondary">Volver al Menú</a>
    </div>
    
    <div class="card shadow-sm mb-4 mt-3">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
                </div>
                <div class="col-md-5">
                    <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Generar Reporte</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-start">
            <button onclick="window.print();" class="btn btn-info text-white me-2"><i class="fas fa-print"></i> Imprimir</button>
            <a href="exportar_ventas_excel.php?fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>" class="btn btn-success me-2"><i class="fas fa-file-excel"></i> Exportar a Excel</a>
            <a href="exportar_ventas_pdf.php?fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Exportar a PDF</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Resumen General</div>
                <div class="card-body">
                    <h4>Total de Ingresos: <span class="text-success"><?php echo getMoneda(); ?><?php echo number_format($resumen['total_ingresos'] ?? 0, 2); ?></span></h4>
                    <p class="fs-5">Número de Ventas: <?php echo $resumen['num_ventas'] ?? 0; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Top 5 Productos Más Vendidos</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($top_productos)): ?>
                            <?php foreach ($top_productos as $producto): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $producto['total_cantidad']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item">No hay datos para este período.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>