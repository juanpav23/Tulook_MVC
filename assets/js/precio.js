/**
 * precio.js - Funciones para gestión de precios
 */

// Variables globales
let valorNumerico = 0;
let precioFormManager = null;

// Clase principal para gestionar el formulario de precios
class PrecioFormManager {
    constructor() {
        this.valorVisual = document.getElementById('ValorVisual');
        this.valorReal = document.getElementById('ValorReal');
        this.form = document.getElementById('form-precio');
        this.sugerenciaElement = document.getElementById('sugerencia-valor');
        this.previewElement = document.getElementById('valor-preview');
        
        this.init();
    }
    
    init() {
        if (this.valorVisual && this.valorReal) {
            // Inicializar valor si existe
            this.inicializarValor();
            
            // Configurar eventos
            this.configurarEventos();
            
            // Configurar botones de sugerencia
            this.configurarBotonesSugerencia();
            
            // Configurar botones de acción
            this.configurarBotonesAccion();
        }
    }
    
    inicializarValor() {
        if (this.valorReal && this.valorReal.value) {
            // Convertir valor a número
            valorNumerico = parseFloat(this.valorReal.value) || 0;
            
            // Formatear visualmente
            if (valorNumerico > 0) {
                this.valorVisual.value = this.formatearNumero(valorNumerico);
            }
            
            // Actualizar sugerencia
            this.actualizarSugerencia(valorNumerico);
        }
    }
    
