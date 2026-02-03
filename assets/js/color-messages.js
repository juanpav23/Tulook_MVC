/**
 * color-messages.js - Mensajes mejorados para gestión de colores
 * Diseño específico usando la paleta #1B202D
 * VERSIÓN CORREGIDA - Elimina confirm() nativo
 */

class ColorMessages {
    constructor() {
        this.init();
    }

    init() {
        this.enhanceSessionMessages();
        this.overrideNativeConfirms();
        this.setupActionButtons();
        this.removeExistingModals();
    }

    // ============================================
    // ELIMINAR CONFIRM() NATIVOS DE LOS BOTONES
    // ============================================

    overrideNativeConfirms() {
        // Eliminar todos los onclick con confirm() existentes
        document.querySelectorAll('a[onclick*="confirm"], button[onclick*="confirm"]').forEach(element => {
            const onclick = element.getAttribute('onclick');
            if (onclick && onclick.includes('confirm')) {
                element.removeAttribute('onclick');
                
                // También eliminar cualquier event listener existente
                const newElement = element.cloneNode(true);
                element.parentNode.replaceChild(newElement, element);
            }
        });
    }

    setupActionButtons() {
        // Configurar botones de estado (Activar/Desactivar)
        document.querySelectorAll('a[href*="cambiarEstado"]').forEach(link => {
            this.setupEstadoButton(link);
        });

        // Configurar botones de eliminar
        document.querySelectorAll('a[href*="eliminar"]').forEach(link => {
            this.setupDeleteButton(link);
        });

        // Configurar botones de editar (solo para consistencia)
        document.querySelectorAll('a[href*="editar"]').forEach(link => {
            link.addEventListener('click', (e) => {
                // Permitir que el enlace de edición funcione normalmente
                return true;
            });
        });
    }

