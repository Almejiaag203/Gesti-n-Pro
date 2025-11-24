<?php
require_once __DIR__ . '/../../config/database.php';

// Proteger
if (!isset($_SESSION['id_usuario'])) {
    redirigir('/mi_sistema/modules/auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recolectar y sanitizar datos
    $id_producto = $_POST['id_producto'] ?? null;
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $id_categoria = !empty($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : null;
    $precio_venta = (float)$_POST['precio_venta'];
    $precio_compra = (float)$_POST['precio_compra'];
    $stock = (int)$_POST['stock'];
    $stock_minimo = (int)$_POST['stock_minimo'];

    // Lógica para Actualizar (Update)
    if (!empty($id_producto)) {
        $stmt = $conexion->prepare("UPDATE productos SET nombre = ?, descripcion = ?, id_categoria = ?, precio_venta = ?, precio_compra = ?, stock = ?, stock_minimo = ? WHERE id_producto = ?");
        $stmt->bind_param("ssiddiii", $nombre, $descripcion, $id_categoria, $precio_venta, $precio_compra, $stock, $stock_minimo, $id_producto);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = 'Producto actualizado correctamente.';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al actualizar el producto: ' . $stmt->error;
            $_SESSION['mensaje_tipo'] = 'danger';
        }
        $stmt->close();
    } 
    // Lógica para Crear (Create)
    else {
        $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, id_categoria, precio_venta, precio_compra, stock, stock_minimo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiddii", $nombre, $descripcion, $id_categoria, $precio_venta, $precio_compra, $stock, $stock_minimo);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = 'Producto agregado correctamente.';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al agregar el producto: ' . $stmt->error;
            $_SESSION['mensaje_tipo'] = 'danger';
        }
        $stmt->close();
    }

    $conexion->close();
    redirigir('index.php');

} else {
    // Si no es POST, redirigir
    redirigir('index.php');
}