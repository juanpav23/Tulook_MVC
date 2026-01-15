// ================================
// Archivo: carrito-functions.js
// Funciones auxiliares para el carrito
// ================================

// Variable global para acceso a funciones
var CarritoFunctions = (function() {
    
    // Configuración
    const CONFIG = {
        IVA_PORCENTAJE: 19,
        MONEDA: 'COP',
        IDIOMA: 'es-CO'
    };
    
    /**
     * Formatea un número como moneda colombiana
     */
    function formatCurrency(amount) {
        return new Intl.NumberFormat(CONFIG.IDIOMA, {
            style: 'currency',
            currency: CONFIG.MONEDA,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }
    
    /**
     * Formatea un número sin símbolo de moneda
     */
    function formatNumber(amount) {
        return new Intl.NumberFormat(CONFIG.IDIOMA).format(amount);
    }
    
    /**
     * Calcula el IVA para un monto
     */
    function calcularIVA(subtotal, porcentaje = CONFIG.IVA_PORCENTAJE) {
        const ivaMonto = subtotal * (porcentaje / 100);
        const totalConIVA = subtotal + ivaMonto;
        
        return {
            subtotal: subtotal,
            ivaPorcentaje: porcentaje,
            ivaMonto: ivaMonto,
            totalConIVA: totalConIVA
        };
    }
    
    /**
     * Calcula los totales del carrito incluyendo IVA
     */
    function calcularTotalesCarrito(carritoItems) {
        let subtotal = 0;
        let descuentos = 0;
        let totalItems = 0;
        
        if (!carritoItems || !Array.isArray(carritoItems)) {
            return {
                subtotal: 0,
                descuentos: 0,
                ivaMonto: 0,
                totalConIVA: 0,
                totalItems: 0,
                porcentajeIVA: CONFIG.IVA_PORCENTAJE
            };
        }
        
        carritoItems.forEach(item => {
            const precioUnitario = parseFloat(item.Precio) || 0;
            const cantidad = parseInt(item.Cantidad) || 1;
            const precioOriginal = parseFloat(item.Precio_Original) || precioUnitario;
            
            const subtotalItem = precioUnitario * cantidad;
            const subtotalOriginal = precioOriginal * cantidad;
            
            subtotal += subtotalItem;
            descuentos += (subtotalOriginal - subtotalItem);
            totalItems += cantidad;
        });
        
        const ivaCalculado = calcularIVA(subtotal);
        
        return {
            subtotal: subtotal,
            descuentos: descuentos,
            ivaMonto: ivaCalculado.ivaMonto,
            totalConIVA: ivaCalculado.totalConIVA,
            totalItems: totalItems,
            porcentajeIVA: CONFIG.IVA_PORCENTAJE
        };
    }
    
    /**
     * Actualiza el resumen del carrito en la UI
     */
    function actualizarResumenCarrito() {
        const carrito = window.carritoItems || [];
        const totals = calcularTotalesCarrito(carrito);
        
        // Actualizar elementos en la página
        const elementos = {
            subtotal: document.getElementById('resumen-subtotal'),
            descuentos: document.getElementById('resumen-descuentos'),
            iva: document.getElementById('resumen-iva'),
            total: document.getElementById('resumen-total'),
            totalItems: document.getElementById('resumen-total-items')
        };
        
        // Subtotal
        if (elementos.subtotal) {
            elementos.subtotal.textContent = formatCurrency(totals.subtotal);
        }
        
        // Descuentos
        if (elementos.descuentos) {
            if (totals.descuentos > 0) {
                elementos.descuentos.textContent = `-${formatCurrency(totals.descuentos)}`;
                elementos.descuentos.style.display = 'block';
            } else {
                elementos.descuentos.style.display = 'none';
            }
        }
        
        // IVA
        if (elementos.iva) {
            elementos.iva.textContent = formatCurrency(totals.ivaMonto);
        }
        
        // Total
        if (elementos.total) {
            if (totals.totalConIVA === 0) {
                elementos.total.innerHTML = '<span class="text-success fw-bold">GRATIS</span>';
            } else {
                elementos.total.textContent = formatCurrency(totals.totalConIVA);
            }
        }
        
        // Total items
        if (elementos.totalItems) {
            elementos.totalItems.textContent = totals.totalItems;
        }
        
        // Actualizar botones según el total
        const btnCheckout = document.getElementById('btn-proceder-pago');
        if (btnCheckout) {
            if (totals.totalConIVA === 0) {
                btnCheckout.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirmar Pedido Gratuito';
            } else {
                btnCheckout.innerHTML = '<i class="fas fa-credit-card me-2"></i>Proceder al Pago';
            }
        }
        
        // Retornar los totales calculados
        return totals;
    }
    
    /**
     * Agrega un producto al carrito vía AJAX
     */
    async function agregarAlCarrito(productoData) {
        try {
            if (!window.BASE_URL) {
                throw new Error('BASE_URL no está definida');
            }
            
            const response = await fetch(`${window.BASE_URL}?c=Carrito&a=agregarAjax`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(productoData)
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                // Actualizar contador del carrito en el navbar
                actualizarContadorCarrito(result.carrito_count);
                
                // Mostrar notificación
                mostrarNotificacion('success', '✅ Producto agregado al carrito');
                
                return result;
            } else {
                throw new Error(result.message || 'Error desconocido');
            }
        } catch (error) {
            console.error('Error agregando al carrito:', error);
            mostrarNotificacion('error', '❌ Error: ' + error.message);
            return { 
                success: false, 
                message: error.message 
            };
        }
    }
    
    /**
     * Actualiza la cantidad de un producto en el carrito
     */
    async function actualizarCantidadCarrito(index, nuevaCantidad) {
        try {
            if (!window.BASE_URL) {
                throw new Error('BASE_URL no está definida');
            }
            
            const response = await fetch(`${window.BASE_URL}?c=Carrito&a=actualizarCantidad`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    index: index,
                    cantidad: nuevaCantidad
                })
            });
            
            if (response.ok) {
                // Recargar la página para ver cambios
                window.location.reload();
                return true;
            } else {
                throw new Error('Error en la respuesta del servidor');
            }
        } catch (error) {
            console.error('Error actualizando cantidad:', error);
            mostrarNotificacion('error', '❌ Error actualizando cantidad');
            return false;
        }
    }
    
    /**
     * Elimina un producto del carrito
     */
    async function eliminarDelCarrito(index) {
        try {
            if (!window.BASE_URL) {
                throw new Error('BASE_URL no está definida');
            }
            
            const response = await fetch(`${window.BASE_URL}?c=Carrito&a=eliminar&id=${index}`);
            
            if (response.ok) {
                window.location.reload();
                return true;
            } else {
                throw new Error('Error en la respuesta del servidor');
            }
        } catch (error) {
            console.error('Error eliminando del carrito:', error);
            mostrarNotificacion('error', '❌ Error eliminando producto');
            return false;
        }
    }
    
    /**
     * Vacía todo el carrito
     */
    async function vaciarCarrito() {
        try {
            if (!window.BASE_URL) {
                throw new Error('BASE_URL no está definida');
            }
            
            const response = await fetch(`${window.BASE_URL}?c=Carrito&a=vaciar`);
            
            if (response.ok) {
                window.location.reload();
                return true;
            } else {
                throw new Error('Error en la respuesta del servidor');
            }
        } catch (error) {
            console.error('Error vaciando carrito:', error);
            mostrarNotificacion('error', '❌ Error vaciando carrito');
            return false;
        }
    }
    
    /**
     * Actualiza el contador del carrito en el navbar
     */
    function actualizarContadorCarrito(count) {
        const contadores = document.querySelectorAll('.carrito-count');
        contadores.forEach(contador => {
            contador.textContent = count;
            contador.style.display = count > 0 ? 'inline' : 'none';
        });
        
        // También actualizar en localStorage para persistencia
        localStorage.setItem('carrito_count', count);
    }
    
    /**
     * Muestra una notificación estilo toast
     */
    function mostrarNotificacion(tipo, mensaje, duracion = 5000) {
        // Crear contenedor de notificaciones si no existe
        let container = document.getElementById('notificaciones-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificaciones-container';
            container.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                z-index: 9999;
            `;
            document.body.appendChild(container);
        }
        
        // Colores por tipo
        const iconos = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        
        // Crear notificación
        const notificacion = document.createElement('div');
        notificacion.className = `alert alert-${tipo} alert-dismissible fade show`;
        notificacion.style.cssText = `
            min-width: 300px;
            max-width: 400px;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        notificacion.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${iconos[tipo] || 'info-circle'} me-2"></i>
                <span>${mensaje}</span>
            </div>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        `;
        
        container.appendChild(notificacion);
        
        // Auto-remover después del tiempo especificado
        setTimeout(() => {
            if (notificacion.parentNode) {
                notificacion.classList.remove('show');
                setTimeout(() => {
                    if (notificacion.parentNode) {
                        notificacion.remove();
                    }
                }, 300);
            }
        }, duracion);
        
        // Agregar estilos de animación si no existen
        if (!document.getElementById('notificaciones-estilos')) {
            const estilos = document.createElement('style');
            estilos.id = 'notificaciones-estilos';
            estilos.textContent = `
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                .alert {
                    transition: opacity 0.3s ease;
                }
            `;
            document.head.appendChild(estilos);
        }
    }
    
    /**
     * Inicializa los eventos del carrito
     */
    function inicializarEventosCarrito() {
        // Botones de eliminar producto
        document.querySelectorAll('.eliminar-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.dataset.url;
                const producto = this.dataset.producto;
                
                Swal.fire({
                    title: '¿Eliminar producto?',
                    html: `¿Estás seguro de eliminar <strong>"${producto}"</strong> del carrito?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
        
        // Botón vaciar carrito
        const vaciarBtn = document.getElementById('vaciarCarrito');
        if (vaciarBtn) {
            vaciarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: '¿Vaciar carrito?',
                    text: "Se eliminarán todos los productos del carrito.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, vaciar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = `${window.BASE_URL}?c=Carrito&a=vaciar`;
                    }
                });
            });
        }
        
        // Botón proceder al pago
        const formCompra = document.getElementById('formCompra');
        if (formCompra) {
            formCompra.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const carrito = window.carritoItems || [];
                if (carrito.length === 0) {
                    mostrarNotificacion('warning', 'Tu carrito está vacío');
                    return;
                }
                
                const totals = calcularTotalesCarrito(carrito);
                
                Swal.fire({
                    title: '¿Proceder al pago?',
                    html: `
                        <div class="text-start">
                            <p><strong>Resumen de compra:</strong></p>
                            <div class="mb-2">
                                <small class="text-muted">Subtotal:</small>
                                <div class="fw-bold">${formatCurrency(totals.subtotal)}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">IVA (${totals.porcentajeIVA}%):</small>
                                <div class="fw-bold text-info">${formatCurrency(totals.ivaMonto)}</div>
                            </div>
                            <div class="mt-3 pt-2 border-top">
                                <small class="text-muted">Total a pagar:</small>
                                <div class="h4 text-success fw-bold">${formatCurrency(totals.totalConIVA)}</div>
                            </div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Continuar al pago',
                    cancelButtonText: 'Seguir comprando',
                    reverseButtons: true,
                    showCloseButton: true
                }).then(result => {
                    if (result.isConfirmed) {
                        // Mostrar loading
                        const submitBtn = this.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
                        
                        // Enviar formulario después de un breve delay
                        setTimeout(() => {
                            this.submit();
                        }, 500);
                    }
                });
            });
        }
        
        // Controles de cantidad (botones + y -)
        document.querySelectorAll('.cantidad-control form').forEach(form => {
            const btnMenos = form.querySelector('button[value*="-"]');
            const btnMas = form.querySelector('button[value*="+"]');
            
            if (btnMenos) {
                btnMenos.addEventListener('click', function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const index = formData.get('index');
                    const cantidadActual = formData.get('cantidad');
                    const nuevaCantidad = Math.max(1, parseInt(cantidadActual) - 1);
                    
                    if (index !== null) {
                        actualizarCantidadCarrito(index, nuevaCantidad);
                    }
                });
            }
            
            if (btnMas) {
                btnMas.addEventListener('click', function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const index = formData.get('index');
                    const cantidadActual = formData.get('cantidad');
                    const nuevaCantidad = parseInt(cantidadActual) + 1;
                    
                    if (index !== null) {
                        actualizarCantidadCarrito(index, nuevaCantidad);
                    }
                });
            }
        });
        
        // Inputs de cantidad directos
        document.querySelectorAll('input[name="cantidad-input"]').forEach(input => {
            input.addEventListener('change', function() {
                const index = this.dataset.index;
                const nuevaCantidad = Math.max(1, parseInt(this.value) || 1);
                
                if (index !== undefined) {
                    actualizarCantidadCarrito(index, nuevaCantidad);
                }
            });
        });
    }
    
    /**
     * Carga el contador del carrito desde localStorage
     */
    function cargarContadorCarrito() {
        const count = localStorage.getItem('carrito_count');
        if (count) {
            actualizarContadorCarrito(parseInt(count));
        }
    }
    
    /**
     * Inicializa el sistema del carrito
     */
    function inicializarCarrito() {
        cargarContadorCarrito();
        inicializarEventosCarrito();
        
        // Actualizar resumen si estamos en la página del carrito
        if (document.querySelector('.resumen-compra')) {
            actualizarResumenCarrito();
        }
    }
    
    // API pública
    return {
        // Funciones de utilidad
        formatCurrency,
        formatNumber,
        calcularIVA,
        calcularTotalesCarrito,
        
        // Funciones del carrito
        agregarAlCarrito,
        actualizarCantidadCarrito,
        eliminarDelCarrito,
        vaciarCarrito,
        actualizarContadorCarrito,
        actualizarResumenCarrito,
        
        // UI
        mostrarNotificacion,
        
        // Inicialización
        inicializarEventosCarrito,
        inicializarCarrito
    };
})();

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si SweetAlert2 está disponible
    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert2 no está cargado. Algunas funciones pueden no funcionar.');
    }
    
    // Inicializar sistema del carrito
    CarritoFunctions.inicializarCarrito();
    
    // Pasar datos globales si están disponibles
    if (typeof window.carritoItems === 'undefined') {
        window.carritoItems = [];
    }
    
    if (typeof window.BASE_URL === 'undefined') {
        console.warn('BASE_URL no está definida globalmente');
    }
});

// Hacer disponible globalmente
window.CarritoFunctions = CarritoFunctions;