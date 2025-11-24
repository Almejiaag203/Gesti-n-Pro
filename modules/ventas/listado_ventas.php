<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_SESSION['id_usuario']) || !tienePermiso('ventas_ver_listado')) {
    echo "<div class='alert alert-danger'>No tienes permiso para acceder a esta sección.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Consulta para obtener el historial de ventas
$sql = "SELECT v.id_venta, v.fecha_venta, v.total_venta, v.metodo_pago,
               IFNULL(c.nombre_cliente, 'Venta genérica') as cliente,
               u.nombre_completo as cajero
        FROM ventas v
        LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
        JOIN usuarios u ON v.id_usuario = u.id_usuario
        WHERE v.estado = 'completada'
        ORDER BY v.fecha_venta DESC";

$resultado = $conexion->query($sql);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="m-0"><i class="fas fa-history"></i> Historial de Ventas</h2>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-cash-register"></i> Volver al POS
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Cajero</th>
                        <th>Total</th>
                        <th>Método de Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($venta = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $venta['id_venta']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></td>
                            <td><?php echo htmlspecialchars($venta['cliente']); ?></td>
                            <td><?php echo htmlspecialchars($venta['cajero']); ?></td>
                            <td><?php echo getMoneda(); ?><?php echo number_format($venta['total_venta'], 2); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $venta['metodo_pago'])); ?></td>
                            <td>
                                <a href="ticket.php?id_venta=<?php echo $venta['id_venta']; ?>" class="btn btn-sm btn-info" title="Ver Ticket" target="_blank">
                                    <i class="fas fa-receipt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay ventas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>