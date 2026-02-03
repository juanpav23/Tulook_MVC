/**
 * variantesConfirmaciones.js
 * Sistema de confirmaciones personalizadas para variantes de productos
 * Usa el mismo estilo que productosManager.js
 */

class VariantesConfirmaciones {
    constructor() {
        this.baseUrl = window.BASE_URL || '';
        this.currentAction = null;
        this.currentVarianteId = null;
        this.init();
    }

    init() {
        this.initEventListeners();
        this.replaceConfirmations();
        this.ensureModalExists();
    }

    initEventListeners() {
        // Reemplazar confirmaciones de activar/desactivar
        document.querySelectorAll('a[href*="toggleVariante"]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                this.toggleVariante(url, link);
            });
        });

        // Reemplazar confirmaciones de eliminar
        document.querySelectorAll('a[href*="eliminarVariante"]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                const nombreVariante = link.closest('tr')?.querySelector('td:nth-child(2)')?.textContent.trim() || 'esta variante';
                this.eliminarVariante(url, nombreVariante);
            });
        });
    }

    replaceConfirmations() {
        // Eliminar eventos onclick existentes y reemplazar con nuestros métodos
        document.querySelectorAll('a[href*="toggleVariante"]').forEach(link => {
            link.removeAttribute('onclick');
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                this.toggleVariante(url, link);
            });
        });

        document.querySelectorAll('a[href*="eliminarVariante"]').forEach(link => {
            link.removeAttribute('onclick');
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                const nombreVariante = link.closest('tr')?.querySelector('td:nth-child(2)')?.textContent.trim() || 'esta variante';
                this.eliminarVariante(url, nombreVariante);
            });
        });
    }

    ensureModalExists() {
        if (!document.getElementById('confirmModalVariantes')) {
            this.createModal();
        }
    }

    toggleVariante(url, linkElement) {
        // Extraer información de la URL y el botón
        const urlParams = new URLSearchParams(url.split('?')[1]);
        const idVariante = urlParams.get('id');
        
        // Determinar si es activar o desactivar basado en el ícono
        const icon = linkElement.querySelector('i');
        const estaActiva = icon.classList.contains('fa-pause');
        const actionType = estaActiva ? 'deactivate' : 'activate';
        
        this.currentVarianteId = idVariante;
        this.currentAction = actionType;
        
        this.showConfirmModal({
            type: actionType,
            varianteId: idVariante,
            isActive: estaActiva,
            url: url
        });
    }

    eliminarVariante(url, nombreVariante) {
        const urlParams = new URLSearchParams(url.split('?')[1]);
        const idVariante = urlParams.get('id');
        
        this.currentVarianteId = idVariante;
        this.currentAction = 'delete';
        
        this.showConfirmModal({
            type: 'delete',
            varianteId: idVariante,
            productName: nombreVariante,
            url: url
        });
    }

    showConfirmModal(config) {
        // Asegurarse que el modal existe
        if (!document.getElementById('confirmModalVariantes')) {
            this.createModal();
        }
        
        const modalEl = document.getElementById('confirmModalVariantes');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        
        // Configurar eventos para evitar congelamiento
        modalEl.addEventListener('hidden.bs.modal', () => {
            this.cleanupModal();
        });
        
        const modalTitle = document.getElementById('confirmModalTitleVariantes');
        const modalMessage = document.getElementById('confirmModalMessageVariantes');
        const modalDetails = document.getElementById('confirmModalDetailsVariantes');
        const modalIcon = document.getElementById('confirmModalIconVariantes');
        const modalHeader = document.getElementById('confirmModalHeaderVariantes');
        const actionBtn = document.getElementById('confirmModalActionBtnVariantes');
        
        // USAR EL MISMO COLOR PARA ACTIVAR Y DESACTIVAR (primary-dark)
        const primaryColor = 'var(--primary-dark)';
        
        switch(config.type) {
            case 'activate':
                modalTitle.innerHTML = '<i class="fas fa-check-circle me-2"></i>Activar Variante';
                modalMessage.textContent = '¿Estás seguro de activar esta variante?';
                modalDetails.textContent = 'La variante estará disponible para la venta y será visible en la tienda.';
                modalIcon.innerHTML = '<i class="fas fa-check-circle fa-3x" style="color: ' + primaryColor + '"></i>';
                modalHeader.style.background = 'linear-gradient(135deg, ' + primaryColor + ', ' + primaryColor + ')';
                actionBtn.style.background = 'linear-gradient(135deg, ' + primaryColor + ', ' + primaryColor + ')';
                actionBtn.style.borderColor = primaryColor;
                actionBtn.innerHTML = '<i class="fas fa-power-on me-1"></i> Activar';
                break;
                
            case 'deactivate':
                modalTitle.innerHTML = '<i class="fas fa-pause-circle me-2"></i>Desactivar Variante';
                modalMessage.textContent = '¿Estás seguro de desactivar esta variante?';
                modalDetails.textContent = 'La variante no estará disponible para la venta y no será visible en la tienda.';
                modalIcon.innerHTML = '<i class="fas fa-pause-circle fa-3x" style="color: ' + primaryColor + '"></i>';
                modalHeader.style.background = 'linear-gradient(135deg, ' + primaryColor + ', ' + primaryColor + ')';
                actionBtn.style.background = 'linear-gradient(135deg, ' + primaryColor + ', ' + primaryColor + ')';
                actionBtn.style.borderColor = primaryColor;
                actionBtn.innerHTML = '<i class="fas fa-power-off me-1"></i> Desactivar';
                break;
                
            case 'delete':
                const productName = config.productName || 'esta variante';
                modalTitle.innerHTML = '<i class="fas fa-trash-alt me-2"></i>Eliminar Variante';
                modalMessage.textContent = `¿Estás seguro de eliminar "${productName}" permanentemente?`;
                modalDetails.textContent = 'Esta acción eliminará la variante y toda su información. ¡Esta acción no se puede deshacer!';
                modalIcon.innerHTML = '<i class="fas fa-exclamation-triangle fa-3x text-danger"></i>';
                modalHeader.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
                actionBtn.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
                actionBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Eliminar';
                break;
        }
        
        // Configurar la acción del botón de confirmar
        actionBtn.onclick = () => {
            modal.hide();
            if (config.url) {
                window.location.href = config.url;
            }
        };
        
        modal.show();
    }

    createModal() {
        const modalHTML = `
            <div class="modal fade" id="confirmModalVariantes" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header" id="confirmModalHeaderVariantes">
                            <h5 class="modal-title fw-bold" id="confirmModalTitleVariantes">
                                <i class="fas fa-question-circle me-2"></i>Confirmar Acción
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body" id="confirmModalBodyVariantes">
                            <div class="text-center mb-3">
                                <div class="mb-3" id="confirmModalIconVariantes">
                                    <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                                </div>
                                <h6 id="confirmModalMessageVariantes">¿Estás seguro de realizar esta acción?</h6>
                                <p class="text-muted fs-6" id="confirmModalDetailsVariantes"></p>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </button>
                            <button type="button" class="btn" id="confirmModalActionBtnVariantes">
                                <i class="fas fa-check me-1"></i> Confirmar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = modalHTML;
        document.body.appendChild(modalContainer);
        
        // Estilos para el modal
        this.addModalStyles();
        
        // Configurar eventos para el nuevo modal
        const modalEl = document.getElementById('confirmModalVariantes');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                this.cleanupModal();
            });
        }
    }

    cleanupModal() {
        this.currentAction = null;
        this.currentVarianteId = null;
        
        // Forzar liberación de focus
        const modal = document.getElementById('confirmModalVariantes');
        if (modal) {
            const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            focusableElements.forEach(el => el.blur());
            
            // Limpiar backdrop
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                if (backdrop.parentNode) {
                    backdrop.parentNode.removeChild(backdrop);
                }
            });
            
            // Restaurar scroll
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
    }

    addModalStyles() {
        const style = document.createElement('style');
        style.textContent = `
            #confirmModalHeaderVariantes {
                background: linear-gradient(135deg, var(--primary-dark), var(--primary-dark));
                color: white;
            }
            
            #confirmModalActionBtnVariantes {
                border: none;
                color: white;
                min-width: 120px;
                border-radius: 6px;
                padding: 0.5rem 1.5rem;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            
            #confirmModalActionBtnVariantes:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            
            /* Prevenir congelamiento */
            .modal {
                z-index: 1055;
            }
            
            .modal-backdrop {
                z-index: 1050;
            }
        `;
        document.head.appendChild(style);
    }

    showToast(message, type = 'info') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        let icon = 'fa-info-circle';
        if (type === 'success') icon = 'fa-check-circle';
        else if (type === 'danger') icon = 'fa-exclamation-triangle';
        else if (type === 'warning') icon = 'fa-exclamation-circle';
        
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
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.variantesConfirmaciones = new VariantesConfirmaciones();
});