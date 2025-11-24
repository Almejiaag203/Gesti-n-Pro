<?php
// Carga la configuración y la base de datos.
require_once __DIR__ . '/../config/database.php';

// Asegurarnos de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/sistema_venta_inventario/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="/sistema_venta_inventario/index.php">
        <i class="fas fa-store"></i> <?php echo getConfig('nombre_tienda') ?? 'GestiónPRO'; ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <?php if (isset($_SESSION['id_usuario'])): ?>
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <?php if (tienePermiso('dashboard_ver')): ?>
            <li class="nav-item">
              <a class="nav-link" href="/sistema_venta_inventario/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <?php endif; ?>
            
            <?php if (tienePermiso('ventas_crear') || tienePermiso('ventas_ver_listado')): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownVentas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-cash-register"></i> Ventas
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownVentas">
                    <?php if (tienePermiso('ventas_crear')): ?>
                        <li><a class="dropdown-item" href="/sistema_venta_inventario/modules/ventas/index.php">Punto de Venta (POS)</a></li>
                    <?php endif; ?>
                    <?php if (tienePermiso('ventas_ver_listado')): ?>
                        <li><a class="dropdown-item" href="/sistema_venta_inventario/modules/ventas/listado_ventas.php">Historial de Ventas</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if (tienePermiso('productos_ver_lista')): ?>
            <li class="nav-item">
              <a class="nav-link" href="/sistema_venta_inventario/modules/productos/index.php"><i class="fas fa-box-open"></i> Inventario</a>
            </li>
            <?php endif; ?>
            <?php if (tienePermiso('categorias_ver_lista')): ?>
            <li class="nav-item">
              <a class="nav-link" href="/sistema_venta_inventario/modules/categorias/index.php"><i class="fas fa-tags"></i> Categorías</a>
            </li>
            <?php endif; ?>
            <?php if (tienePermiso('compras_ver_lista')): ?>
            <li class="nav-item">
              <a class="nav-link" href="/sistema_venta_inventario/modules/compras/index.php"><i class="fas fa-truck"></i> Compras</a>
            </li>
            <?php endif; ?>
            <?php if (tienePermiso('proveedores_ver_lista')): ?>
            <li class="nav-item">
              <a class="nav-link" href="/sistema_venta_inventario/modules/proveedores/index.php"><i class="fas fa-truck-loading"></i> Proveedores</a>
            </li>
            <?php endif; ?>
            <?php if (tienePermiso('clientes_ver_lista')): ?>
            <li class="nav-item">
              <a class="nav-link" href="/sistema_venta_inventario/modules/clientes/index.php"><i class="fas fa-users"></i> Clientes</a>
            </li>
            <?php endif; ?>
            <?php if (tienePermiso('caja_gestionar')): ?>
            <li class="nav-item">
              <a class="nav-link" href="/sistema_venta_inventario/modules/caja/index.php"><i class="fas fa-calculator"></i> Caja</a>
            </li>
            <?php endif; ?>
            <?php if (tienePermiso('reportes_ver_ventas') || tienePermiso('reportes_ver_inventario')): ?>
            <li class="nav-item">
              <a class="nav-link" href="/sistema_venta_inventario/modules/reportes/index.php"><i class="fas fa-chart-line"></i> Reportes</a>
            </li>
            <?php endif; ?>

            <?php if (tienePermiso('usuarios_ver_lista')): ?>
            <li class="nav-item">
              <a class="nav-link" href="/sistema_venta_inventario/modules/usuarios/index.php"><i class="fas fa-users-cog"></i> Usuarios</a>
            </li>
            <?php endif; ?>
          </ul>
          <ul class="navbar-nav">
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                      <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario'); ?>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="/sistema_venta_inventario/modules/perfil/index.php">Mi Perfil</a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li><a class="dropdown-item" href="/sistema_venta_inventario/modules/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                  </ul>
              </li>
          </ul>
        <?php endif; ?>
    </div>
  </div>
</nav>

<main class="container mt-4">