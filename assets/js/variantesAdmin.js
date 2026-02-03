// variantesAdmin.js - Funcionalidades JavaScript para gestión de variantes

// ===== DEFINIR BASE_URL SI NO ESTÁ DEFINIDO =====
if (typeof BASE_URL === 'undefined') {
    // Intentar obtener BASE_URL del contexto
    const baseUrlElement = document.querySelector('meta[name="base-url"]');
    if (baseUrlElement) {
        window.BASE_URL = baseUrlElement.getAttribute('content');
    } else {
        // Fallback: calcular desde la URL actual
        const currentPath = window.location.pathname;
        const pathParts = currentPath.split('/');
        pathParts.pop(); // Remover el archivo actual
        window.BASE_URL = window.location.origin + pathParts.join('/') + '/';
    }
}

// ===== FUNCIONES PARA FORMULARIO PRINCIPAL =====
function procesarSubidaImagen() {
    const fileInput = document.getElementById('imagenVariante');
    
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        
        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('❌ Error: Solo se permiten archivos JPG, JPEG, PNG, GIF o WebP.');
            fileInput.value = '';
            mostrarMensaje('danger', '❌ Formato de imagen no permitido.');
            return false;
        }
        
        // Validar tamaño (15MB máximo)
        const maxSizeMB = 15;
        const maxSizeBytes = maxSizeMB * 1024 * 1024;
        
        if (file.size > maxSizeBytes) {
            alert(`❌ Error: La imagen es demasiado grande. Máximo ${maxSizeMB}MB.`);
            fileInput.value = '';
            mostrarMensaje('danger', `❌ La imagen es demasiado grande. Máximo ${maxSizeMB}MB.`);
            return false;
        }
        
        // Generar nombre único automáticamente
        const timestamp = Date.now();
        const randomId = Math.random().toString(36).substring(2, 9);
        const extension = file.name.split('.').pop().toLowerCase();
        const nombreArchivo = `var_${timestamp}_${randomId}.${extension}`;
        
        // Actualizar interfaz
        document.getElementById('nombreArchivoFinal').value = nombreArchivo;
        
        // Construir ruta completa
        document.getElementById('fotoFinal').value = nombreArchivo;
        
        // Mostrar vista previa
        const reader = new FileReader();
        reader.onload = function(e) {
            const vistaPreviaImg = document.getElementById('vistaPreviaImg');
            const vistaPreviaContainer = document.getElementById('vistaPreviaContainer');
            const infoArchivo = document.getElementById('infoArchivo');
            
            if (vistaPreviaImg) {
                vistaPreviaImg.src = e.target.result;
            }
            
            if (vistaPreviaContainer) {
                vistaPreviaContainer.style.display = 'block';
            }
            
            if (infoArchivo) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                infoArchivo.innerHTML = `
                    <strong>Archivo:</strong> ${nombreArchivo}<br>
                    <strong>Tamaño:</strong> ${sizeMB} MB<br>
                    <strong>Tipo:</strong> ${file.type}
                `;
            }
            
            mostrarMensaje('success', '✅ Imagen cargada correctamente. Lista para guardar.');
        };
        
        reader.onerror = function(error) {
            console.error('Error al leer archivo:', error);
            alert('❌ Error al cargar la imagen. Intenta con otra.');
            fileInput.value = '';
            mostrarMensaje('danger', '❌ Error al cargar la imagen.');
        };
        
        reader.readAsDataURL(file);
        return true;
    } else {
        alert('⚠️ Por favor, selecciona una imagen primero.');
        mostrarMensaje('warning', '⚠️ Selecciona una imagen primero.');
        return false;
    }
}

// ===== FUNCIONES PARA MODAL DE EDICIÓN =====
function procesarSubidaImagenEdit() {
    const fileInput = document.getElementById('imagenVarianteEdit');
    
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        
        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('❌ Error: Solo se permiten archivos JPG, JPEG, PNG, GIF o WebP.');
            fileInput.value = '';
            mostrarMensajeModal('danger', '❌ Formato de imagen no permitido.');
            return false;
        }
        
        // Validar tamaño (15MB máximo)
        const maxSizeMB = 15;
        const maxSizeBytes = maxSizeMB * 1024 * 1024;
        
        if (file.size > maxSizeBytes) {
            alert(`❌ Error: La imagen es demasiado grande. Máximo ${maxSizeMB}MB.`);
            fileInput.value = '';
            mostrarMensajeModal('danger', `❌ La imagen es demasiado grande. Máximo ${maxSizeMB}MB.`);
            return false;
        }
        
        // Generar nombre único automáticamente
        const timestamp = Date.now();
        const randomId = Math.random().toString(36).substring(2, 9);
        const extension = file.name.split('.').pop().toLowerCase();
        const nombreArchivo = `var_${timestamp}_${randomId}.${extension}`;
        
        // Actualizar interfaz
        document.getElementById('edit_nombreArchivoFinal').value = nombreArchivo;
        document.getElementById('edit_fotoFinal').value = nombreArchivo;
        
        // Ocultar imagen actual y mostrar vista previa de nueva imagen
        document.getElementById('imagenActualContainer').style.display = 'none';
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('edit_vistaPreviaImg').src = e.target.result;
            document.getElementById('edit_vistaPreviaContainer').style.display = 'block';
            mostrarMensajeModal('success', '✅ Nueva imagen cargada. Se reemplazará la anterior.');
        };
        reader.readAsDataURL(file);
        return true;
    } else {
        // Si no hay archivo seleccionado, restaurar imagen actual
        const rutaExistente = document.getElementById('edit_fotoFinal').value;
        if (rutaExistente) {
            document.getElementById('imagenActualContainer').style.display = 'block';
        }
        document.getElementById('edit_vistaPreviaContainer').style.display = 'none';
        return false;
    }
}

