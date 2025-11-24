// Espera a que todo el contenido del DOM (la página) esté cargado
document.addEventListener('DOMContentLoaded', function() {
    
    const productoModal = document.getElementById('productoModal');
    
    // Solo ejecutar si el modal de productos realmente existe en esta página
    if (productoModal) {
        // 'show.bs.modal' se dispara cada vez que se intenta abrir el modal
        productoModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget; // Botón que activó el modal (+ Agregar o Editar)
            const modalTitle = productoModal.querySelector('.modal-title');
            const form = productoModal.querySelector('form');
            
            // Siempre reseteamos el formulario al abrir
            form.reset();
            document.getElementById('id_producto').value = '';
            modalTitle.textContent = 'Agregar Nuevo Producto';

            // --- LÓGICA PARA EDITAR (esto es lo que faltaba) ---
            // Si el botón que abrió el modal es un botón de editar (tiene la clase 'edit-btn')
            if (button.classList.contains('edit-btn')) {
                modalTitle.textContent = 'Editar Producto';
                
                // Llenar el formulario con los datos guardados en los atributos 'data-*' del botón
                document.getElementById('id_producto').value = button.dataset.id;
                document.getElementById('nombre').value = button.dataset.nombre;
                document.getElementById('descripcion').value = button.dataset.descripcion;
                document.getElementById('id_categoria').value = button.dataset.categoria;
                document.getElementById('precio_venta').value = button.dataset.precioventa;
                document.getElementById('precio_compra').value = button.dataset.preciocompra;
                document.getElementById('stock').value = button.dataset.stock;
                document.getElementById('stock_minimo').value = button.dataset.stockminimo;
            }
        });
    }
});