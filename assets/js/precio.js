/**
 * precio.js - Funciones para gestión de precios
 * Versión corregida - Solo considera artículos base
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
            this.inicializarValor();
            this.configurarEventos();
            this.configurarBotonesSugerencia();
            this.configurarBotonesAccion();
        }
    }
    
    inicializarValor() {
        if (this.valorReal && this.valorReal.value) {
            valorNumerico = parseFloat(this.valorReal.value) || 0;
            if (valorNumerico > 0) {
                this.valorVisual.value = this.formatearNumero(valorNumerico);
            }
            this.actualizarSugerencia(valorNumerico);
        }
    }
    
    configurarEventos() {
        this.valorVisual.addEventListener('input', (e) => this.formatearMonedaVisual(e.target));
        this.valorVisual.addEventListener('blur', (e) => this.validarValorCompleto(e.target));
        this.valorVisual.addEventListener('keypress', (e) => this.soloNumeros(e));
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.validarYEnviarFormulario(e));
        }
    }
    
    configurarBotonesSugerencia() {
        document.querySelectorAll('.btn-outline-primary[data-valor]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const valor = parseInt(e.currentTarget.getAttribute('data-valor'), 10);
                this.sugerirValor(valor);
            });
        });
        
        document.querySelectorAll('.btn-sugerencia').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const valor = parseInt(e.currentTarget.getAttribute('data-valor'), 10);
                this.sugerirValor(valor);
            });
        });
    }
    
    configurarBotonesAccion() {
        const btnAyuda = document.querySelector('button[onclick="mostrarAyuda()"]');
        if (btnAyuda) {
            btnAyuda.addEventListener('click', () => this.mostrarAyuda());
        }
        
        document.querySelectorAll('.btn-ver-productos').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                const valor = e.currentTarget.getAttribute('data-valor');
                this.verProductosPrecio(id, valor);
            });
        });
        
        document.querySelectorAll('.btn-migrar-precio').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                const valor = e.currentTarget.getAttribute('data-valor');
                this.migrarPrecio(id, valor);
            });
        });
        
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
    
    formatearMonedaVisual(input) {
        let valor = input.value.replace(/[^\d]/g, '');
        
        if (valor === '') {
            input.value = '';
            valorNumerico = 0;
            this.actualizarCampoOculto();
            this.actualizarSugerencia(0);
            return;
        }
        
        valorNumerico = parseInt(valor, 10);
        input.value = this.formatearNumero(valorNumerico);
        this.actualizarCampoOculto();
        this.actualizarSugerencia(valorNumerico);
    }
    
    actualizarCampoOculto() {
        if (this.valorReal) {
            this.valorReal.value = valorNumerico;
        }
    }
    
    validarValorCompleto(input) {
        let valor = input.value.replace(/[^\d]/g, '');
        
        if (valor === '') {
            valorNumerico = 0;
            this.actualizarCampoOculto();
            return;
        }
        
        valorNumerico = parseInt(valor, 10);
        this.actualizarCampoOculto();
        input.value = this.formatearNumero(valorNumerico);
    }
    
    soloNumeros(e) {
        if (!/[\d]|Backspace|Delete|Tab|Enter|Arrow/.test(e.key)) {
            e.preventDefault();
        }
    }
    
    sugerirValor(valor) {
        if (!this.valorVisual) return;
        
        valorNumerico = valor;
        this.actualizarCampoOculto();
        this.valorVisual.value = this.formatearNumero(valor);
        this.actualizarSugerencia(valor);
        this.mostrarMensaje('Valor sugerido', `Se ha establecido el valor en $${this.formatearNumero(valor)}`, 'info');
    }
    
    actualizarSugerencia(valor) {
        if (!this.sugerenciaElement || !this.previewElement) return;
        
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
    
    validarYEnviarFormulario(event) {
        if (!this.valorVisual || !this.valorReal) {
            event.preventDefault();
            this.mostrarError('Error en el formulario');
            return false;
        }
        
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
        
        return true;
    }
    
    verProductosPrecio(idPrecio, valor) {
        Swal.fire({
            title: 'Cargando artículos...',
            html: 'Buscando artículos que usan este precio',
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
                    this.mostrarError(data.message || 'No se pudieron cargar los artículos');
                    return;
                }
                
                this.mostrarModalProductos(valor, data.articulos || [], data.variantes || []);
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                this.mostrarError('No se pudieron cargar los artículos. Verifica la conexión.');
            });
    }
    
    mostrarModalProductos(valor, articulos, variantes) {
        let html = `<h5 class="mb-4 text-primary-dark">Artículos con precio: <strong>$${valor}</strong></h5>`;
        
        if (articulos.length === 0) {
            html += `<div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-primary-light mb-3"></i>
                        <h5 class="text-primary-dark">No hay artículos asociados</h5>
                        <p class="text-primary-light">Este precio no está siendo usado por ningún artículo base.</p>
                    </div>`;
        } else {
            // Mostrar artículos base
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
                                    <th>Variantes</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>`;
            
            articulos.forEach(art => {
                const totalVariantes = art.total_variantes || 0;
                html += `<tr>
                            <td><strong>${art.N_Articulo || 'Sin nombre'}</strong></td>
                            <td><span class="badge bg-primary-light">${art.Categoria || '-'}</span></td>
                            <td><small class="text-primary-light">${art.SubCategoria || '-'}</small></td>
                            <td><small class="text-primary-light">${art.Genero || '-'}</small></td>
                            <td>
                                <span class="badge ${totalVariantes > 0 ? 'bg-primary' : 'bg-secondary'}">
                                    ${totalVariantes} variante(s)
                                </span>
                            </td>
                            <td>
                                <span class="badge ${art.Activo == 1 ? 'bg-primary' : 'bg-secondary'}">
                                    ${art.Activo == 1 ? 'Activo' : 'Inactivo'}
                                </span>
                            </td>
                        </tr>`;
            });
            
            html += `</tbody></table></div>`;
            
            // Mostrar variantes directas solo si existen
            if (variantes.length > 0) {
                html += `<h6 class="text-primary-dark mt-4 mb-2">
                            <i class="fas fa-layer-group me-2"></i>Variantes Directas (${variantes.length})
                            <small class="text-primary-light ms-2">(Variantes que usan este precio específicamente)</small>
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
            
            // Resumen CORREGIDO - Solo artículos base
            html += `<div class="alert alert-primary-light mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <small class="text-primary-dark">
                            <strong>Resumen:</strong> 
                            <strong>${articulos.length} artículo(s) base</strong>
                            ${variantes.length > 0 ? ` + ${variantes.length} variante(s) directa(s)` : ''}
                        </small>
                    </div>`;
        }
        
        Swal.fire({
            title: 'Artículos Asociados',
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
    
    confirmarEliminarPrecio(valor, idPrecio, url) {
        // Primero verificar si el precio está en uso (solo artículos base)
        fetch(`?c=Precio&a=obtenerProductos&id=${idPrecio}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.articulos?.length > 0) {
                    // El precio está en uso por artículos base, ofrecer migrar y eliminar
                    this.migrarYEliminarPrecio(idPrecio, valor);
                } else {
                    // El precio no está en uso, proceder con eliminación normal
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
            })
            .catch(() => {
                // Si hay error en la verificación, proceder con eliminación normal
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
            });
    }
    
    migrarPrecio(idPrecioOrigen, valorOrigen) {
        this.migrarProductosParaEliminar(idPrecioOrigen, valorOrigen, false);
    }
    
    migrarYEliminarPrecio(idPrecioOrigen, valorOrigen) {
        Swal.fire({
            title: 'Precio en uso',
            html: `El precio <strong>$${valorOrigen}</strong> está siendo usado por artículos base.<br>
                   <div class="mt-3">
                     <strong>Opciones:</strong>
                     <ul class="text-start mt-2">
                       <li><strong>Migrar y Eliminar:</strong> Migra los artículos a otro precio y elimina este</li>
                       <li><strong>Solo Migrar:</strong> Solo migra los artículos (mantiene el precio actual)</li>
                       <li><strong>Cancelar:</strong> Mantener el precio actual</li>
                     </ul>
                   </div>`,
            icon: 'info',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Migrar y Eliminar',
            denyButtonText: 'Solo Migrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#1B202D',
            denyButtonColor: '#1B202D',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            width: 600
        }).then((result) => {
            if (result.isConfirmed) {
                this.migrarProductosParaEliminar(idPrecioOrigen, valorOrigen, true);
            } else if (result.isDenied) {
                this.migrarProductosParaEliminar(idPrecioOrigen, valorOrigen, false);
            }
        });
    }
    
    migrarProductosParaEliminar(idPrecioOrigen, valorOrigen, eliminarDespues = false) {
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
                        html: 'No existen otros precios activos a los que migrar los artículos.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#1B202D'
                    });
                    return;
                }
                
                Swal.fire({
                    title: eliminarDespues ? 'Migrar y Eliminar' : 'Migrar artículos',
                    html: eliminarDespues 
                        ? `Selecciona el nuevo precio al que migrar los artículos que usan <strong>$${valorOrigen}</strong><br>
                           <small class="text-primary-light">Después de la migración, el precio actual será eliminado.</small>`
                        : `Selecciona el nuevo precio al que migrar los artículos que usan <strong>$${valorOrigen}</strong>`,
                    input: 'select',
                    inputOptions: precios,
                    inputPlaceholder: 'Selecciona un precio',
                    showCancelButton: true,
                    confirmButtonText: eliminarDespues ? 'Migrar y Eliminar' : 'Migrar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: eliminarDespues ? '#dc3545' : '#1B202D',
                    cancelButtonColor: '#6c757d',
                    showLoaderOnConfirm: true,
                    reverseButtons: true,
                    width: 600,
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
                                
                                if (eliminarDespues) {
                                    return this.eliminarPrecioDespuesDeMigrar(idPrecioOrigen, data);
                                }
                                
                                return data;
                            })
                            .catch(error => {
                                Swal.showValidationMessage(`Error: ${error.message}`);
                            });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (eliminarDespues) {
                            Swal.fire({
                                title: '¡Proceso completado!',
                                html: `✅ Los artículos han sido migrados y el precio ha sido eliminado.<br>
                                       <small class="text-primary-light">
                                       Se migraron ${result.value.articulos_migrados} artículo(s) base
                                       ${result.value.variantes_migradas > 0 ? ` + ${result.value.variantes_migradas} variante(s) directa(s)` : ''}
                                       </small>`,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#1B202D'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: '¡Migración exitosa!',
                                html: `Los artículos han sido migrados correctamente.<br>
                                       <small class="text-primary-light">
                                       Se migraron ${result.value.articulos_migrados} artículo(s) base
                                       ${result.value.variantes_migradas > 0 ? ` + ${result.value.variantes_migradas} variante(s) directa(s)` : ''}
                                       </small>`,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#1B202D'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                });
            })
            .catch(error => {
                Swal.close();
                this.mostrarError('No se pudieron cargar los precios disponibles');
                console.error('Error:', error);
            });
    }
    
    eliminarPrecioDespuesDeMigrar(idPrecio, datosMigracion) {
        return fetch(`?c=Precio&a=eliminar&id=${idPrecio}`)
            .then(() => {
                return {
                    ...datosMigracion,
                    eliminado: true
                };
            })
            .catch(error => {
                throw new Error(`Error al eliminar el precio después de migrar: ${error.message}`);
            });
    }
    
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
        this.configurarEventosGenerales();
    }
    
    configurarBotonesAccion() {
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
        
        document.querySelectorAll('a[onclick^="confirmarEliminarPrecio"]').forEach(link => {
            const onclick = link.getAttribute('onclick');
            const matches = onclick.match(/confirmarEliminarPrecio\(event,\s*'([^']+)',\s*(\d+)\)/);
            if (matches) {
                link.removeAttribute('onclick');
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.confirmarEliminarPrecio(matches[1], matches[2]);
                });
            }
        });
        
        document.querySelectorAll('button[onclick^="migrarYEliminarPrecio"]').forEach(btn => {
            const onclick = btn.getAttribute('onclick');
            const matches = onclick.match(/migrarYEliminarPrecio\((\d+),\s*'([^']+)'\)/);
            if (matches) {
                btn.removeAttribute('onclick');
                btn.addEventListener('click', () => {
                    this.migrarYEliminarPrecio(matches[1], matches[2]);
                });
            }
        });
    }
    
    configurarEventosGenerales() {
        document.querySelectorAll('.btn[disabled], a[disabled]').forEach(element => {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const title = this.getAttribute('title') || 'Acción no disponible';
                Swal.fire({
                    title: 'Acción no disponible',
                    text: title,
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                return false;
            });
        });
    }
    
    verProductosPrecio(idPrecio, valor) {
        Swal.fire({
            title: 'Cargando artículos...',
            html: 'Buscando artículos que usan este precio',
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
                    Swal.fire('Error', data.message || 'No se pudieron cargar los artículos', 'error');
                    return;
                }
                
                this.mostrarModalProductos(valor, data.articulos || [], data.variantes || []);
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudieron cargar los artículos. Verifica la conexión.', 'error');
            });
    }
    
    mostrarModalProductos(valor, articulos, variantes) {
        let html = `<h5 class="mb-4 text-primary-dark">Artículos con precio: <strong>$${valor}</strong></h5>`;
        
        if (articulos.length === 0) {
            html += `<div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-primary-light mb-3"></i>
                        <h5 class="text-primary-dark">No hay artículos asociados</h5>
                        <p class="text-primary-light">Este precio no está siendo usado por ningún artículo base.</p>
                    </div>`;
        } else {
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
                                    <th>Variantes</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>`;
            
            articulos.forEach(art => {
                const totalVariantes = art.total_variantes || 0;
                html += `<tr>
                            <td><strong>${art.N_Articulo || 'Sin nombre'}</strong></td>
                            <td><span class="badge bg-primary-light">${art.Categoria || '-'}</span></td>
                            <td><small class="text-primary-light">${art.SubCategoria || '-'}</small></td>
                            <td><small class="text-primary-light">${art.Genero || '-'}</small></td>
                            <td>
                                <span class="badge ${totalVariantes > 0 ? 'bg-primary' : 'bg-secondary'}">
                                    ${totalVariantes} variante(s)
                                </span>
                            </td>
                            <td>
                                <span class="badge ${art.Activo == 1 ? 'bg-primary' : 'bg-secondary'}">
                                    ${art.Activo == 1 ? 'Activo' : 'Inactivo'}
                                </span>
                            </td>
                        </tr>`;
            });
            
            html += `</tbody></table></div>`;
            
            if (variantes.length > 0) {
                html += `<h6 class="text-primary-dark mt-4 mb-2">
                            <i class="fas fa-layer-group me-2"></i>Variantes Directas (${variantes.length})
                            <small class="text-primary-light ms-2">(Variantes que usan este precio específicamente)</small>
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
            
            html += `<div class="alert alert-primary-light mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <small class="text-primary-dark">
                            <strong>Resumen:</strong> 
                            <strong>${articulos.length} artículo(s) base</strong>
                            ${variantes.length > 0 ? ` + ${variantes.length} variante(s) directa(s)` : ''}
                        </small>
                    </div>`;
        }
        
        Swal.fire({
            title: 'Artículos Asociados',
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
        this.migrarProductosParaEliminar(idPrecioOrigen, valorOrigen, false);
    }
    
    confirmarEliminarPrecio(valor, idPrecio) {
        const url = `?c=Precio&a=eliminar&id=${idPrecio}`;
        
        fetch(`?c=Precio&a=obtenerProductos&id=${idPrecio}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.articulos?.length > 0) {
                    this.migrarYEliminarPrecio(idPrecio, valor);
                } else {
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
            })
            .catch(() => {
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
            });
    }
    
    migrarYEliminarPrecio(idPrecioOrigen, valorOrigen) {
        Swal.fire({
            title: 'Precio en uso',
            html: `El precio <strong>$${valorOrigen}</strong> está siendo usado por artículos base.<br>
                   <div class="mt-3">
                     <strong>Opciones:</strong>
                     <ul class="text-start mt-2">
                       <li><strong>Migrar y Eliminar:</strong> Migra los artículos a otro precio y elimina este</li>
                       <li><strong>Solo Migrar:</strong> Solo migra los artículos (mantiene el precio actual)</li>
                       <li><strong>Cancelar:</strong> Mantener el precio actual</li>
                     </ul>
                   </div>`,
            icon: 'info',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Migrar y Eliminar',
            denyButtonText: 'Solo Migrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#1B202D',
            denyButtonColor: '#1B202D',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            width: 600
        }).then((result) => {
            if (result.isConfirmed) {
                this.migrarProductosParaEliminar(idPrecioOrigen, valorOrigen, true);
            } else if (result.isDenied) {
                this.migrarProductosParaEliminar(idPrecioOrigen, valorOrigen, false);
            }
        });
    }
    
    migrarProductosParaEliminar(idPrecioOrigen, valorOrigen, eliminarDespues = false) {
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
                        html: 'No existen otros precios activos a los que migrar los artículos.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#1B202D'
                    });
                    return;
                }
                
                Swal.fire({
                    title: eliminarDespues ? 'Migrar y Eliminar' : 'Migrar artículos',
                    html: eliminarDespues 
                        ? `Selecciona el nuevo precio al que migrar los artículos que usan <strong>$${valorOrigen}</strong><br>
                           <small class="text-primary-light">Después de la migración, el precio actual será eliminado.</small>`
                        : `Selecciona el nuevo precio al que migrar los artículos que usan <strong>$${valorOrigen}</strong>`,
                    input: 'select',
                    inputOptions: precios,
                    inputPlaceholder: 'Selecciona un precio',
                    showCancelButton: true,
                    confirmButtonText: eliminarDespues ? 'Migrar y Eliminar' : 'Migrar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: eliminarDespues ? '#dc3545' : '#1B202D',
                    cancelButtonColor: '#6c757d',
                    showLoaderOnConfirm: true,
                    reverseButtons: true,
                    width: 600,
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
                                
                                if (eliminarDespues) {
                                    return this.eliminarPrecioDespuesDeMigrar(idPrecioOrigen, data);
                                }
                                
                                return data;
                            })
                            .catch(error => {
                                Swal.showValidationMessage(`Error: ${error.message}`);
                            });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (eliminarDespues) {
                            Swal.fire({
                                title: '¡Proceso completado!',
                                html: `✅ Los artículos han sido migrados y el precio ha sido eliminado.<br>
                                       <small class="text-primary-light">
                                       Se migraron ${result.value.articulos_migrados} artículo(s) base
                                       ${result.value.variantes_migradas > 0 ? ` + ${result.value.variantes_migradas} variante(s) directa(s)` : ''}
                                       </small>`,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#1B202D'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: '¡Migración exitosa!',
                                html: `Los artículos han sido migrados correctamente.<br>
                                       <small class="text-primary-light">
                                       Se migraron ${result.value.articulos_migrados} artículo(s) base
                                       ${result.value.variantes_migradas > 0 ? ` + ${result.value.variantes_migradas} variante(s) directa(s)` : ''}
                                       </small>`,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#1B202D'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                });
            })
            .catch(error => {
                Swal.close();
                Swal.fire('Error', 'No se pudieron cargar los precios disponibles', 'error');
                console.error('Error:', error);
            });
    }
    
    eliminarPrecioDespuesDeMigrar(idPrecio, datosMigracion) {
        return fetch(`?c=Precio&a=eliminar&id=${idPrecio}`)
            .then(() => {
                return {
                    ...datosMigracion,
                    eliminado: true
                };
            })
            .catch(error => {
                throw new Error(`Error al eliminar el precio después de migrar: ${error.message}`);
            });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('form-precio')) {
        precioFormManager = new PrecioFormManager();
    }
    
    if (document.querySelector('table.table-striped')) {
        const precioListManager = new PrecioListManager();
    }
    
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Funciones globales para compatibilidad con onclick HTML
function migrarYEliminarPrecio(idPrecio, valor) {
    const manager = new PrecioListManager();
    manager.migrarYEliminarPrecio(idPrecio, valor);
}

function confirmarEliminarPrecio(event, valor, idPrecio) {
    event.preventDefault();
    const manager = new PrecioListManager();
    manager.confirmarEliminarPrecio(valor, idPrecio);
}

function confirmarCambiarEstado(event, valor, idPrecio, estado) {
    event.preventDefault();
    const manager = new PrecioListManager();
    manager.confirmarCambiarEstado(valor, idPrecio, estado);
}

function verProductosPrecio(idPrecio, valor) {
    const manager = new PrecioListManager();
    manager.verProductosPrecio(idPrecio, valor);
}

function migrarPrecio(idPrecio, valor) {
    const manager = new PrecioListManager();
    manager.migrarPrecio(idPrecio, valor);
}