// ===== FUNCIONES AUXILIARES =====
function mostrarMensaje(tipo, texto) {
    const contenedor = document.getElementById('msgContainer');
    if (contenedor) {
        const alertasAnteriores = contenedor.querySelectorAll('.alert');
        alertasAnteriores.forEach(alerta => alerta.remove());
        
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show mt-2 shadow-sm`;
        alerta.innerHTML = `
            <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
            ${texto}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        contenedor.appendChild(alerta);
        
        setTimeout(() => {
            if (alerta.parentNode) {
                alerta.remove();
            }
        }, 5000);
    }
}

function mostrarMensajeModal(tipo, texto) {
    const contenedor = document.getElementById('msgModal');
    if (contenedor) {
        const alertasAnteriores = contenedor.querySelectorAll('.alert');
        alertasAnteriores.forEach(alerta => alerta.remove());
        
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show mt-2 shadow-sm`;
        alerta.innerHTML = `
            <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
            ${texto}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        contenedor.appendChild(alerta);
        
        setTimeout(() => {
            if (alerta.parentNode) {
                alerta.remove();
            }
        }, 5000);
    }
}

// FUNCIÓN CORREGIDA - SIN ERROR BASE_URL
function mostrarImagenActualEdit(rutaExistente) {
    if (!rutaExistente || rutaExistente.trim() === '') {
        document.getElementById('imagenActualContainer').style.display = 'none';
        document.getElementById('edit_nombreArchivoFinal').value = '';
        document.getElementById('edit_fotoFinal').value = '';
        return;
    }
    
    // Extraer solo el nombre del archivo
    const partes = rutaExistente.split('/');
    const nombreArchivo = partes.length > 0 ? partes[partes.length - 1] : rutaExistente;
    
    document.getElementById('edit_nombreArchivoFinal').value = nombreArchivo;
    document.getElementById('edit_fotoFinal').value = rutaExistente; // Guardar ruta completa
    
    // Mostrar información de la imagen actual
    document.getElementById('rutaActual').textContent = `Archivo: ${nombreArchivo}`;
    document.getElementById('imagenActualContainer').style.display = 'block';
    
    // Intentar cargar la imagen
    const imgElement = document.getElementById('imagenActual');
    if (imgElement) {
        // Usar la ruta relativa directamente
        imgElement.src = rutaExistente.startsWith('http') ? rutaExistente : 
                         (rutaExistente.startsWith('/') ? rutaExistente : '/' + rutaExistente);
        
        imgElement.onerror = function() {
            // Si falla, mostrar placeholder
            imgElement.src = BASE_URL + 'assets/img/sin_imagen.png';
            document.getElementById('rutaActual').textContent = 'Imagen no encontrada - Usando placeholder';
        };
        
        imgElement.onload = function() {
            // Imagen cargada correctamente
            document.getElementById('rutaActual').textContent = `Archivo: ${nombreArchivo} ✓`;
        };
    }
}