    configurarEventos() {
        // Evento input para formatear moneda
        this.valorVisual.addEventListener('input', (e) => this.formatearMonedaVisual(e.target));
        
        // Evento blur para validar valor completo
        this.valorVisual.addEventListener('blur', (e) => this.validarValorCompleto(e.target));
        
        // Evento keypress para permitir solo números
        this.valorVisual.addEventListener('keypress', (e) => this.soloNumeros(e));
        
        // Evento submit del formulario
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.validarYEnviarFormulario(e));
        }
    }
    
    configurarBotonesSugerencia() {
        // Botones de sugerencia rápida
        document.querySelectorAll('.btn-outline-primary[data-valor]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const valor = parseInt(e.currentTarget.getAttribute('data-valor'), 10);
                this.sugerirValor(valor);
            });
        });
        
        // Botones de sugerencia por categoría
        document.querySelectorAll('.btn-sugerencia').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const valor = parseInt(e.currentTarget.getAttribute('data-valor'), 10);
                this.sugerirValor(valor);
            });
        });
    }
    
    configurarBotonesAccion() {
        // Botón de ayuda
        const btnAyuda = document.querySelector('button[onclick="mostrarAyuda()"]');
        if (btnAyuda) {
            btnAyuda.addEventListener('click', () => this.mostrarAyuda());
        }
        
        // Botones de ver productos
        document.querySelectorAll('.btn-ver-productos').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                const valor = e.currentTarget.getAttribute('data-valor');
                this.verProductosPrecio(id, valor);
            });
        });
        
        // Botones de migrar precio
        document.querySelectorAll('.btn-migrar-precio').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                const valor = e.currentTarget.getAttribute('data-valor');
                this.migrarPrecio(id, valor);
            });
        });
        
        // Botones de eliminar precio
        document.querySelectorAll('.btn-eliminar-precio').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const id = e.currentTarget.getAttribute('data-id');
                const valor = e.currentTarget.getAttribute('data-valor');
                const url = e.currentTarget.getAttribute('href');
                this.confirmarEliminarPrecio(valor, id, url);
            });
        });
    }
    
    // Formatear moneda visualmente (solo para mostrar)
    formatearMonedaVisual(input) {
        let valor = input.value.replace(/[^\d]/g, '');
        
        if (valor === '') {
            input.value = '';
            valorNumerico = 0;
            this.actualizarCampoOculto();
            this.actualizarSugerencia(0);
            return;
        }
        
        // Convertir a número y almacenar
        valorNumerico = parseInt(valor, 10);
        
        // Formatear visualmente con separadores de miles
        input.value = this.formatearNumero(valorNumerico);
        
        // Actualizar campo oculto con el valor real
        this.actualizarCampoOculto();
        
        // Actualizar sugerencia y preview
        this.actualizarSugerencia(valorNumerico);
    }
    
    // Actualizar campo oculto con el valor real
    actualizarCampoOculto() {
        if (this.valorReal) {
            this.valorReal.value = valorNumerico;
        }
    }
    
    // Validar valor completo cuando se pierde el foco
    validarValorCompleto(input) {
        let valor = input.value.replace(/[^\d]/g, '');
        
        if (valor === '') {
            valorNumerico = 0;
            this.actualizarCampoOculto();
            return;
        }
        
        // Asegurar que se guarde el valor completo
        valorNumerico = parseInt(valor, 10);
        this.actualizarCampoOculto();
        
        // Formatear visualmente
        input.value = this.formatearNumero(valorNumerico);
    }
    
    // Permitir solo números en el input
    soloNumeros(e) {
        // Permitir solo números y teclas de control
        if (!/[\d]|Backspace|Delete|Tab|Enter|Arrow/.test(e.key)) {
            e.preventDefault();
        }
    }
    
    // Sugerir valor
    sugerirValor(valor) {
        if (!this.valorVisual) return;
        
        // Establecer valor numérico
        valorNumerico = valor;
        
        // Actualizar campo oculto
        this.actualizarCampoOculto();
        
        // Formatear visualmente
        this.valorVisual.value = this.formatearNumero(valor);
        
        // Actualizar sugerencia
        this.actualizarSugerencia(valor);
        
        // Mostrar feedback
        this.mostrarMensaje('Valor sugerido', `Se ha establecido el valor en $${this.formatearNumero(valor)}`, 'info');
    }
    
    // Actualizar sugerencia según el valor
    actualizarSugerencia(valor) {
        if (!this.sugerenciaElement || !this.previewElement) return;
        
        // Mostrar valor numérico real
        this.previewElement.textContent = `(${this.formatearNumero(valor)})`;
        
        let sugerenciaHTML = '';
        
        if (valor === 0) {
            sugerenciaHTML = '<i class="fas fa-tag me-1 text-primary"></i> Ingresa un valor';
            this.previewElement.textContent = '';
        } else if (valor < 10000) {
            sugerenciaHTML = '<i class="fas fa-tag me-1 text-primary"></i> Precio bajo - Ideal para accesorios';
        } else if (valor < 50000) {
            sugerenciaHTML = '<i class="fas fa-tag me-1 text-primary"></i> Precio medio - Ropa básica';
        } else if (valor < 150000) {
            sugerenciaHTML = '<i class="fas fa-tag me-1 text-primary"></i> Precio alto - Calzado y productos premium';
        } else if (valor < 1000000) {
            sugerenciaHTML = '<i class="fas fa-tag me-1 text-primary"></i> Precio muy alto - Productos especializados';
        } else {
            sugerenciaHTML = '<i class="fas fa-tag me-1 text-primary"></i> Precio premium - Productos de alta gama';
        }
        
        this.sugerenciaElement.innerHTML = sugerenciaHTML;
    }
    
    // Validar y enviar formulario
    validarYEnviarFormulario(event) {
        if (!this.valorVisual || !this.valorReal) {
            event.preventDefault();
            this.mostrarError('Error en el formulario');
            return false;
        }
        
        // Obtener valor del campo oculto
        let valor = parseInt(this.valorReal.value, 10);
        
        if (isNaN(valor) || valor <= 0) {
            event.preventDefault();
            this.mostrarError('El valor debe ser mayor a 0');
            return false;
        }
        
        if (valor > 1000000000) {
            event.preventDefault();
            this.mostrarError('El valor es demasiado grande (máximo: 1.000.000.000)');
            return false;
        }
        
        // Todo correcto, permitir envío
        return true;
    }
    
    // Función para ver productos asociados a un precio
    verProductosPrecio(idPrecio, valor) {
        Swal.fire({
            title: 'Cargando productos...',
            html: 'Buscando productos que usan este precio',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch(`?c=Precio&a=obtenerProductos&id=${idPrecio}`)
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta del servidor');
                return response.json();
            })
            .then(data => {
                Swal.close();
                
                if (!data.success) {
                    this.mostrarError(data.message || 'No se pudieron cargar los productos');
                    return;
                }
                
                this.mostrarModalProductos(valor, data.articulos || [], data.variantes || []);
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                this.mostrarError('No se pudieron cargar los productos. Verifica la conexión.');
            });
    }
    
    // Mostrar modal con productos
    mostrarModalProductos(valor, articulos, variantes) {
        let html = `<h5 class="mb-4 text-primary-dark">Productos con precio: <strong>$${valor}</strong></h5>`;
        
        if (articulos.length === 0 && variantes.length === 0) {
            html += `<div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-primary-light mb-3"></i>
                        <h5 class="text-primary-dark">No hay productos asociados</h5>
                        <p class="text-primary-light">Este precio no está siendo usado por ningún producto.</p>
                    </div>`;
        } else {
            // Mostrar artículos base
            if (articulos.length > 0) {
                html += `<h6 class="text-primary-dark mt-3 mb-2">
                            <i class="fas fa-cube me-2"></i>Artículos Base (${articulos.length})
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Subcategoría</th>
                                        <th>Género</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                
                articulos.forEach(art => {
                    html += `<tr>
                                <td><strong>${art.N_Articulo || 'Sin nombre'}</strong></td>
                                <td><span class="badge bg-primary-light">${art.Categoria || '-'}</span></td>
                                <td><small class="text-primary-light">${art.SubCategoria || '-'}</small></td>
                                <td><small class="text-primary-light">${art.Genero || '-'}</small></td>
                                <td>
                                    <span class="badge ${art.Activo == 1 ? 'bg-primary' : 'bg-secondary'}">
                                        ${art.Activo == 1 ? 'Activo' : 'Inactivo'}
                                    </span>
                                </td>
                            </tr>`;
                });
                
                html += `</tbody></table></div>`;
            }
            
            // Mostrar variantes (productos)
            if (variantes.length > 0) {
                html += `<h6 class="text-primary-dark mt-4 mb-2">
                            <i class="fas fa-layer-group me-2"></i>Variantes de Productos (${variantes.length})
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Artículo Base</th>
                                        <th>Atributos</th>
                                        <th>Stock</th>
                                        <th>Categoría</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                
                variantes.forEach(prod => {
                    html += `<tr>
                                <td><strong>${prod.Nombre_Producto || 'Sin nombre'}</strong></td>
                                <td><small class="text-primary-light">${prod.ArticuloNombre || '-'}</small></td>
                                <td>
                                    <small class="text-primary-light">
                                        ${[prod.ValorAtributo1, prod.ValorAtributo2, prod.ValorAtributo3]
                                            .filter(val => val && val.trim() !== '')
                                            .join(' / ') || '-'}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge ${prod.Cantidad > 0 ? 'bg-primary' : 'bg-secondary'}">
                                        ${prod.Cantidad || 0}
                                    </span>
                                </td>
                                <td><span class="badge bg-primary-light">${prod.Categoria || '-'}</span></td>
                            </tr>`;
                });
                
                html += `</tbody></table></div>`;
            }
            
            // Resumen
            html += `<div class="alert alert-primary-light mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <small class="text-primary-dark">
                            <strong>Resumen:</strong> 
                            ${articulos.length} artículo(s) base + ${variantes.length} variante(s) = 
                            <strong>${articulos.length + variantes.length} producto(s) en total</strong>
                        </small>
                    </div>`;
        }
        
        Swal.fire({
            title: 'Productos Asociados',
            html: html,
            width: '900px',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#1B202D',
            customClass: {
                popup: 'border-primary',
                header: 'border-bottom-0',
                title: 'text-primary-dark'
            }
        });
    }
    
    // Confirmación para eliminar precio
    confirmarEliminarPrecio(valor, idPrecio, url) {
        Swal.fire({
            title: '¿Eliminar precio?',
            html: `¿Estás seguro de eliminar el precio <strong>$${valor}</strong>?<br><br>
                   <small class="text-primary-light">Esta acción no se puede deshacer.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#1B202D',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
    
    // Migrar productos de un precio a otro
    migrarPrecio(idPrecioOrigen, valorOrigen) {
        Swal.fire({
            title: 'Cargando precios disponibles...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch(`?c=Precio&a=obtenerPreciosActivos&excluir=${idPrecioOrigen}`)
            .then(response => response.json())
            .then(precios => {
                Swal.close();
                
                if (!precios || Object.keys(precios).length === 0) {
                    Swal.fire({
                        title: 'No hay precios disponibles',
                        html: 'No existen otros precios activos a los que migrar los productos.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#1B202D'
                    });
                    return;
                }
                
                Swal.fire({
                    title: 'Migrar productos',
                    html: `Selecciona el nuevo precio al que migrar los productos que actualmente usan <strong>$${valorOrigen}</strong>`,
                    input: 'select',
                    inputOptions: precios,
                    inputPlaceholder: 'Selecciona un precio',
                    showCancelButton: true,
                    confirmButtonText: 'Migrar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#1B202D',
                    cancelButtonColor: '#6c757d',
                    showLoaderOnConfirm: true,
                    reverseButtons: true,
                    preConfirm: (idPrecioDestino) => {
                        if (!idPrecioDestino) {
                            Swal.showValidationMessage('Debes seleccionar un precio');
                            return false;
                        }
                        
                        return fetch(`?c=Precio&a=migrarProductos&origen=${idPrecioOrigen}&destino=${idPrecioDestino}`)
                            .then(response => response.json())
                            .then(data => {
                                if (!data.success) {
                                    throw new Error(data.message || 'Error en la migración');
                                }
                                return data;
                            })
                            .catch(error => {
                                Swal.showValidationMessage(`Error: ${error.message}`);
                            });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: '¡Migración exitosa!',
                            html: `Los productos han sido migrados correctamente.<br>
                                   <small class="text-primary-light">
                                   Se migraron ${result.value.total_migrados} producto(s).
                                   </small>`,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#1B202D'
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            })
            .catch(error => {
                Swal.close();
                this.mostrarError('No se pudieron cargar los precios disponibles');
                console.error('Error:', error);
            });
    }
    
    // Mostrar ayuda
    mostrarAyuda() {
        Swal.fire({
            title: 'Ayuda - Gestión de Precios',
            html: `<div class="text-start">
                    <h6>📝 Cómo ingresar valores:</h6>
                    <ul>
                        <li><strong>Ingresa el valor numérico completo:</strong> Ej: "1000000" para un millón</li>
                        <li><strong>El sistema formatea automáticamente:</strong> Se muestra como "1.000.000"</li>
                        <li><strong>Se guarda el valor completo:</strong> 1000000 (no 10.00)</li>
                    </ul>
                    <h6 class="mt-3">💡 Ejemplos:</h6>
                    <ul>
                        <li><strong>10.000</strong> → Ingresa: "10000" → Se guarda: 10000</li>
                        <li><strong>250.000</strong> → Ingresa: "250000" → Se guarda: 250000</li>
                        <li><strong>1.000.000</strong> → Ingresa: "1000000" → Se guarda: 1000000</li>
                    </ul>
                    <h6 class="mt-3">⚠️ Notas importantes:</h6>
                    <ul>
                        <li>No uses puntos ni comas al ingresar el valor</li>
                        <li>Los decimales no se soportan actualmente</li>
                        <li>Usa los botones de sugerencia para valores comunes</li>
                    </ul>
                   </div>`,
            width: 600,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#1B202D'
        });
    }
    
    // Funciones auxiliares
    formatearNumero(numero) {
        return numero.toLocaleString('es-CO');
    }
    
    mostrarError(mensaje) {
        Swal.fire('Error', mensaje, 'error');
    }
    
    mostrarMensaje(titulo, mensaje, tipo = 'info') {
        Swal.fire({
            title: titulo,
            text: mensaje,
            icon: tipo,
            timer: 1500,
            showConfirmButton: false
        });
    }
}

// Funciones para la vista de listado (index.php)
class PrecioListManager {
    constructor() {
        this.init();
    }
    
    init() {
        this.configurarBotonesAccion();
    }
    
    configurarBotonesAccion() {
        // Botones de ver productos en la tabla
        document.querySelectorAll('button[onclick^="verProductosPrecio"]').forEach(btn => {
            const onclick = btn.getAttribute('onclick');
            const matches = onclick.match(/verProductosPrecio\((\d+),\s*'([^']+)'\)/);
            if (matches) {
                btn.removeAttribute('onclick');
                btn.addEventListener('click', () => {
                    this.verProductosPrecio(matches[1], matches[2]);
                });
            }
        });
        
        // Botones de cambiar estado
        document.querySelectorAll('a[onclick^="confirmarCambiarEstado"]').forEach(link => {
            const onclick = link.getAttribute('onclick');
            const matches = onclick.match(/confirmarCambiarEstado\(event,\s*'([^']+)',\s*(\d+),\s*(\d+)\)/);
            if (matches) {
                link.removeAttribute('onclick');
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.confirmarCambiarEstado(matches[1], matches[2], parseInt(matches[3]));
                });
            }
        });
        
        // Botones de migrar precio
        document.querySelectorAll('button[onclick^="migrarPrecio"]').forEach(btn => {
            const onclick = btn.getAttribute('onclick');
            const matches = onclick.match(/migrarPrecio\((\d+),\s*'([^']+)'\)/);
            if (matches) {
                btn.removeAttribute('onclick');
                btn.addEventListener('click', () => {
                    this.migrarPrecio(matches[1], matches[2]);
                });
            }
        });
        
        // Botones de eliminar precio
        document.querySelectorAll('a[onclick^="confirmarEliminarPrecio"]').forEach(link => {
            const onclick = link.getAttribute('onclick');
            const matches = onclick.match(/confirmarEliminarPrecio\(event,\s*'([^']+)',\s*(\d+)\)/);
            if (matches) {
                link.removeAttribute('onclick');
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = link.getAttribute('href');
                    this.confirmarEliminarPrecio(matches[1], matches[2], url);
                });
            }
        });
    }
    
    verProductosPrecio(idPrecio, valor) {
        Swal.fire({
            title: 'Cargando productos...',
            html: 'Buscando productos que usan este precio',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch(`?c=Precio&a=obtenerProductos&id=${idPrecio}`)
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta del servidor');
                return response.json();
            })
            .then(data => {
                Swal.close();
                
                if (!data.success) {
                    Swal.fire('Error', data.message || 'No se pudieron cargar los productos', 'error');
                    return;
                }
                
                this.mostrarModalProductos(valor, data.articulos || [], data.variantes || []);
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudieron cargar los productos. Verifica la conexión.', 'error');
            });
    }
    
    mostrarModalProductos(valor, articulos, variantes) {
        let html = `<h5 class="mb-4 text-primary-dark">Productos con precio: <strong>$${valor}</strong></h5>`;
        
        if (articulos.length === 0 && variantes.length === 0) {
            html += `<div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-primary-light mb-3"></i>
                        <h5 class="text-primary-dark">No hay productos asociados</h5>
                        <p class="text-primary-light">Este precio no está siendo usado por ningún producto.</p>
                    </div>`;
        } else {
            // Mostrar artículos base
            if (articulos.length > 0) {
                html += `<h6 class="text-primary-dark mt-3 mb-2">
                            <i class="fas fa-cube me-2"></i>Artículos Base (${articulos.length})
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Subcategoría</th>
                                        <th>Género</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                
                articulos.forEach(art => {
                    html += `<tr>
                                <td><strong>${art.N_Articulo || 'Sin nombre'}</strong></td>
                                <td><span class="badge bg-primary-light">${art.Categoria || '-'}</span></td>
                                <td><small class="text-primary-light">${art.SubCategoria || '-'}</small></td>
                                <td><small class="text-primary-light">${art.Genero || '-'}</small></td>
                                <td>
                                    <span class="badge ${art.Activo == 1 ? 'bg-primary' : 'bg-secondary'}">
                                        ${art.Activo == 1 ? 'Activo' : 'Inactivo'}
                                    </span>
                                </td>
                            </tr>`;
                });
                
                html += `</tbody></table></div>`;
            }
            
            // Mostrar variantes (productos)
            if (variantes.length > 0) {
                html += `<h6 class="text-primary-dark mt-4 mb-2">
                            <i class="fas fa-layer-group me-2"></i>Variantes de Productos (${variantes.length})
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Artículo Base</th>
                                        <th>Atributos</th>
                                        <th>Stock</th>
                                        <th>Categoría</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                
                variantes.forEach(prod => {
                    html += `<tr>
                                <td><strong>${prod.Nombre_Producto || 'Sin nombre'}</strong></td>
                                <td><small class="text-primary-light">${prod.ArticuloNombre || '-'}</small></td>
                                <td>
                                    <small class="text-primary-light">
                                        ${[prod.ValorAtributo1, prod.ValorAtributo2, prod.ValorAtributo3]
                                            .filter(val => val && val.trim() !== '')
                                            .join(' / ') || '-'}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge ${prod.Cantidad > 0 ? 'bg-primary' : 'bg-secondary'}">
                                        ${prod.Cantidad || 0}
                                    </span>
                                </td>
                                <td><span class="badge bg-primary-light">${prod.Categoria || '-'}</span></td>
                            </tr>`;
                });
                
                html += `</tbody></table></div>`;
            }
            
            // Resumen
            html += `<div class="alert alert-primary-light mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <small class="text-primary-dark">
                            <strong>Resumen:</strong> 
                            ${articulos.length} artículo(s) base + ${variantes.length} variante(s) = 
                            <strong>${articulos.length + variantes.length} producto(s) en total</strong>
                        </small>
                    </div>`;
        }
        
        Swal.fire({
            title: 'Productos Asociados',
            html: html,
            width: '900px',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#1B202D',
            customClass: {
                popup: 'border-primary',
                header: 'border-bottom-0',
                title: 'text-primary-dark'
            }
        });
    }
    
    confirmarCambiarEstado(valor, idPrecio, nuevoEstado) {
        const accion = nuevoEstado == 0 ? 'desactivar' : 'activar';
        const icono = nuevoEstado == 0 ? 'warning' : 'question';
        const textoBoton = nuevoEstado == 0 ? 'Desactivar' : 'Activar';
        
        Swal.fire({
            title: `¿${textoBoton} precio?`,
            html: `¿Estás seguro de ${accion} el precio <strong>$${valor}</strong>?`,
            icon: icono,
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: nuevoEstado == 0 ? '#1B202D' : '#1B202D',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `?c=Precio&a=cambiarEstado&id=${idPrecio}&estado=${nuevoEstado}`;
            }
        });
    }
    
    migrarPrecio(idPrecioOrigen, valorOrigen) {
        Swal.fire({
            title: 'Cargando precios disponibles...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch(`?c=Precio&a=obtenerPreciosActivos&excluir=${idPrecioOrigen}`)
            .then(response => response.json())
            .then(precios => {
                Swal.close();
                
                if (!precios || Object.keys(precios).length === 0) {
                    Swal.fire({
                        title: 'No hay precios disponibles',
                        html: 'No existen otros precios activos a los que migrar los productos.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#1B202D'
                    });
                    return;
                }
                
                Swal.fire({
                    title: 'Migrar productos',
                    html: `Selecciona el nuevo precio al que migrar los productos que actualmente usan <strong>$${valorOrigen}</strong>`,
                    input: 'select',
                    inputOptions: precios,
                    inputPlaceholder: 'Selecciona un precio',
                    showCancelButton: true,
                    confirmButtonText: 'Migrar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#1B202D',
                    cancelButtonColor: '#6c757d',
                    showLoaderOnConfirm: true,
                    reverseButtons: true,
                    preConfirm: (idPrecioDestino) => {
                        if (!idPrecioDestino) {
                            Swal.showValidationMessage('Debes seleccionar un precio');
                            return false;
                        }
                        
                        return fetch(`?c=Precio&a=migrarProductos&origen=${idPrecioOrigen}&destino=${idPrecioDestino}`)
                            .then(response => response.json())
                            .then(data => {
                                if (!data.success) {
                                    throw new Error(data.message || 'Error en la migración');
                                }
                                return data;
                            })
                            .catch(error => {
                                Swal.showValidationMessage(`Error: ${error.message}`);
                            });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: '¡Migración exitosa!',
                            html: `Los productos han sido migrados correctamente.<br>
                                   <small class="text-primary-light">
                                   Se migraron ${result.value.total_migrados} producto(s).
                                   </small>`,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#1B202D'
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            })
            .catch(error => {
                Swal.close();
                Swal.fire('Error', 'No se pudieron cargar los precios disponibles', 'error');
                console.error('Error:', error);
            });
    }
    
    confirmarEliminarPrecio(valor, idPrecio, url) {
        Swal.fire({
            title: '¿Eliminar precio?',
            html: `¿Estás seguro de eliminar el precio <strong>$${valor}</strong>?<br><br>
                   <small class="text-primary-light">Esta acción no se puede deshacer.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#1B202D',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si estamos en el formulario de precios
    if (document.getElementById('form-precio')) {
        precioFormManager = new PrecioFormManager();
    }
    
    // Verificar si estamos en la lista de precios
    if (document.querySelector('table.table-striped')) {
        const precioListManager = new PrecioListManager();
    }
    
    // Tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});