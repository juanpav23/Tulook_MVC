// colores.js - Funcionalidades para gestión de colores

class ColorManager {
    constructor() {
        this.hexInput = document.getElementById('CodigoHex');
        this.colorPickerBtn = document.getElementById('color-picker-btn');
        this.preview = document.getElementById('color-preview');
        this.previewText = document.getElementById('preview-text');
        this.form = document.getElementById('form-color');
        
        this.init();
    }
    
    init() {
        this.initColorPicker();
        this.initFormValidation();
        this.initPreview();
        
        // Si estamos en edición, establecer valor inicial
        if (this.preview && this.preview.style.backgroundColor) {
            const currentColor = this.preview.style.backgroundColor;
            this.previewText.textContent = this.rgbToHex(currentColor);
        }
    }
    
    // Inicializar selector de color
    initColorPicker() {
        if (this.colorPickerBtn && this.hexInput) {
            // Verificar si el navegador soporta EyeDropper API
            if ('EyeDropper' in window) {
                this.eyeDropper = new EyeDropper();
                
                this.colorPickerBtn.addEventListener('click', () => this.openColorPicker());
                this.colorPickerBtn.title = 'Seleccionar color de la pantalla';
                
            } else {
                // Navegador no compatible
                this.colorPickerBtn.innerHTML = '<i class="fas fa-palette"></i> Manual';
                this.colorPickerBtn.title = 'Navegador no compatible. Ingresa el código manualmente.';
                this.colorPickerBtn.disabled = true;
                
                this.showAlert('info', 'Tu navegador no soporta el selector de color. Ingresa el código hexadecimal manualmente (ej: FF0000 para rojo).', 5000);
            }
        }
    }
    
    // Abrir selector de color
    async openColorPicker() {
        try {
            const result = await this.eyeDropper.open();
            const hexColor = result.sRGBHex; // Ej: #FF0000
            
            // Remover # y convertir a mayúsculas
            const hexWithoutHash = hexColor.substring(1).toUpperCase();
            
            // Actualizar input
            this.hexInput.value = hexWithoutHash;
            
            // Actualizar vista previa
            this.updatePreview(hexColor);
            
            // Mostrar mensaje de éxito
            this.showAlert('success', `Color seleccionado: ${hexColor}`);
            
        } catch (err) {
            // Usuario canceló o hubo error
            if (err.name !== 'AbortError') {
                console.error('Error al seleccionar color:', err);
                this.showAlert('warning', 'No se pudo seleccionar el color. Ingresa el código manualmente.');
            }
        }
    }
    
    // Inicializar vista previa
    initPreview() {
        if (this.hexInput && this.preview) {
            // Actualizar al escribir
            this.hexInput.addEventListener('input', () => this.updatePreviewFromInput());
            
            // Inicializar con valor actual
            this.updatePreviewFromInput();
        }
    }
    
    // Actualizar vista previa desde input
    updatePreviewFromInput() {
        let hex = this.hexInput.value.trim().toUpperCase();
        
        if (hex) {
            // Asegurar que tenga #
            const hexWithHash = hex.startsWith('#') ? hex : '#' + hex;
            
            const hexRegex = /^#[0-9A-F]{6}$/i;
            if (hexRegex.test(hexWithHash)) {
                this.preview.style.backgroundColor = hexWithHash;
                this.previewText.textContent = hexWithHash;
                this.hexInput.classList.remove('is-invalid');
            } else if (hex.length === 6) {
                this.preview.style.backgroundColor = '#' + hex;
                this.previewText.textContent = '#' + hex;
                this.hexInput.classList.add('is-invalid');
            }
        } else {
            // Valor vacío
            this.preview.style.backgroundColor = '#FFFFFF';
            this.previewText.textContent = '#FFFFFF';
        }
    }
    
    // Actualizar vista previa con color específico
    updatePreview(hexColor) {
        if (this.preview) {
            this.preview.style.backgroundColor = hexColor;
            this.previewText.textContent = hexColor;
        }
    }
    
    // Convertir RGB a HEX
    rgbToHex(rgb) {
        if (rgb.startsWith('#')) return rgb;
        
        // Extraer valores RGB
        const match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        if (!match) return rgb;
        
        const r = parseInt(match[1]);
        const g = parseInt(match[2]);
        const b = parseInt(match[3]);
        
        return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
    }
    
