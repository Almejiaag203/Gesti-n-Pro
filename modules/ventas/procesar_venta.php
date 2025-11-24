<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario']) || !tienePermiso('ventas_crear')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_cliente = !empty($data['id_cliente']) ? (int)$data['id_cliente'] : null;
$metodo_pago = $data['metodo_pago'];
$carrito = $data['carrito'];
$total_venta = 0;

if (empty($carrito)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El carrito está vacío.']);
    exit;
}

$conexion->begin_transaction();
try {
    // 1. Recalcular el total en el backend por seguridad
    foreach ($carrito as $item) {
        $stmt_precio = $conexion->prepare("SELECT precio_venta FROM productos WHERE id_producto = ?");
        $stmt_precio->bind_param('i', $item['id']);
        $stmt_precio->execute();
        $resultado_precio = $stmt_precio->get_result()->fetch_assoc();
        if ($resultado_precio) {
            $total_venta += $resultado_precio['precio_venta'] * $item['cantidad'];
        }
        $stmt_precio->close();
    }
    
    // 2. Insertar en la tabla 'ventas'
    $stmt_venta = $conexion->prepare("INSERT INTO ventas (id_cliente, id_usuario, total_venta, metodo_pago) VALUES (?, ?, ?, ?)");
    $stmt_venta->bind_param("iids", $id_cliente, $_SESSION['id_usuario'], $total_venta, $metodo_pago);
    $stmt_venta->execute();
    $id_venta = $conexion->insert_id;
    $stmt_venta->close();

    // 3. Insertar en 'detalle_ventas' y actualizar stock
    $stmt_detalle = $conexion->prepare("INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    $stmt_stock = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");
    $stmt_kardex = $conexion->prepare("INSERT INTO movimientos_inventario (id_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, id_usuario, referencia_id) VALUES (?, 'venta', ?, ?, ?, ?, ?)");

    foreach ($carrito as $item) {
        $id_producto = $item['id'];
        $cantidad = $item['cantidad'];

        // Obtener precio y stock actual para insertar
        $stmt_prod_info = $conexion->prepare("SELECT precio_venta, stock FROM productos WHERE id_producto = ?");
        $stmt_prod_info->bind_param('i', $id_producto);
        $stmt_prod_info->execute();
        $prod_info = $stmt_prod_info->get_result()->fetch_assoc();
        $precio_unitario = $prod_info['precio_venta'];
        $stock_anterior = $prod_info['stock'];
        $stmt_prod_info->close();

        // Insertar en detalle_ventas
        $stmt_detalle->bind_param("iiid", $id_venta, $id_producto, $cantidad, $precio_unitario);
        $stmt_detalle->execute();
        
        // Actualizar stock
        $stmt_stock->bind_param("ii", $cantidad, $id_producto);
        $stmt_stock->execute();

        // Insertar en Kardex
        $stock_nuevo = $stock_anterior - $cantidad;
        $stmt_kardex->bind_param("iiiiii", $id_producto, $cantidad, $stock_anterior, $stock_nuevo, $_SESSION['id_usuario'], $id_venta);
        $stmt_kardex->execute();
    }
    $stmt_detalle->close();
    $stmt_stock->close();
    $stmt_kardex->close();

    $conexion->commit();
    echo json_encode(['success' => true, 'id_venta' => $id_venta]);

} catch (Exception $e) {
    $conexion->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al procesar la venta: ' . $e->getMessage()]);
}
$conexion->close();