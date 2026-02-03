/**
 * productosManager.js
 * Gestión de productos base - Separado del HTML
 */

class ProductosManager {
    constructor() {
        this.baseUrl = window.BASE_URL || '';
        this.currentAction = null;
        this.currentProductId = null;
        this.init();
    }

    init() {
        this.initEventListeners();
        this.initTooltips();
        this.highlightActiveFilters();
        this.fixModalAccessibility();
    }

    // ============================================
    // MÉTODOS PÚBLICOS
    // ============================================

    static clearSessionMessages() {
        const baseUrl = window.BASE_URL || '';
        fetch(`${baseUrl}?c=Admin&a=clearMessages`);
    }

    // ============================================
    // INICIALIZACIÓN
    // ============================================

    initEventListeners() {
        // Eventos para botones de estado
        document.querySelectorAll('.toggle-estado-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                const activo = e.currentTarget.getAttribute('data-activo') === '1';
                this.toggleEstadoProducto(id, activo);
            });
        });

        // Eventos para botones de eliminar
        document.querySelectorAll('.delete-producto-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                const nombre = e.currentTarget.getAttribute('data-nombre');
                this.confirmarEliminarProducto(id, nombre);
            });
        });

        // Eventos para filtros
        ['filterCategoria', 'filterGenero', 'filterSubcategoria', 'filterEstado'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', () => {
                    this.highlightActiveFilters();
                    if (element.value) {
                        setTimeout(() => this.autoSubmitSearch(), 500);
                    }
                });
            }
        });

        // Evento para búsqueda con Enter
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('searchForm')?.submit();
                }
            });
        }

        // Configurar botón de confirmación en modal
        const confirmBtn = document.getElementById('confirmModalActionBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => this.executeConfirmedAction());
        }

        // Configurar cierre del modal para evitar congelamiento
        const confirmModal = document.getElementById('confirmModal');
        if (confirmModal) {
            confirmModal.addEventListener('hidden.bs.modal', () => {
                this.cleanupModal();
            });
        }
    }

    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // ============================================
    // FUNCIONALIDADES DE FILTROS
    // ============================================

    highlightActiveFilters() {
        const filters = ['categoria', 'genero', 'subcategoria', 'estado'];
        let activeFilters = 0;

        filters.forEach(filter => {
            const element = document.getElementById(`filter${filter.charAt(0).toUpperCase() + filter.slice(1)}`);
            if (element && element.value) {
                element.classList.add('active-filter');
                activeFilters++;
            } else if (element) {
                element.classList.remove('active-filter');
            }
        });

        const badge = document.getElementById('filtrosBadge');
        if (badge) {
            badge.textContent = `${activeFilters} Filtro${activeFilters !== 1 ? 's' : ''} Activo${activeFilters !== 1 ? 's' : ''}`;
        }
    }

    autoSubmitSearch() {
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.submit();
        }
    }

    // ============================================
    // FUNCIONALIDADES DE PRODUCTOS
    // ============================================

    toggleEstadoProducto(idProducto, estaActivo) {
        this.currentProductId = idProducto;
        this.currentAction = estaActivo ? 'deactivate' : 'activate';
        
        this.showConfirmModal({
            type: this.currentAction,
            productId: idProducto,
            isActive: estaActivo
        });
    }

    confirmarEliminarProducto(idProducto, nombreProducto) {
        this.currentProductId = idProducto;
        this.currentAction = 'delete';
        
        this.showConfirmModal({
            type: 'delete',
            productId: idProducto,
            productName: nombreProducto
        });
    }

    // ============================================
    // MODAL DE CONFIRMACIÓN
    // ============================================

    showConfirmModal(config) {
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmModal'));
        const modalTitle = document.getElementById('confirmModalTitle');
        const modalMessage = document.getElementById('confirmModalMessage');
        const modalDetails = document.getElementById('confirmModalDetails');
        const modalIcon = document.getElementById('confirmModalIcon');
        const modalHeader = document.getElementById('confirmModalHeader');
        const actionBtn = document.getElementById('confirmModalActionBtn');
        
        // USAR EL MISMO COLOR PARA ACTIVAR Y DESACTIVAR
        const primaryColor = 'var(--primary-dark)';
        
        switch(config.type) {
            case 'activate':
                modalTitle.innerHTML = '<i class="fas fa-play me-2"></i>Activar Producto';
                modalMessage.textContent = '¿Estás seguro de activar este producto?';
                modalDetails.textContent = 'El producto estará disponible para la venta y sus variantes serán visibles.';
                modalIcon.innerHTML = '<i class="fas fa-play fa-3x" style="color: ' + primaryColor + '"></i>';
                modalHeader.style.background = 'linear-gradient(135deg, ' + primaryColor + ', ' + primaryColor + ')';
                actionBtn.style.background = 'linear-gradient(135deg, ' + primaryColor + ', ' + primaryColor + ')';
                actionBtn.style.borderColor = primaryColor;
                actionBtn.innerHTML = '<i class="fas fa-play me-1"></i> Activar';
                break;
                
            case 'deactivate':
                modalTitle.innerHTML = '<i class="fas fa-pause me-2"></i>Desactivar Producto';
                modalMessage.textContent = '¿Estás seguro de desactivar este producto?';
                modalDetails.textContent = 'El producto no estará disponible para la venta y sus variantes no serán visibles.';
                modalIcon.innerHTML = '<i class="fas fa-pause fa-3x" style="color: ' + primaryColor + '"></i>';
                modalHeader.style.background = 'linear-gradient(135deg, ' + primaryColor + ', ' + primaryColor + ')';
                actionBtn.style.background = 'linear-gradient(135deg, ' + primaryColor + ', ' + primaryColor + ')';
                actionBtn.style.borderColor = primaryColor;
                actionBtn.innerHTML = '<i class="fas fa-pause me-1"></i> Desactivar';
                break;
                
            case 'delete':
                const productName = config.productName || 'este producto';
                modalTitle.innerHTML = '<i class="fas fa-trash-alt me-2"></i>Eliminar Producto';
                modalMessage.textContent = `¿Estás seguro de eliminar "${productName}" permanentemente?`;
                modalDetails.textContent = 'Esta acción eliminará el producto base y todas sus variantes. ¡Esta acción no se puede deshacer!';
                modalIcon.innerHTML = '<i class="fas fa-exclamation-triangle fa-3x text-danger"></i>';
                modalHeader.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
                actionBtn.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
                actionBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Eliminar';
                break;
        }
        
        modal.show();
    }

    executeConfirmedAction() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
        
        switch(this.currentAction) {
            case 'activate':
            case 'deactivate':
                this.executeToggleEstado();
                break;
            case 'delete':
                this.executeDeleteProducto();
                break;
        }
        
        if (modal) {
            modal.hide();
        }
    }

    cleanupModal() {
        this.currentAction = null;
        this.currentProductId = null;
        
        // Forzar liberación de focus
        const modal = document.getElementById('confirmModal');
        if (modal) {
            const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            focusableElements.forEach(el => el.blur());
        }
    }

    async executeToggleEstado() {
        const idProducto = this.currentProductId;
        const btn = document.querySelector(`.toggle-estado-btn[data-id="${idProducto}"]`);
        if (!btn) return;
        
        const estaActivo = btn.getAttribute('data-activo') === '1';
        const nuevoEstado = estaActivo ? 0 : 1;
        
        // Guardar información original para restaurar en caso de error
        const originalIcon = btn.querySelector('i').className;
        const originalTitle = btn.getAttribute('title');
        
        // Mostrar indicador de carga
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;
        
        try {
            const response = await fetch(`${this.baseUrl}?c=Admin&a=toggleEstadoProducto&id=${idProducto}&estado=${nuevoEstado}`);
            const data = await response.json();
            
            if (data.success) {
                // ACTUALIZACIÓN CORRECTA DEL BOTÓN CON NUEVOS ÍCONOS
                // Si el producto está ACTIVO (nuevoEstado = 1): mostrar fa-pause (para desactivar)
                // Si el producto está INACTIVO (nuevoEstado = 0): mostrar fa-play (para activar)
                const iconClass = nuevoEstado ? 'fas fa-pause' : 'fas fa-play';
                const tooltipText = nuevoEstado ? 'Desactivar Producto' : 'Activar Producto';
                
                // Actualizar botón
                btn.innerHTML = `<i class="${iconClass}"></i>`;
                btn.setAttribute('title', tooltipText);
                btn.setAttribute('data-activo', nuevoEstado);
                
                // Actualizar el tooltip de Bootstrap
                const tooltipInstance = bootstrap.Tooltip.getInstance(btn);
                if (tooltipInstance) {
                    tooltipInstance.setContent({ '.tooltip-inner': tooltipText });
                }
                
                // Actualizar el badge de estado en la nueva columna
                const row = btn.closest('tr');
                const badge = row.querySelector('.estado-badge');
                if (badge) {
                    if (nuevoEstado) {
                        badge.className = 'badge bg-success rounded-pill fs-6 estado-badge';
                        badge.innerHTML = '<i class="fas fa-check-circle me-1"></i>Activo';
                    } else {
                        badge.className = 'badge bg-secondary rounded-pill fs-6 estado-badge';
                        badge.innerHTML = '<i class="fas fa-pause-circle me-1"></i>Inactivo';
                    }
                }
                
                // Mostrar mensaje flotante AZUL
                this.showToast(data.message, 'primary');
            } else {
                // Restaurar estado original en caso de error
                btn.innerHTML = `<i class="${originalIcon}"></i>`;
                btn.setAttribute('title', originalTitle);
                // Mostrar mensaje de error también AZUL
                this.showToast(data.message, 'primary');
            }
        } catch (error) {
            console.error('Error:', error);
            // Restaurar estado original en caso de error
            btn.innerHTML = `<i class="${originalIcon}"></i>`;
            btn.setAttribute('title', originalTitle);
            // Mostrar mensaje de error AZUL
            this.showToast('Error al cambiar el estado', 'primary');
        } finally {
            btn.disabled = false;
        }
    }

    executeDeleteProducto() {
        const idProducto = this.currentProductId;
        window.location.href = `${this.baseUrl}?c=Admin&a=deleteProducto&id=${idProducto}`;
    }

    // ============================================
    // NOTIFICACIONES - MODIFICADA PARA SER AZULES
    // ============================================

    showToast(message, type = 'primary') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        
        const toast = document.createElement('div');
        // Siempre usar bg-primary para el color azul
        toast.className = `toast align-items-center text-white bg-primary border-0 notification-toast`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        // Determinar ícono según el tipo pero mantener color azul
        let icon = 'fa-info-circle';
        if (type === 'success' || type === 'primary') {
            icon = 'fa-check-circle';
        } else if (type === 'danger') {
            icon = 'fa-exclamation-triangle';
        } else if (type === 'warning') {
            icon = 'fa-exclamation-circle';
        }
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="fas ${icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        document.body.appendChild(toastContainer);
        
        const bsToast = new bootstrap.Toast(toast, { 
            delay: 5000,
            animation: true
        });
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toastContainer.remove();
        });
    }

    // ============================================
    // CORRECCIÓN DE ACCESIBILIDAD
    // ============================================

    fixModalAccessibility() {
        // Remover aria-hidden cuando se cierre el modal
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('hidden.bs.modal', () => {
                // Forzar remoción de focus
                document.activeElement?.blur();
                
                // Remover backdrop si existe
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                
                // Restaurar scroll del body
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.productosManager = new ProductosManager();
});