// ===== INICIALIZACIÓN Y VALIDACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarVariante');

    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            document.getElementById('edit_ID_Producto').value = btn.getAttribute('data-id');
            document.getElementById('edit_Nombre_Producto').value = btn.getAttribute('data-nombre') || '';
            document.getElementById('edit_Porcentaje').value = btn.getAttribute('data-porcentaje');
            document.getElementById('edit_Cantidad').value = btn.getAttribute('data-cantidad');
            document.getElementById('edit_Activo').value = btn.getAttribute('data-activo');
            
            // Cargar valores de atributos dinámicos
            for (let i = 1; i <= 3; i++) {
                const atributo = btn.getAttribute(`data-atributo${i}`);
                const valorAtributo = btn.getAttribute(`data-valor-atributo${i}`);
                if (atributo && valorAtributo) {
                    const select = document.getElementById(`edit_valor_atributo${i}`);
                    if (select) {
                        select.value = valorAtributo;
                    }
                }
            }
            
            const fotoExistente = btn.getAttribute('data-foto') || '';
            mostrarImagenActualEdit(fotoExistente);
            
            document.getElementById('edit_vistaPreviaContainer').style.display = 'none';
            document.getElementById('imagenVarianteEdit').value = '';
        });
        
        // Prevenir congelamiento al cerrar modal
        modalEditar.addEventListener('hidden.bs.modal', function() {
            // Limpiar focus
            document.activeElement?.blur();
            
            // Limpiar backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                if (backdrop.parentNode && backdrop.classList.contains('show')) {
                    backdrop.parentNode.removeChild(backdrop);
                }
            });
            
            // Restaurar scroll
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }

    // Validación del formulario principal
    const formVariante = document.getElementById('formVariante');
    if (formVariante) {
        formVariante.onsubmit = function(e) {
            const selects = this.querySelectorAll('select[name^="valor_atributo"]');
            let todosSeleccionados = true;
            
            selects.forEach(select => {
                if (!select.value) {
                    todosSeleccionados = false;
                    select.classList.add('is-invalid');
                } else {
                    select.classList.remove('is-invalid');
                }
            });
            
            if (!todosSeleccionados) {
                e.preventDefault();
                mostrarMensaje('danger', 'Por favor, completa todos los atributos requeridos.');
                return false;
            }
            
            const fileInput = document.getElementById('imagenVariante');
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                mostrarMensaje('danger', 'Debes seleccionar una imagen para la variante.');
                fileInput.focus();
                return false;
            }
            
            const porcentaje = this.querySelector('[name="Porcentaje"]');
            if (porcentaje.value < -90 || porcentaje.value > 300) {
                e.preventDefault();
                mostrarMensaje('danger', 'El porcentaje debe estar entre -90% y +300%.');
                porcentaje.focus();
                return false;
            }
            
            const cantidad = this.querySelector('[name="Cantidad"]');
            if (cantidad.value < 0 || cantidad.value > 99999) {
                e.preventDefault();
                mostrarMensaje('danger', 'La cantidad debe estar entre 0 y 99,999.');
                cantidad.focus();
                return false;
            }
            
            mostrarMensaje('success', '✅ Datos validados correctamente. Guardando...');
            return true;
        };
    }

    // Validación del formulario de edición
    const formEditarVariante = document.getElementById('formEditarVariante');
    if (formEditarVariante) {
        formEditarVariante.onsubmit = function(e) {
            const porcentaje = this.querySelector('[name="Porcentaje"]');
            if (porcentaje.value < -90 || porcentaje.value > 300) {
                e.preventDefault();
                mostrarMensajeModal('danger', 'El porcentaje debe estar entre -90% y +300%.');
                porcentaje.focus();
                return false;
            }
            
            const cantidad = this.querySelector('[name="Cantidad"]');
            if (cantidad.value < 0 || cantidad.value > 99999) {
                e.preventDefault();
                mostrarMensajeModal('danger', 'La cantidad debe estar entre 0 y 99,999.');
                cantidad.focus();
                return false;
            }
            
            mostrarMensajeModal('success', '✅ Datos validados correctamente. Guardando...');
            return true;
        };
    }

    // Validación de porcentaje en tiempo real
    const porcentajeInput = document.querySelector('input[name="Porcentaje"]');
    if (porcentajeInput) {
        porcentajeInput.addEventListener('change', function() {
            const valor = parseFloat(this.value);
            if (valor < -90 || valor > 300) {
                alert('⚠️ El porcentaje debe estar entre -90% y +300%');
                this.focus();
            }
        });
    }

    // Validación de archivo de imagen en tiempo real
    const imagenInput = document.querySelector('input[name="imagen_variante"]');
    if (imagenInput) {
        imagenInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                
                if (!allowedTypes.includes(file.type)) {
                    alert('❌ Error: Solo se permiten archivos JPG, JPEG, PNG, GIF o WebP.');
                    this.value = '';
                    return;
                }
                
                if (file.size > 15 * 1024 * 1024) {
                    alert('❌ Error: La imagen no puede ser mayor a 15MB.');
                    this.value = '';
                    return;
                }
            }
        });
    }
});

// ===== INCLUIR SISTEMA DE CONFIRMACIONES =====
// Verificar si hay enlaces que requieren confirmaciones personalizadas
if (document.querySelector('a[href*="toggleVariante"]') || document.querySelector('a[href*="eliminarVariante"]')) {
    // Cargar el script de confirmaciones
    const script = document.createElement('script');
    script.src = (window.BASE_URL || '') + 'assets/js/variantesConfirmaciones.js';
    script.onload = function() {
        console.log('✅ Sistema de confirmaciones cargado correctamente');
    };
    script.onerror = function() {
        console.warn('⚠️ No se pudo cargar el sistema de confirmaciones');
    };
    document.head.appendChild(script);
}