    setupEstadoButton(link) {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const href = link.getAttribute('href');
            const url = new URL(href, window.location.origin);
            const estado = url.searchParams.get('estado');
            const id = url.searchParams.get('id');
            
            // Determinar si es activar (1) o desactivar (0)
            const isActivate = estado === '1';
            const action = isActivate ? 'activate' : 'deactivate';
            
            // Obtener nombre del color
            let colorName = 'este color';
            const row = link.closest('tr');
            if (row) {
                const nameCell = row.querySelector('td:first-child strong');
                if (nameCell) {
                    colorName = nameCell.textContent.trim();
                }
            }
            
            // Verificar si el color está en uso (para desactivar)
            if (!isActivate) {
                const enUsoCell = row.querySelector('td:nth-child(5)');
                if (enUsoCell && enUsoCell.textContent.includes('En uso')) {
                    this.showWarningToast(
                        `No se puede desactivar "${colorName}" porque está en uso por productos.`,
                        colorName
                    );
                    return false;
                }
            }
            
            // Mostrar modal de confirmación
            this.showConfirmModal({
                type: action,
                colorName: colorName,
                onConfirm: () => {
                    // Ejecutar la acción
                    window.location.href = href;
                }
            });
            
            return false;
        });
    }

    setupDeleteButton(link) {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const href = link.getAttribute('href');
            
            // Obtener nombre del color
            let colorName = 'este color';
            const row = link.closest('tr');
            if (row) {
                const nameCell = row.querySelector('td:first-child strong');
                if (nameCell) {
                    colorName = nameCell.textContent.trim();
                }
                
                // Verificar si el color está en uso
                const enUsoCell = row.querySelector('td:nth-child(5)');
                if (enUsoCell && enUsoCell.textContent.includes('En uso')) {
                    this.showWarningToast(
                        `No se puede eliminar "${colorName}" porque está en uso por productos.`,
                        colorName
                    );
                    return false;
                }
            }
            
            // Mostrar modal de confirmación
            this.showConfirmModal({
                type: 'delete',
                colorName: colorName,
                onConfirm: () => {
                    // Ejecutar la acción
                    window.location.href = href;
                }
            });
            
            return false;
        });
    }

    // ============================================
    // MENSAJES TOAST
    // ============================================

    showWarningToast(message, colorName = '') {
        const colors = {
            bg: '#3A4A6B',      // accent-blue
            icon: 'fa-exclamation-triangle',
            title: 'Acción no permitida',
            gradient: 'linear-gradient(135deg, #3A4A6B, #4A5B7D)'
        };
        
        this.createToast(message, colors, colorName);
    }

    showSuccessToast(message, colorName = '', action = 'success') {
        const colors = {
            'activate': {
                bg: '#2A3448',
                icon: 'fa-check-circle',
                title: 'Color Activado',
                gradient: 'linear-gradient(135deg, #2A3448, #3A4A6B)'
            },
            'deactivate': {
                bg: '#3A4A6B',
                icon: 'fa-check-circle',
                title: 'Color Desactivado',
                gradient: 'linear-gradient(135deg, #3A4A6B, #4A5B7D)'
            },
            'delete': {
                bg: '#2A3448',
                icon: 'fa-check-circle',
                title: 'Color Eliminado',
                gradient: 'linear-gradient(135deg, #2A3448, #3A4A6B)'
            },
            'success': {
                bg: '#2A3448',
                icon: 'fa-check-circle',
                title: 'Operación Exitosa',
                gradient: 'linear-gradient(135deg, #2A3448, #3A4A6B)'
            }
        };
        
        const config = colors[action] || colors['success'];
        this.createToast(message, config, colorName);
    }

    createToast(message, config, colorName = '') {
        // Crear toast
        const toastContainer = document.createElement('div');
        toastContainer.className = 'color-toast-container';
        
        const toast = document.createElement('div');
        toast.className = 'color-toast';
        toast.style.cssText = `
            background: ${config.gradient};
            color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            padding: 0;
            overflow: hidden;
            min-width: 300px;
            max-width: 350px;
            margin-bottom: 10px;
            animation: slideInRight 0.3s ease-out;
        `;
        
        toast.innerHTML = `
            <div class="toast-header" style="background: transparent; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <div class="d-flex align-items-center w-100">
                    <div class="toast-icon" style="background: rgba(255,255,255,0.2); padding: 8px; border-radius: 50%; margin-right: 10px;">
                        <i class="fas ${config.icon}" style="color: white;"></i>
                    </div>
                    <div>
                        <strong class="me-auto">${config.title}</strong>
                        ${colorName ? `<small>${colorName}</small>` : ''}
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            <div class="toast-body" style="padding: 15px;">
                <div class="d-flex align-items-center">
                    <div style="flex-grow: 1;">
                        ${message}
                    </div>
                </div>
            </div>
            <div class="toast-progress" style="height: 3px; background: rgba(255,255,255,0.3); width: 100%;"></div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Agregar al contenedor
        let container = document.querySelector('.color-messages-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'color-messages-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1060;
            `;
            document.body.appendChild(container);
        }
        
        container.appendChild(toastContainer);
        
        // Animación de la barra de progreso
        const progressBar = toast.querySelector('.toast-progress');
        if (progressBar) {
            progressBar.style.width = '100%';
            progressBar.style.transition = 'width 5s linear';
            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 100);
        }
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-out forwards';
            setTimeout(() => {
                if (toast.parentNode) {
                    toastContainer.remove();
                }
            }, 300);
        }, 5000);
        
        // Cerrar manualmente
        const closeBtn = toast.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                toast.style.animation = 'slideOutRight 0.3s ease-out forwards';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toastContainer.remove();
                    }
                }, 300);
            });
        }
    }

    // ============================================
    // MODAL DE CONFIRMACIÓN (VERSIÓN MEJORADA)
    // ============================================

    showConfirmModal(config) {
        // Eliminar modales existentes
        this.removeExistingModals();
        
        const modalId = 'colorConfirmModal';
        const colors = {
            'activate': {
                title: 'Activar Color',
                icon: 'fa-play',
                color: '#2A3448',
                gradient: 'linear-gradient(135deg, #2A3448, #3A4A6B)',
                buttonText: 'Activar Color',
                message: '¿Estás seguro de activar este color?'
            },
            'deactivate': {
                title: 'Desactivar Color',
                icon: 'fa-pause',
                color: '#3A4A6B',
                gradient: 'linear-gradient(135deg, #3A4A6B, #4A5B7D)',
                buttonText: 'Desactivar Color',
                message: '¿Estás seguro de desactivar este color?'
            },
            'delete': {
                title: 'Eliminar Color',
                icon: 'fa-trash-alt',
                color: '#DC3545',
                gradient: 'linear-gradient(135deg, #DC3545, #C82333)',
                buttonText: 'Eliminar Permanentemente',
                message: '¿Estás seguro de eliminar este color permanentemente?'
            }
        };
        
        const action = config.type;
        const actionConfig = colors[action] || colors['delete'];
        const colorName = config.colorName || 'este color';
        
        // Crear modal HTML
        const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content color-modal-content">
                    <div class="modal-header" style="background: ${actionConfig.gradient};">
                        <div class="d-flex align-items-center w-100">
                            <div class="modal-icon">
                                <i class="fas ${actionConfig.icon} fa-lg"></i>
                            </div>
                            <div class="modal-title-content">
                                <h5 class="modal-title mb-0">${actionConfig.title}</h5>
                                <small>${colorName}</small>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <p class="modal-message">${actionConfig.message}</p>
                            
                            ${action === 'delete' ? `
                            <div class="alert alert-warning mt-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted">
                                            <strong>Advertencia:</strong> Esta acción no se puede deshacer. 
                                            Si el color está siendo usado por productos, no podrá ser eliminado.
                                        </small>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                            
                            ${action === 'deactivate' ? `
                            <div class="alert alert-info mt-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-info-circle me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted">
                                            Los colores desactivados no aparecerán en los formularios de productos.
                                        </small>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="button" class="btn confirm-action-btn" 
                                style="background: ${actionConfig.gradient};">
                            <i class="fas ${actionConfig.icon} me-1"></i> ${actionConfig.buttonText}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        // Agregar modal al body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Mostrar modal
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        
        modal.show();
        
        // Configurar botón de confirmación
        const confirmBtn = modalElement.querySelector('.confirm-action-btn');
        confirmBtn.addEventListener('click', () => {
            if (typeof config.onConfirm === 'function') {
                config.onConfirm();
            }
            modal.hide();
        });
        
        // Configurar botón de cancelar para limpiar
        const cancelBtn = modalElement.querySelector('.btn-outline-secondary');
        cancelBtn.addEventListener('click', () => {
            modal.hide();
        });
        
        // Limpiar al cerrar
        modalElement.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                modalElement.remove();
            }, 300);
        });
    }

    // ============================================
    // MEJORAR MENSAJES DE SESIÓN (PHP)
    // ============================================

    enhanceSessionMessages() {
        const sessionAlerts = document.querySelectorAll('.alert[role="alert"]');
        
        sessionAlerts.forEach(alert => {
            let type = 'info';
            let icon = 'fa-info-circle';
            
            if (alert.classList.contains('alert-success')) {
                type = 'success';
                icon = 'fa-check-circle';
            } else if (alert.classList.contains('alert-warning')) {
                type = 'warning';
                icon = 'fa-exclamation-triangle';
            } else if (alert.classList.contains('alert-danger')) {
                type = 'danger';
                icon = 'fa-exclamation-circle';
            }
            
            const message = alert.textContent.trim();
            
            alert.className = `alert alert-${type} alert-dismissible fade show color-message`;
            alert.innerHTML = `
                <div class="message-content">
                    <div class="message-icon">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="message-text">
                        <strong>${message}</strong>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <div class="message-progress"></div>
            `;
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                if (alert.parentNode) {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 5000);
        });
    }

    // ============================================
    // UTILIDADES
    // ============================================

    removeExistingModals() {
        // Eliminar modales de color existentes
        const existingModals = document.querySelectorAll('#colorConfirmModal, .color-toast-container');
        existingModals.forEach(modal => modal.remove());
    }

    // Función para simular éxito después de una acción
    simulateSuccess(action, colorName) {
        const messages = {
            'activate': `El color "${colorName}" ha sido activado correctamente.`,
            'deactivate': `El color "${colorName}" ha sido desactivado correctamente.`,
            'delete': `El color "${colorName}" ha sido eliminado correctamente.`
        };
        
        this.showSuccessToast(messages[action], colorName, action);
    }
}

// ============================================
// INICIALIZACIÓN GLOBAL
// ============================================

// Inicializar inmediatamente cuando se carga el script
(function() {
    // Esperar a que el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.colorMessages = new ColorMessages();
        });
    } else {
        // DOM ya está listo
        window.colorMessages = new ColorMessages();
    }
})();

// Exponer funciones globales
window.showColorMessage = function(message, action, colorName = '') {
    if (!window.colorMessages) {
        window.colorMessages = new ColorMessages();
    }
    window.colorMessages.showSuccessToast(message, colorName, action);
};

window.confirmColorAction = function(config) {
    if (!window.colorMessages) {
        window.colorMessages = new ColorMessages();
    }
    window.colorMessages.showConfirmModal(config);
};