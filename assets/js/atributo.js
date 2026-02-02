// atributo.js - Sistema completo de gestión de atributos
// Incluye sistema de mensajes, validaciones y modal flotante para productos

class AtributoSystem {
    constructor() {
        this.ejemplosPorTipo = {
            'talla': 'Ej: M, 32, XL, S, 40, Única, etc.',
            'medida': 'Ej: 18, 30, Ajuste Estándar, Correa Larga, etc.',
            'volumen': 'Ej: 100 ml, 50 ml, 30 ml, 150 ml, etc.',
            'tamaño': 'Ej: Mediano, Grande, Pequeño, Extra Grande'
        };
        this.init();
    }

    init() {
        this.setupEjemplos();
        this.setupFormValidation();
        this.setupActionButtons();
        this.setupProductosModal();
        this.setupTooltips();
    }

    setupEjemplos() {
        const tipoSelect = document.getElementById('ID_TipoAtributo');
        const ejemploSpan = document.getElementById('ejemplo');
        
        if (tipoSelect && ejemploSpan) {
            const actualizarEjemplo = () => {
                const selectedOption = tipoSelect.options[tipoSelect.selectedIndex];
                const tipoNombre = selectedOption.textContent.toLowerCase();
                
                for (const [tipo, ejemplo] of Object.entries(this.ejemplosPorTipo)) {
                    if (tipoNombre.includes(tipo)) {
                        ejemploSpan.textContent = ejemplo;
                        return;
                    }
                }
                
                ejemploSpan.textContent = 'Ej: M, 32, Mediano, 100 ml, etc.';
            };
            
            tipoSelect.addEventListener('change', actualizarEjemplo);
            if (tipoSelect.value) actualizarEjemplo();
        }
    }

    setupFormValidation() {
        const formAtributo = document.getElementById('formAtributo');
        
        if (formAtributo) {
            formAtributo.addEventListener('submit', (e) => {
                const valorInput = document.getElementById('Valor');
                const tipoSelect = document.getElementById('ID_TipoAtributo');
                
                if (valorInput && valorInput.value.trim() === '') {
                    e.preventDefault();
                    this.showMessage('Por favor, ingresa un valor para el atributo.', 'warning');
                    valorInput.focus();
                    return false;
                }
                
                if (tipoSelect && tipoSelect.value === '') {
                    e.preventDefault();
                    this.showMessage('Por favor, selecciona un tipo de atributo.', 'warning');
                    tipoSelect.focus();
                    return false;
                }
                
                return true;
            });
        }
    }

