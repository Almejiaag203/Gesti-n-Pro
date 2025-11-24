<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}
header('Content-Type: application/json');
$term = $_GET['term'] ?? '';
if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

// Busca solo productos activos y con stock
$stmt = $conexion->prepare("SELECT id_producto, nombre, precio_venta, stock 
                            FROM productos 
                            WHERE (nombre LIKE ? OR codigo_barra LIKE ?) AND stock > 0 AND activo = 1
                            LIMIT 10");
$searchTerm = "%{$term}%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$resultado = $stmt->get_result();
$productos = [];
while ($row = $resultado->fetch_assoc()) {
    $productos[] = $row;
}
$stmt->close();
$conexion->close();

echo json_encode($productos);