<?php
require_once __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['id_usuario'])) { redirigir('login.php'); }

if (!isset($_GET['id_venta'])) {
    echo "Venta no encontrada.";
    exit;
}
$id_venta = (int)$_GET['id_venta'];

// Obtener datos de la venta
$sql_venta = "SELECT v.*, c.nombre_cliente, u.nombre_completo as cajero
              FROM ventas v
              LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
              JOIN usuarios u ON v.id_usuario = u.id_usuario
              WHERE v.id_venta = ?";
$stmt_venta = $conexion->prepare($sql_venta);
$stmt_venta->bind_param("i", $id_venta);
$stmt_venta->execute();
$venta = $stmt_venta->get_result()->fetch_assoc();

// Obtener detalles de la venta
$sql_detalle = "SELECT d.*, p.nombre as producto_nombre
                FROM detalle_ventas d
                JOIN productos p ON d.id_producto = p.id_producto
                WHERE d.id_venta = ?";
$stmt_detalle = $conexion->prepare($sql_detalle);
$stmt_detalle->bind_param("i", $id_venta);
$stmt_detalle->execute();
$detalles = $stmt_detalle->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta #<?php echo $venta['id_venta']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Courier New', Courier, monospace; }
        .ticket { max-width: 350px; margin: 20px auto; padding: 15px; border: 1px solid #ccc; }
        @media print {
            body * { visibility: hidden; }
            .ticket, .ticket * { visibility: visible; }
            .ticket { position: absolute; left: 0; top: 0; width: 100%; border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <?php if ($venta): ?>
    <div class="ticket">
        <h4 class="text-center"><?php echo getConfig('nombre_tienda'); ?></h4>
        <p class="text-center">Ticket de Venta #: <?php echo $venta['id_venta']; ?></p>
        <hr>
        <p>Fecha: <?php echo date('d/m/Y H:i:s', strtotime($venta['fecha_venta'])); ?></p>
        <p>Cajero: <?php echo htmlspecialchars($venta['cajero']); ?></p>
        <p>Cliente: <?php echo htmlspecialchars($venta['nombre_cliente'] ?? 'Varios'); ?></p>
        <hr>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Cant.</th>
                    <th>Producto</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($detalles->num_rows > 0): ?>
                    <?php while($item = $detalles->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $item['cantidad']; ?></td>
                        <td><?php echo htmlspecialchars($item['producto_nombre']); ?></td>
                        <td class="text-end"><?php echo getMoneda() . number_format($item['cantidad'] * $item['precio_unitario'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <hr>
        <h5 class="text-end">TOTAL: <?php echo getMoneda() . number_format($venta['total_venta'], 2); ?></h5>
        <p class="text-center mt-3">¡Gracias por su compra!</p>
    </div>
    <?php else: ?>
        <p class="text-center">No se encontró la venta solicitada.</p>
    <?php endif; ?>

    <div class="text-center no-print mt-3">
        <button onclick="window.print();" class="btn btn-primary">Imprimir Ticket</button>
        <a href="index.php" class="btn btn-secondary">Volver al POS</a>
        <a href="listado_ventas.php" class="btn btn-info text-white">Volver al Listado de Ventas</a>
    </div>
</body>
</html>