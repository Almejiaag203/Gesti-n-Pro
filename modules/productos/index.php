<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_SESSION['id_usuario'])) { redirigir('/mi_sistema/modules/auth/login.php'); }
if (!tienePermiso('productos_ver_lista')) {
    echo "<div class='alert alert-danger'>No tienes permiso para acceder a esta sección.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$ver_inactivos = isset($_GET['ver']) && $_GET['ver'] == 'inactivos';
$filtro_activo = $ver_inactivos ? 0 : 1;
$sql = "SELECT p.*, c.nombre as nombre_categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.activo = ? ORDER BY p.nombre ASC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $filtro_activo);
$stmt->execute();
$resultado = $stmt->get_result();

$categorias_sql = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre ASC";
$categorias_resultado = $conexion->query($categorias_sql);
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="m-0"><i class="fas fa-box-open"></i> Gestión de Inventario</h2>
        <div>
            <?php if ($ver_inactivos): ?>
                <a href="index.php" class="btn btn-info">Ver Activos</a>
            <?php else: ?>
                <a href="index.php?ver=inactivos" class="btn btn-secondary">Ver Inactivos</a>
            <?php endif; ?>
            <?php if (tienePermiso('productos_crear')): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productoModal">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio Venta</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($producto = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre_categoria'] ?? 'Sin categoría'); ?></td>
                            <td><?php echo getMoneda(); ?><?php echo number_format($producto['precio_venta'], 2); ?></td>
                            <td><?php echo $producto['stock']; ?></td>
                            <td>
                                <?php if ($producto['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (tienePermiso('productos_ver_detalle')): ?>
                                    <a href="detalle_producto.php?id=<?php echo $producto['id_producto']; ?>" class="btn btn-sm btn-info" title="Ver Kardex">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (tienePermiso('productos_editar')): ?>
                                    <button type="button" class="btn btn-sm btn-warning edit-btn" 
                                            data-bs-toggle="modal" data-bs-target="#productoModal"
                                            data-id="<?php echo $producto['id_producto']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                            data-descripcion="<?php echo htmlspecialchars($producto['descripcion']); ?>"
                                            data-categoria="<?php echo $producto['id_categoria']; ?>"
                                            data-precioventa="<?php echo $producto['precio_venta']; ?>"
                                            data-preciocompra="<?php echo $producto['precio_compra']; ?>"
                                            data-stock="<?php echo $producto['stock']; ?>"
                                            data-stockminimo="<?php echo $producto['stock_minimo']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if (tienePermiso('productos_cambiar_estado')): ?>
                                    <?php if ($producto['activo']): ?>
                                        <a href="cambiar_estado.php?id=<?php echo $producto['id_producto']; ?>&estado=0" class="btn btn-sm btn-danger" title="Desactivar"><i class="fas fa-ban"></i></a>
                                    <?php else: ?>
                                        <a href="cambiar_estado.php?id=<?php echo $producto['id_producto']; ?>&estado=1" class="btn btn-sm btn-success" title="Activar"><i class="fas fa-check"></i></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay productos <?php echo $ver_inactivos ? 'inactivos' : 'activos'; ?>.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="productoModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Agregar Nuevo Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="guardar.php" method="POST">
            <input type="hidden" name="id_producto" id="id_producto">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Producto</label>
                <input type="text" class="form-control" name="nombre" id="nombre" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" name="descripcion" id="descripcion" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="id_categoria" class="form-label">Categoría</label>
                <select class="form-select" name="id_categoria" id="id_categoria">
                    <option value="">Seleccione una categoría</option>
                    <?php mysqli_data_seek($categorias_resultado, 0); ?>
                    <?php while($categoria = $categorias_resultado->fetch_assoc()): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="precio_venta" class="form-label">Precio Venta</label>
                    <input type="number" step="0.01" class="form-control" name="precio_venta" id="precio_venta" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="precio_compra" class="form-label">Precio Compra</label>
                    <input type="number" step="0.01" class="form-control" name="precio_compra" id="precio_compra">
                </div>
            </div>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="stock" class="form-label">Stock Actual</label>
                    <input type="number" class="form-control" name="stock" id="stock" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                    <input type="number" class="form-control" name="stock_minimo" id="stock_minimo">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php 
echo '<script src="/mi_sistema/assets/js/productos.js"></script>';
require_once __DIR__ . '/../../includes/footer.php'; 
?>