    setupActionButtons() {
        // Configurar botones de acción en la tabla
        document.querySelectorAll('a[href*="cambiarEstado"]').forEach(link => {
            link.addEventListener('click', (e) => this.handleEstadoClick(e, link));
        });

        document.querySelectorAll('a[href*="eliminar"]').forEach(link => {
            link.addEventListener('click', (e) => this.handleEliminarClick(e, link));
        });

        // Botones deshabilitados
        document.querySelectorAll('button.btn-outline-secondary[disabled], a.btn-outline-secondary[disabled]').forEach(boton => {
            boton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                if (boton.querySelector('.fa-ban')) {
                    this.showMessage('Este atributo está en uso y no puede eliminarse.', 'warning');
                } else if (boton.querySelector('.fa-shield-alt')) {
                    this.showMessage('⚠️ Este valor universal no se puede eliminar.', 'warning');
                }
                return false;
            });
        });
    }

    setupProductosModal() {
        // Modal flotante para mostrar productos que usan el atributo
        const modalHTML = `
        <div class="modal fade" id="productosAtributoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #2A3448, #3A4A6B); color: white;">
                        <h5 class="modal-title">
                            <i class="fas fa-box me-2"></i>
                            <span id="modalAtributoTitle"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="productosLoading" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-3">Cargando productos...</p>
                        </div>
                        <div id="productosContent" style="display: none;">
                            <div id="productosList"></div>
                        </div>
                        <div id="productosEmpty" class="text-center py-5" style="display: none;">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5>No hay productos usando este atributo</h5>
                            <p class="text-muted">Este atributo no está siendo usado por ningún producto.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        if (!document.getElementById('productosAtributoModal')) {
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
    }

    async showProductosModal(atributoId, atributoNombre) {
        const modal = new bootstrap.Modal(document.getElementById('productosAtributoModal'));
        const title = document.getElementById('modalAtributoTitle');
        const loading = document.getElementById('productosLoading');
        const content = document.getElementById('productosContent');
        const empty = document.getElementById('productosEmpty');
        const list = document.getElementById('productosList');
        
        title.textContent = `Productos que usan "${atributoNombre}"`;
        
        // Mostrar loading
        loading.style.display = 'block';
        content.style.display = 'none';
        empty.style.display = 'none';
        
        modal.show();
        
        try {
            // Obtener productos que usan este atributo
            const response = await fetch(`${window.BASE_URL || ''}?c=Atributo&a=getProductosByAtributo&id=${atributoId}`);
            const productos = await response.json();
            
            loading.style.display = 'none';
            
            if (productos.length > 0) {
                let html = `
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead style="background-color: #2A3448; color: white;">
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Artículo</th>
                                <th>Atributo</th>
                                <th>Estado</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                productos.forEach(producto => {
                    // CAMBIO AQUÍ: Estado activo ahora usa #2A3448
                    html += `
                    <tr>
                        <td><span class="badge bg-secondary">#${producto.ID_Producto}</span></td>
                        <td><strong>${this.escapeHtml(producto.Nombre_Producto)}</strong></td>
                        <td>${this.escapeHtml(producto.Articulo)}</td>
                        <td><span class="badge" style="background-color: #2A3448; color: white;">${this.escapeHtml(producto.AtributoUsado || atributoNombre)}</span></td>
                        <td>
                            <span class="badge" style="background-color: ${producto.Activo ? '#2A3448' : '#6c757d'}; color: white;">
                                ${producto.Activo ? 'Activo' : 'Inactivo'}
                            </span>
                        </td>
                        <td><span class="badge" style="background-color: #2A3448; color: white;">${producto.Cantidad || 0}</span></td>
                    </tr>`;
                });
                
                html += `</tbody></table></div>`;
                list.innerHTML = html;
                content.style.display = 'block';
            } else {
                empty.style.display = 'block';
            }
        } catch (error) {
            console.error('Error al cargar productos:', error);
            loading.style.display = 'none';
            empty.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar los productos. Por favor, intenta nuevamente.
                </div>`;
            empty.style.display = 'block';
        }
    }

    handleEstadoClick(e, link) {
        e.preventDefault();
        e.stopPropagation();
        
        const href = link.getAttribute('href');
        const url = new URL(href, window.location.origin);
        const estado = url.searchParams.get('estado');
        const id = url.searchParams.get('id');
        const isActivate = estado === '1';
        
        // Obtener nombre del atributo
        let atributoName = 'este atributo';
        const row = link.closest('tr');
        if (row) {
            const nameCell = row.querySelector('td:nth-child(2) strong');
            if (nameCell) atributoName = nameCell.textContent.trim();
        }
        
        // Verificar si está en uso (para desactivar)
        if (!isActivate) {
            const enUsoCell = row?.querySelector('td:nth-child(5)');
            if (enUsoCell && enUsoCell.textContent.includes('En uso')) {
                this.showConfirmModal({
                    title: 'No se puede desactivar',
                    message: `El atributo <strong>"${atributoName}"</strong> está siendo usado por productos y no puede desactivarse.`,
                    icon: 'fa-exclamation-triangle',
                    type: 'primary', // CAMBIADO de 'warning' a 'primary'
                    showCancel: false,
                    confirmText: 'Entendido'
                });
                return false;
            }
        }
        
        this.showConfirmModal({
            title: isActivate ? 'Activar Atributo' : 'Desactivar Atributo',
            message: `¿Estás seguro de ${isActivate ? 'activar' : 'desactivar'} el atributo <strong>"${atributoName}"</strong>?`,
            icon: isActivate ? 'fa-play' : 'fa-pause',
            type: 'primary', // CAMBIADO para usar nuestro color
            onConfirm: () => {
                window.location.href = href;
            }
        });
        
        return false;
    }

    handleEliminarClick(e, link) {
        e.preventDefault();
        e.stopPropagation();
        
        const href = link.getAttribute('href');
        const url = new URL(href, window.location.origin);
        const id = url.searchParams.get('id');
        
        // Obtener nombre del atributo
        let atributoName = 'este atributo';
        let atributoId = 0;
        const row = link.closest('tr');
        
        if (row) {
            const nameCell = row.querySelector('td:nth-child(2) strong');
            if (nameCell) atributoName = nameCell.textContent.trim();
            atributoId = id;
        }
        
        // Verificar si es el valor universal "Única"
        if (atributoId == 16 || atributoName.toLowerCase() === 'única') {
            this.showConfirmModal({
                title: 'Valor Universal',
                message: `El valor <strong>"Única"</strong> es un valor universal del sistema y NO puede eliminarse.`,
                icon: 'fa-shield-alt',
                type: 'primary', // CAMBIADO de 'warning' a 'primary'
                showCancel: false,
                confirmText: 'Entendido'
            });
            return false;
        }
        
        // Verificar si el atributo está en uso
        const enUsoCell = row?.querySelector('td:nth-child(5)');
        if (enUsoCell && enUsoCell.textContent.includes('En uso')) {
            this.showConfirmModal({
                title: 'No se puede eliminar',
                message: `El atributo <strong>"${atributoName}"</strong> está siendo usado por productos y no puede eliminarse.`,
                icon: 'fa-exclamation-triangle',
                type: 'primary', // CAMBIADO de 'warning' a 'primary'
                showCancel: false,
                confirmText: 'Entendido'
            });
            return false;
        }
        
        this.showConfirmModal({
            title: 'Eliminar Atributo',
            message: `
                <div class="alert" style="background-color: rgba(220, 53, 69, 0.1); border-color: #dc3545; color: #721c24;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>ADVERTENCIA:</strong> Esta acción no se puede deshacer.
                </div>
                <p>¿Estás seguro de eliminar el atributo <strong>"${atributoName}"</strong> permanentemente?</p>
            `,
            icon: 'fa-trash-alt',
            type: 'danger', // Mantenemos 'danger' para eliminaciones
            confirmText: 'Eliminar Permanentemente',
            onConfirm: () => {
                window.location.href = href;
            }
        });
        
        return false;
    }

    showConfirmModal(config) {
        const modalId = 'atributoConfirmModal';
        
        // Eliminar modal existente
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove();
        
        // Determinar el color basado en el tipo
        let headerColor = '#2A3448';
        let buttonClass = 'btn-primary';
        
        if (config.type === 'danger') {
            headerColor = '#DC3545';
            buttonClass = 'btn-danger';
        }
        
        const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="cursor: default !important;">
                    <div class="modal-header" style="background: ${headerColor}; color: white; cursor: default !important;">
                        <div class="d-flex align-items-center w-100">
                            <div class="modal-icon" style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%; margin-right: 15px; cursor: default !important;">
                                <i class="fas ${config.icon} fa-lg"></i>
                            </div>
                            <div style="cursor: default !important;">
                                <h5 class="modal-title mb-0">${config.title}</h5>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" style="cursor: pointer !important;"></button>
                        </div>
                    </div>
                    <div class="modal-body" style="cursor: default !important;">
                        <div class="text-center mb-3">
                            ${config.message}
                        </div>
                    </div>
                    <div class="modal-footer" style="cursor: default !important;">
                        ${config.showCancel !== false ? `
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="cursor: pointer !important;">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        ` : ''}
                        <button type="button" class="btn ${buttonClass} confirm-action-btn" style="cursor: pointer !important;">
                            <i class="fas ${config.icon} me-1"></i> ${config.confirmText || 'Confirmar'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Añadir estilos CSS para corregir el cursor
        const styleId = 'modal-cursor-fix';
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                #${modalId} .modal-content,
                #${modalId} .modal-header,
                #${modalId} .modal-body,
                #${modalId} .modal-footer,
                #${modalId} .modal-title,
                #${modalId} .modal-icon {
                    cursor: default !important;
                }
                
                #${modalId} button,
                #${modalId} .btn-close,
                #${modalId} .confirm-action-btn {
                    cursor: pointer !important;
                }
                
                #${modalId}.fade .modal-backdrop {
                    cursor: default !important;
                }
                
                .modal-backdrop.show {
                    cursor: default !important;
                }
            `;
            document.head.appendChild(style);
        }
        
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        
        modal.show();
        
        // Forzar cursor pointer en el backdrop
        modalElement.addEventListener('shown.bs.modal', () => {
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.style.cursor = 'default !important';
                backdrop.classList.add('cursor-fix');
            }
        });
        
        const confirmBtn = modalElement.querySelector('.confirm-action-btn');
        confirmBtn.addEventListener('click', () => {
            if (typeof config.onConfirm === 'function') {
                config.onConfirm();
            }
            modal.hide();
        });
        
        modalElement.addEventListener('hidden.bs.modal', () => {
            // Remover estilos después de cerrar
            setTimeout(() => {
                modalElement.remove();
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }, 300);
        });
    }

    showMessage(message, type = 'info') {
        // Crear toast message
        const toastId = 'atributoToast-' + Date.now();
        const toastHTML = `
        <div class="toast align-items-center text-bg-${type} border-0" id="${toastId}" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        `;
        
        // Agregar al contenedor
        let container = document.querySelector('.atributo-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'atributo-toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1060';
            document.body.appendChild(container);
        }
        
        container.insertAdjacentHTML('beforeend', toastHTML);
        
        // Mostrar y auto-ocultar
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    getGradient(type) {
        const gradients = {
            'primary': 'linear-gradient(135deg, #2A3448, #3A4A6B)',
            'warning': 'linear-gradient(135deg, #3A4A6B, #4A5B7D)',
            'danger': 'linear-gradient(135deg, #DC3545, #C82333)',
            'info': 'linear-gradient(135deg, #0dcaf0, #0aa2c0)'
        };
        return gradients[type] || gradients['primary'];
    }

    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    setupTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }
}

