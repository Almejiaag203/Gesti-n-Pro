<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_SESSION['id_usuario']) || !tienePermiso('ventas_crear')) {
    echo "<div class='alert alert-danger'>No tienes permiso para acceder a esta sección.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$clientes_sql = "SELECT id_cliente, nombre_cliente FROM clientes WHERE activo = 1 ORDER BY nombre_cliente ASC";
$clientes_resultado = $conexion->query($clientes_sql);
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title">Punto de Venta</h4>
                    <div class="mb-3">
                        <label for="buscar_producto" class="form-label">Buscar Producto (Nombre o Código)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="buscar_producto" class="form-control" placeholder="Escribe para buscar...">
                        </div>
                        <div id="resultados_busqueda" class="list-group position-absolute" style="z-index: 1000; width: 95%;"></div>
                    </div>
                    <hr>
                    <h5><i class="fas fa-shopping-cart"></i> Carrito</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="carrito_tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title">Resumen de Venta</h4>
                     <div class="mb-3">
                        <label for="id_cliente" class="form-label">Cliente</label>
                        <select id="id_cliente" class="form-select">
                            <option value="">Venta genérica</option>
                             <?php while($cliente = $clientes_resultado->fetch_assoc()): ?>
                                <option value="<?php echo $cliente['id_cliente']; ?>"><?php echo htmlspecialchars($cliente['nombre_cliente']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between fs-4">
                        <strong>TOTAL:</strong>
                        <strong id="total_venta"><?php echo getMoneda(); ?>0.00</strong>
                    </div>
                    <hr>
                    <div class="d-grid">
                        <button id="btn_finalizar_venta" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#pagoModal" disabled>
                            <i class="fas fa-check"></i> Finalizar Venta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pagoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Procesar Pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <h3 class="text-center">Total a Pagar: <span id="modal_total_pagar"></span></h3>
        <div class="mb-3">
            <label for="metodo_pago" class="form-label">Método de Pago</label>
            <select id="metodo_pago" class="form-select">
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta_credito">Tarjeta de Crédito</option>
                <option value="tarjeta_debito">Tarjeta de Débito</option>
                <option value="transferencia">Transferencia</option>
            </select>
        </div>
        <div id="pago_efectivo_div">
            <div class="mb-3">
                <label for="monto_recibido" class="form-label">Monto Recibido</label>
                <input type="number" step="0.01" class="form-control" id="monto_recibido">
            </div>
            <h4>Cambio: <span id="cambio_cliente"><?php echo getMoneda(); ?>0.00</span></h4>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btn_confirmar_venta" class="btn btn-primary">Confirmar Venta</button>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const MONEDA = '<?php echo getMoneda(); ?>';
    let carrito = [];

    const buscarInput = document.getElementById('buscar_producto');
    const resultadosBusqueda = document.getElementById('resultados_busqueda');
    const carritoTbody = document.getElementById('carrito_tbody');
    const totalVentaSpan = document.getElementById('total_venta');
    const btnFinalizarVenta = document.getElementById('btn_finalizar_venta');
    const modalTotalPagar = document.getElementById('modal_total_pagar');
    const montoRecibidoInput = document.getElementById('monto_recibido');
    const cambioClienteSpan = document.getElementById('cambio_cliente');
    const btnConfirmarVenta = document.getElementById('btn_confirmar_venta');
    const metodoPagoSelect = document.getElementById('metodo_pago');
    const pagoEfectivoDiv = document.getElementById('pago_efectivo_div');
    const idClienteSelect = document.getElementById('id_cliente');

    buscarInput.addEventListener('keyup', function() {
        const term = this.value;
        if (term.length < 2) {
            resultadosBusqueda.innerHTML = '';
            return;
        }
        fetch(`buscar_productos.php?term=${term}`)
            .then(response => response.json())
            .then(data => {
                resultadosBusqueda.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(producto => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = `${producto.nombre} - Stock: ${producto.stock} - ${MONEDA}${parseFloat(producto.precio_venta).toFixed(2)}`;
                        item.addEventListener('click', (e) => {
                            e.preventDefault();
                            agregarAlCarrito(producto);
                            buscarInput.value = '';
                            resultadosBusqueda.innerHTML = '';
                        });
                        resultadosBusqueda.appendChild(item);
                    });
                } else {
                    resultadosBusqueda.innerHTML = '<span class="list-group-item">No se encontraron productos</span>';
                }
            });
    });

    function agregarAlCarrito(producto) {
        const existente = carrito.find(item => item.id === producto.id_producto);
        if (existente) {
            existente.cantidad++;
        } else {
            carrito.push({
                id: producto.id_producto,
                nombre: producto.nombre,
                precio: parseFloat(producto.precio_venta),
                cantidad: 1
            });
        }
        renderizarCarrito();
    }

    function renderizarCarrito() {
        carritoTbody.innerHTML = '';
        let total = 0;
        carrito.forEach((item, index) => {
            const subtotal = item.precio * item.cantidad;
            total += subtotal;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.nombre}</td>
                <td>${MONEDA}${item.precio.toFixed(2)}</td>
                <td>
                    <input type="number" value="${item.cantidad}" min="1" class="form-control form-control-sm cantidad-input" data-index="${index}">
                </td>
                <td>${MONEDA}${subtotal.toFixed(2)}</td>
                <td>
                    <button class="btn btn-danger btn-sm eliminar-btn" data-index="${index}"><i class="fas fa-trash"></i></button>
                </td>
            `;
            carritoTbody.appendChild(tr);
        });
        totalVentaSpan.textContent = `${MONEDA}${total.toFixed(2)}`;
        btnFinalizarVenta.disabled = carrito.length === 0;
    }

    carritoTbody.addEventListener('change', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            const index = e.target.dataset.index;
            const nuevaCantidad = parseInt(e.target.value);
            if (nuevaCantidad > 0) {
                carrito[index].cantidad = nuevaCantidad;
                renderizarCarrito();
            }
        }
    });

    carritoTbody.addEventListener('click', function(e) {
        const targetButton = e.target.closest('.eliminar-btn');
        if (targetButton) {
            const index = targetButton.dataset.index;
            carrito.splice(index, 1);
            renderizarCarrito();
        }
    });
    
    btnFinalizarVenta.addEventListener('click', () => {
        const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        modalTotalPagar.textContent = `${MONEDA}${total.toFixed(2)}`;
        montoRecibidoInput.value = '';
        cambioClienteSpan.textContent = `${MONEDA}0.00`;
    });

    metodoPagoSelect.addEventListener('change', () => {
        pagoEfectivoDiv.style.display = metodoPagoSelect.value === 'efectivo' ? 'block' : 'none';
    });

    montoRecibidoInput.addEventListener('keyup', () => {
        const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        const recibido = parseFloat(montoRecibidoInput.value) || 0;
        const cambio = recibido - total;
        cambioClienteSpan.textContent = `${MONEDA}${cambio.toFixed(2)}`;
    });

    btnConfirmarVenta.addEventListener('click', function() {
        this.disabled = true;
        const ventaData = {
            id_cliente: idClienteSelect.value,
            metodo_pago: metodoPagoSelect.value,
            carrito: carrito.map(item => ({ id: item.id, cantidad: item.cantidad }))
        };

        fetch('procesar_venta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(ventaData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `ticket.php?id_venta=${data.id_venta}`;
            } else {
                alert('Error: ' + data.error);
                this.disabled = false;
            }
        })
        .catch(error => {
            alert('Hubo un error de conexión.');
            this.disabled = false;
        });
    });
});
</script>