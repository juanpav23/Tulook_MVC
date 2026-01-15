// variantesAdmin.js - Funcionalidades JavaScript para gestión de variantes

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

function mostrarImagenActualEdit(rutaExistente) {
    const partes = rutaExistente.split('/');
    const nombreArchivo = partes.length > 0 ? partes[partes.length - 1] : rutaExistente;
    
    document.getElementById('edit_nombreArchivoFinal').value = nombreArchivo;
    document.getElementById('edit_fotoFinal').value = nombreArchivo;
    
    const urlCompleta = BASE_URL + rutaExistente;
    document.getElementById('imagenActual').src = urlCompleta;
    document.getElementById('rutaActual').textContent = `Archivo: ${nombreArchivo}`;
    document.getElementById('imagenActualContainer').style.display = 'block';
    
    document.getElementById('imagenActual').onerror = function() {
        document.getElementById('imagenActualContainer').style.display = 'none';
        document.getElementById('rutaActual').textContent = 'Imagen no encontrada en el servidor';
    };
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
            if (fotoExistente) {
                mostrarImagenActualEdit(fotoExistente);
            } else {
                document.getElementById('imagenActualContainer').style.display = 'none';
                document.getElementById('edit_nombreArchivoFinal').value = '';
                document.getElementById('edit_fotoFinal').value = '';
            }
            
            document.getElementById('edit_vistaPreviaContainer').style.display = 'none';
            document.getElementById('imagenVarianteEdit').value = '';
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

// Función para confirmar eliminación con estilo personalizado
function confirmarEliminacion(url) {
    const modalHTML = `
        <div class="modal fade" id="confirmModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-light">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>⚠️ ¿Seguro que deseas eliminar esta variante?</p>
                        <p class="text-muted small">Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-detalle-outline-primary-light" data-bs-dismiss="modal">Cancelar</button>
                        <a href="${url}" class="btn btn-detalle-primary">Eliminar</a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
    
    modalContainer.addEventListener('hidden.bs.modal', function() {
        modalContainer.remove();
    });
    
    return false;
}