// Inicializar el sistema
document.addEventListener('DOMContentLoaded', function() {
    window.atributoSystem = new AtributoSystem();
});

// Función global para abrir el modal de productos
function verProductosAtributo(id, nombre) {
    if (window.atributoSystem) {
        window.atributoSystem.showProductosModal(id, nombre);
    }
}

// Función global para confirmación (backwards compatibility)
function confirmEliminarAtributo(event, nombreAtributo, idAtributo) {
    event.preventDefault();
    event.stopPropagation();
    
    if (idAtributo == 16 || nombreAtributo.toLowerCase() === 'única') {
        if (window.atributoSystem) {
            window.atributoSystem.showMessage('⚠️ El valor "Única" es un valor universal del sistema y NO puede eliminarse.', 'warning');
        }
        return false;
    }
    
    if (window.atributoSystem) {
        window.atributoSystem.showConfirmModal({
            title: 'Eliminar Atributo',
            message: `⚠️ ¿Estás seguro de eliminar el atributo <strong>"${nombreAtributo}"</strong>?<br><br>Esta acción NO se puede deshacer.`,
            icon: 'fa-trash-alt',
            type: 'danger',
            confirmText: 'Eliminar',
            onConfirm: () => {
                const href = event.target.closest('a').getAttribute('href');
                window.location.href = href;
            }
        });
    }
    
    return false;
}