    // Inicializar validación del formulario
    initFormValidation() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.validateForm(e));
            
            // Validación en tiempo real del nombre
            const nombreInput = document.getElementById('N_Color');
            if (nombreInput) {
                nombreInput.addEventListener('input', () => {
                    const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/;
                    if (!regex.test(nombreInput.value.trim()) && nombreInput.value !== '') {
                        nombreInput.classList.add('is-invalid');
                    } else {
                        nombreInput.classList.remove('is-invalid');
                    }
                });
            }
        }
    }
    
    // Validar formulario
    validateForm(e) {
        let valid = true;
        
        // Validar nombre
        const nombreInput = document.getElementById('N_Color');
        if (nombreInput) {
            const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/;
            if (!nombreRegex.test(nombreInput.value.trim())) {
                nombreInput.classList.add('is-invalid');
                valid = false;
            } else {
                nombreInput.classList.remove('is-invalid');
            }
        }
        
        // Validar HEX
        if (this.hexInput) {
            const hexRegex = /^[0-9A-F]{6}$/i;
            const hexValue = this.hexInput.value.trim().toUpperCase();
            if (!hexRegex.test(hexValue)) {
                this.hexInput.classList.add('is-invalid');
                valid = false;
            } else {
                this.hexInput.classList.remove('is-invalid');
            }
        }
        
        if (!valid) {
            e.preventDefault();
            e.stopPropagation();
            this.form.classList.add('was-validated');
            this.showAlert('warning', 'Por favor, corrige los errores en el formulario.', 5000);
        }
    }
    
    // Mostrar alerta
    showAlert(type, message, duration = 3000) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '1050';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, duration);
    }
}

// Función para copiar código HEX (para la lista de colores)
function copyHexCode(hexCode, button) {
    if (!hexCode) return;

    navigator.clipboard.writeText(hexCode)
        .then(() => {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('btn-success');
            }, 2000);
        })
        .catch(err => {
            console.error('Error al copiar:', err);
            showAlert('danger', 'Error al copiar el código');
        });
}

// Función para ver productos asociados
async function viewProducts(colorId, colorName) {
    if (!colorId) return;

    try {
        const response = await fetch(`?c=Color&a=obtenerProductosPorColor&id=${colorId}`);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        const result = await response.json();

        if (result.success) {
            showProductsModal(colorName, result.productos);
        } else {
            showAlert('warning', result.message || 'No hay productos asociados a este color');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('danger', 'Error al cargar los productos. Verifica la conexión.');
    }
}

// Función para mostrar modal de productos
function showProductsModal(colorName, products) {
    let modalContent = `
        <div class="modal fade" id="productsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-boxes me-2"></i>
                            Productos que usan: ${colorName}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
    `;

    if (products && products.length > 0) {
        modalContent += `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Artículo</th>
                            <th>Categoría</th>
                            <th>Subcategoría</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        products.forEach(product => {
            modalContent += `
                <tr>
                    <td><strong>${product.Nombre_Producto || 'Sin nombre'}</strong></td>
                    <td>${product.Articulo || '-'}</td>
                    <td>${product.N_Categoria || '-'}</td>
                    <td>${product.SubCategoria || '-'}</td>
                </tr>
            `;
        });

        modalContent += `
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                Total: ${products.length} productos encontrados
            </div>
        `;
    } else {
        modalContent += `
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5>No hay productos asociados</h5>
                <p class="text-muted">Este color no está siendo usado por ningún producto.</p>
            </div>
        `;
    }

    modalContent += `
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    const existingModal = document.getElementById('productsModal');
    if (existingModal) existingModal.remove();

    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    const modal = new bootstrap.Modal(document.getElementById('productsModal'));
    modal.show();
}

// Función para mostrar alertas (genérica)
function showAlert(type, message, duration = 5000) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.insertAdjacentElement('afterbegin', alertDiv);
    } else {
        // Contenedor temporal
        const tempContainer = document.createElement('div');
        tempContainer.className = 'container-fluid mt-3';
        tempContainer.appendChild(alertDiv);
        document.querySelector('.main-content').insertAdjacentElement('afterbegin', tempContainer);
    }
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, duration);
}

// Confirmación de eliminación
function confirmEliminarColor(event, colorName) {
    event.preventDefault();
    const url = event.currentTarget.href;
    
    if (confirm(`¿Estás seguro de eliminar el color "${colorName}"?\nEsta acción no se puede deshacer.`)) {
        window.location.href = url;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gestor de colores para formularios
    if (document.getElementById('form-color')) {
        window.colorFormManager = new ColorManager();
    }
});

function validarCambioEstado(event, colorId, colorNombre, estaEnUso, estaActivo) {
    if (estaActivo && estaEnUso) {
        // Intentar desactivar un color en uso
        event.preventDefault();
        showAlert('warning', `No se puede desactivar el color "${colorNombre}" porque está siendo usado por productos.`, 5000);
        return false;
    }
    
    const accion = estaActivo ? 'desactivar' : 'activar';
    return confirm(`¿Estás seguro de ${accion} el color "${colorNombre}"?`);
}