// FormProductos.js - Funcionalidades para el formulario de productos

class ProductoFormManager {
    constructor(config) {
        // Configuración pasada desde PHP
        this.tieneVariantes = config.tieneVariantes || false;
        this.baseUrl = config.baseUrl || '';
        this.articuloId = config.articuloId || null;
        this.categoriaInicial = config.categoriaInicial || null;
        this.generoInicial = config.generoInicial || null;
        this.subcategoriaInicial = config.subcategoriaInicial || null;
        
        // ⭐⭐ CORRECCIÓN CRÍTICA ⭐⭐
        // ANTES: this.esProductoNuevo = config.esProductoNuevo || true; // ¡ERROR!
        // DESPUÉS:
        this.esProductoNuevo = config.esProductoNuevo !== undefined ? config.esProductoNuevo : true;
        
        this.rutaImagenActual = config.rutaImagenActual || '';
        
        // Para rastrear cambios en categorías
        this.hayCambiosCategorias = false;
        this.imagenObligatoria = this.esProductoNuevo; // Para nuevos productos siempre es obligatoria
        
        this.init();
    }

    init() {
        this.initEventListeners();
        this.configurarSelects();
        this.actualizarBadgesIniciales();
        this.initImagePreview();
        
        if (!this.tieneVariantes) {
            this.verificarYCargarSubcategorias();
        }
    }

    // Inicializar event listeners
    initEventListeners() {
        const form = document.getElementById('formProducto');
        const imagenProducto = document.getElementById('imagenProducto');
        const categoriaSelect = document.getElementById('ID_Categoria');
        const generoSelect = document.getElementById('ID_Genero');
        const subcategoriaSelect = document.getElementById('ID_SubCategoria');
        const precioSelect = document.getElementById('ID_Precio');
        const nombreInput = document.getElementById('N_Articulo');

        // Solo agregar event listeners si no tiene variantes
        if (!this.tieneVariantes) {
            if (categoriaSelect) {
                categoriaSelect.addEventListener('change', () => {
                    this.verificarYCargarSubcategorias();
                    this.validarCampoEnTiempoReal(categoriaSelect, 'Categoría seleccionada correctamente');
                    this.verificarCambiosCategorias();
                });
            }

            if (generoSelect) {
                generoSelect.addEventListener('change', () => {
                    this.verificarYCargarSubcategorias();
                    this.validarCampoEnTiempoReal(generoSelect, 'Género seleccionado correctamente');
                    this.verificarCambiosCategorias();
                });
            }

            if (subcategoriaSelect) {
                subcategoriaSelect.addEventListener('change', () => {
                    this.actualizarBadges();
                    this.generarRutaCompleta();
                    this.validarCampoEnTiempoReal(subcategoriaSelect, 'Subcategoría seleccionada correctamente');
                    this.verificarCambiosCategorias();
                });
            }
        }

        if (precioSelect) {
            precioSelect.addEventListener('change', () => {
                if (precioSelect.value) {
                    this.validarCampoEnTiempoReal(precioSelect, 'Precio base seleccionado correctamente');
                }
            });
        }

        if (nombreInput) {
            nombreInput.addEventListener('input', () => {
                if (nombreInput.value.trim()) {
                    this.validarCampoEnTiempoReal(nombreInput, 'Nombre del producto válido');
                }
            });
        }

        if (imagenProducto) {
            imagenProducto.addEventListener('change', () => this.procesarSubidaImagen());
        }

        if (form) {
            form.addEventListener('submit', (e) => this.validateForm(e));
        }

        // Validar campos iniciales
        setTimeout(() => this.validarCamposIniciales(), 500);
    }

    // Configurar selects (incluye bloqueo si tiene variantes)
    configurarSelects() {
        if (this.tieneVariantes) {
            console.log('🚫 Producto tiene variantes - Campos bloqueados');
            const selectsBloqueados = ['ID_Categoria', 'ID_Genero', 'ID_SubCategoria'];
            
            selectsBloqueados.forEach(id => {
                const select = document.getElementById(id);
                if (select) {
                    select.disabled = true;
                    select.classList.add('bg-light', 'text-muted', 'field-blocked');
                    select.style.cursor = 'not-allowed';
                    select.style.opacity = '0.7';
                    select.setAttribute('aria-disabled', 'true');
                }
            });
        }
    }

    // Verificar si hay cambios en categorías
    verificarCambiosCategorias() {
        if (this.tieneVariantes || this.esProductoNuevo) return;
        
        const categoriaActual = document.getElementById('ID_Categoria')?.value;
        const generoActual = document.getElementById('ID_Genero')?.value;
        const subcategoriaActual = document.getElementById('ID_SubCategoria')?.value;
        
        // Comparar con valores iniciales
        this.hayCambiosCategorias = 
            (this.categoriaInicial && categoriaActual != this.categoriaInicial) ||
            (this.generoInicial && generoActual != this.generoInicial) ||
            (this.subcategoriaInicial && subcategoriaActual != this.subcategoriaInicial);
        
        console.log('Verificando cambios en categorías:', {
            categoriaInicial: this.categoriaInicial,
            categoriaActual,
            generoInicial: this.generoInicial,
            generoActual,
            subcategoriaInicial: this.subcategoriaInicial,
            subcategoriaActual,
            hayCambios: this.hayCambiosCategorias
        });
        
        // Si hay cambios, forzar que se requiera nueva imagen
        if (this.hayCambiosCategorias) {
            this.imagenObligatoria = true;
            this.mostrarAdvertenciaCambioCategorias();
            this.limpiarImagenActual();
        } else {
            this.imagenObligatoria = false;
            this.ocultarAdvertenciaCambioCategorias();
        }
    }

    // Mostrar advertencia cuando hay cambios en categorías
    mostrarAdvertenciaCambioCategorias() {
        const msgContainer = document.getElementById('msgContainer');
        if (!msgContainer) return;
        
        // Remover advertencias anteriores
        this.ocultarAdvertenciaCambioCategorias();
        
        // Mostrar nueva advertencia
        const advertencia = document.createElement('div');
        advertencia.id = 'advertenciaCambioCategorias';
        advertencia.className = 'alert alert-warning alert-dismissible fade show mt-3';
        advertencia.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¡Atención!</strong> Has cambiado la categoría, género o subcategoría. 
            <strong>Debes seleccionar una nueva imagen</strong> porque la ruta de almacenamiento cambiará.
            <br><small>La imagen actual ha sido eliminada del formulario.</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="window.productoFormManager.ocultarAdvertenciaCambioCategorias()"></button>
        `;
        msgContainer.appendChild(advertencia);
    }

    // Ocultar advertencia
    ocultarAdvertenciaCambioCategorias() {
        const advertencia = document.getElementById('advertenciaCambioCategorias');
        if (advertencia) {
            advertencia.remove();
        }
    }

    // Limpiar imagen actual cuando hay cambios en categorías
    limpiarImagenActual() {
        const imagenInput = document.getElementById('imagenProducto');
        const vistaPreviaImg = document.getElementById('vistaPreviaImg');
        const infoArchivo = document.getElementById('infoArchivo');
        const rutaGenerada = document.getElementById('rutaGenerada');
        const fotoFinal = document.getElementById('fotoFinal');
        
        // Limpiar input file
        if (imagenInput) {
            imagenInput.value = '';
        }
        
        // Restablecer vista previa a imagen por defecto
        if (vistaPreviaImg) {
            vistaPreviaImg.src = this.baseUrl + 'assets/img/sin_imagen.png';
        }
        
        // Actualizar texto informativo
        if (infoArchivo) {
            infoArchivo.textContent = 'Debes seleccionar una nueva imagen (categorías cambiadas)';
            infoArchivo.className = 'text-danger fw-bold';
        }
        
        // Limpiar ruta generada
        if (rutaGenerada) {
            rutaGenerada.value = '';
        }
        
        // Limpiar campo oculto
        if (fotoFinal) {
            fotoFinal.value = '';
        }
    }

    // Validar campos en tiempo real
    validarCampoEnTiempoReal(campo, mensaje) {
        const valor = campo.value.trim();
        const formGroup = campo.closest('.mb-3') || campo.closest('.form-select');
        
        // Remover alertas anteriores
        const alertaAnterior = formGroup.querySelector('.mini-alerta');
        if (alertaAnterior) {
            alertaAnterior.remove();
        }
        
        // Si el campo tiene valor, mostrar mini alerta
        if (valor && valor !== '') {
            const miniAlerta = document.createElement('div');
            miniAlerta.className = 'mini-alerta alert alert-success alert-dismissible fade show mt-1 py-1';
            miniAlerta.innerHTML = `
                <i class="fas fa-check-circle me-1"></i>
                ${mensaje}
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
            `;
            formGroup.appendChild(miniAlerta);
            
            // Auto-ocultar después de 3 segundos
            setTimeout(() => {
                if (miniAlerta.parentNode) {
                    miniAlerta.remove();
                }
            }, 3000);
        }
    }

    // Verificar y cargar subcategorías
    verificarYCargarSubcategorias() {
        if (this.tieneVariantes) return;
        
        const categoriaSelect = document.getElementById('ID_Categoria');
        const generoSelect = document.getElementById('ID_Genero');
        const categoria = categoriaSelect?.value;
        const genero = generoSelect?.value;
        
        if (categoria && genero) {
            this.cargarSubcategorias(categoria, genero);
        } else {
            const subcategoriaSelect = document.getElementById('ID_SubCategoria');
            if (subcategoriaSelect) {
                subcategoriaSelect.innerHTML = '<option value="" disabled selected>Selecciona categoría y género primero</option>';
                subcategoriaSelect.disabled = true;
            }
            this.actualizarBadges();
            this.generarRutaCompleta();
        }
    }

    // Cargar subcategorías via AJAX
    async cargarSubcategorias(idCategoria, idGenero) {
        if (this.tieneVariantes) return;
        
        const subcategoriaSelect = document.getElementById('ID_SubCategoria');
        const loadingElement = document.getElementById('loadingSubcategorias');
        const infoElement = document.getElementById('subcategoriaInfo');
        
        if (!subcategoriaSelect || !loadingElement || !infoElement) return;
        
        try {
            loadingElement.classList.remove('d-none');
            subcategoriaSelect.disabled = true;
            infoElement.textContent = 'Cargando subcategorías disponibles...';
            
            const response = await fetch(`${this.baseUrl}?c=Admin&a=getSubcategoriasByCategoria&id_categoria=${idCategoria}&id_genero=${idGenero}`);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Limpiar y llenar select
            subcategoriaSelect.innerHTML = '<option value="" disabled selected>Seleccionar subcategoría...</option>';
            
            if (data.length > 0) {
                data.forEach(subcategoria => {
                    const option = document.createElement('option');
                    option.value = subcategoria.ID_SubCategoria;
                    option.textContent = subcategoria.SubCategoria;
                    
                    // Seleccionar si coincide con el valor inicial
                    if (this.subcategoriaInicial && this.subcategoriaInicial == subcategoria.ID_SubCategoria) {
                        option.selected = true;
                    }
                    
                    subcategoriaSelect.appendChild(option);
                });
                
                infoElement.textContent = `${data.length} subcategoría(s) disponible(s)`;
                subcategoriaSelect.disabled = false;
                
                // Si hay una selección, validarla
                if (subcategoriaSelect.value) {
                    setTimeout(() => {
                        this.validarCampoEnTiempoReal(subcategoriaSelect, 'Subcategoría seleccionada correctamente');
                    }, 100);
                }
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.disabled = true;
                option.selected = true;
                option.textContent = 'No hay subcategorías disponibles para esta combinación';
                subcategoriaSelect.appendChild(option);
                infoElement.textContent = 'No hay subcategorías disponibles para esta combinación de categoría y género';
            }
            
            this.actualizarBadges();
            this.generarRutaCompleta();
        } catch (error) {
            console.error('Error al cargar subcategorías:', error);
            this.mostrarMensaje('danger', '❌ Error al cargar las subcategorías. Intenta nuevamente.');
            infoElement.textContent = 'Error al cargar subcategorías';
            subcategoriaSelect.innerHTML = '<option value="" disabled selected>Error al cargar subcategorías</option>';
        } finally {
            loadingElement.classList.add('d-none');
        }
    }

    // Actualizar badges
    actualizarBadges() {
        const categoriaSelect = document.getElementById('ID_Categoria');
        const generoSelect = document.getElementById('ID_Genero');
        const subcategoriaSelect = document.getElementById('ID_SubCategoria');
        
        const categoriaNombre = categoriaSelect?.selectedOptions[0]?.textContent || '-';
        const generoNombre = generoSelect?.selectedOptions[0]?.textContent || '-';
        const subcategoriaNombre = subcategoriaSelect?.selectedOptions[0]?.textContent || '-';
        
        document.getElementById('badgeCategoria').textContent = categoriaNombre;
        document.getElementById('badgeGenero').textContent = generoNombre;
        document.getElementById('badgeSubcategoria').textContent = subcategoriaNombre;
    }

    // Actualizar badges iniciales
    actualizarBadgesIniciales() {
        this.actualizarBadges();
        this.generarRutaCompleta();
    }

    // Generar ruta completa - CORREGIDO (MOSTRAR RUTA COMPLETA)
    generarRutaCompleta() {
        const categoria = document.getElementById('badgeCategoria')?.textContent || '-';
        const genero = document.getElementById('badgeGenero')?.textContent || '-';
        const subcategoria = document.getElementById('badgeSubcategoria')?.textContent || '-';
        
        const rutaCompleta = categoria !== '-' && genero !== '-' && subcategoria !== '-' && 
                           subcategoria !== 'Seleccionar subcategoría...' &&
                           subcategoria !== 'Selecciona categoría y género primero';
        
        if (rutaCompleta) {
            const ruta = `${categoria}/${genero}/${subcategoria}/`;
            const rutaCompletaGuardar = `ImgProducto/${ruta}`;
            
            // Mostrar ruta completa en el input
            const inputRuta = document.getElementById('rutaGenerada');
            inputRuta.value = rutaCompletaGuardar;
            inputRuta.setAttribute('title', rutaCompletaGuardar);
            
            // Actualizar campo oculto
            document.getElementById('fotoFinal').value = rutaCompletaGuardar;
            
            document.getElementById('badgeEstado').textContent = 'Completo';
            document.getElementById('badgeEstado').className = 'badge bg-success mt-2';
        } else {
            document.getElementById('rutaGenerada').value = '';
            document.getElementById('fotoFinal').value = '';
            document.getElementById('badgeEstado').textContent = 'Incompleto';
            document.getElementById('badgeEstado').className = 'badge bg-secondary mt-2';
        }
    }

    // Inicializar vista previa de imagen
    initImagePreview() {
        const imagenProducto = document.getElementById('imagenProducto');
        if (imagenProducto) {
            imagenProducto.addEventListener('change', (e) => this.procesarSubidaImagen(e));
        }
    }

    // Procesar subida de imagen
    procesarSubidaImagen(event) {
        const fileInput = document.getElementById('imagenProducto');
        const vistaPreviaImg = document.getElementById('vistaPreviaImg');
        const infoArchivo = document.getElementById('infoArchivo');
        
        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            
            // Validar tipo de archivo
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                this.mostrarMensaje('danger', '❌ Error: Solo se permiten archivos JPG, JPEG, PNG, GIF o WebP.');
                fileInput.value = '';
                return;
            }
            
            // Validar tamaño (15MB máximo)
            if (file.size > 15 * 1024 * 1024) {
                this.mostrarMensaje('danger', '❌ Error: La imagen no puede ser mayor a 15MB.');
                fileInput.value = '';
                return;
            }
            
            // Mostrar vista previa
            const reader = new FileReader();
            reader.onload = (e) => {
                vistaPreviaImg.src = e.target.result;
                infoArchivo.textContent = `Archivo: ${file.name} (${(file.size/1024).toFixed(1)} KB)`;
                infoArchivo.className = 'text-success';
                
                // Si hay cambios en categorías, actualizar el estado
                if (this.hayCambiosCategorias) {
                    infoArchivo.innerHTML += '<br><span class="text-success">✓ Nueva imagen seleccionada para la nueva ruta</span>';
                }
            };
            reader.onerror = () => {
                this.mostrarMensaje('danger', '❌ Error al cargar la imagen para previsualización.');
            };
            reader.readAsDataURL(file);
            
            this.mostrarMensaje('success', '✅ Imagen cargada correctamente.');
        }
    }

    // Validar campos iniciales
    validarCamposIniciales() {
        const selects = document.querySelectorAll('select');
        selects.forEach(select => {
            if (select.value && select.value !== '') {
                let message = '';
                switch(select.name) {
                    case 'ID_Categoria':
                        message = 'Categoría seleccionada correctamente';
                        break;
                    case 'ID_Genero':
                        message = 'Género seleccionado correctamente';
                        break;
                    case 'ID_SubCategoria':
                        message = 'Subcategoría seleccionada correctamente';
                        break;
                    case 'ID_Precio':
                        message = 'Precio base seleccionada correctamente';
                        break;
                }
                if (message) {
                    this.validarCampoEnTiempoReal(select, message);
                }
            }
        });
        
        const nombreInput = document.getElementById('N_Articulo');
        if (nombreInput && nombreInput.value.trim()) {
            this.validarCampoEnTiempoReal(nombreInput, 'Nombre del producto válido');
        }
    }

    // Validar formulario - CORREGIDO (LÓGICA MEJORADA)
    validateForm(e) {
        // Limpiar mensajes anteriores
        const msgContainer = document.getElementById('msgContainer');
        if (msgContainer) {
            msgContainer.innerHTML = '';
        }
        
        const categoria = document.getElementById('ID_Categoria')?.value;
        const genero = document.getElementById('ID_Genero')?.value;
        const subcategoria = document.getElementById('ID_SubCategoria')?.value;
        const precio = document.getElementById('ID_Precio')?.value;
        const nombre = document.getElementById('N_Articulo')?.value;
        const imagenInput = document.getElementById('imagenProducto');
        
        // Validaciones básicas
        if (!categoria) {
            this.mostrarMensaje('danger', '⚠️ Debes seleccionar una categoría.');
            e.preventDefault();
            return false;
        }
        
        if (!genero) {
            this.mostrarMensaje('danger', '⚠️ Debes seleccionar un género.');
            e.preventDefault();
            return false;
        }
        
        if (!subcategoria) {
            this.mostrarMensaje('danger', '⚠️ Debes seleccionar una subcategoría.');
            e.preventDefault();
            return false;
        }
        
        if (!precio) {
            this.mostrarMensaje('danger', '⚠️ Debes seleccionar un precio base.');
            e.preventDefault();
            return false;
        }
        
        if (!nombre.trim()) {
            this.mostrarMensaje('danger', '⚠️ El nombre del producto es obligatorio.');
            e.preventDefault();
            return false;
        }
        
        // Verificar si la imagen es obligatoria
        // Caso 1: Producto nuevo -> SIEMPRE obligatoria
        // Caso 2: Producto existente sin variantes con cambios en categorías -> obligatoria
        // Caso 3: Producto existente sin cambios -> NO obligatoria
        // Caso 4: Producto con variantes -> NO obligatoria (categorías bloqueadas)
        
        const tieneImagen = imagenInput.files && imagenInput.files.length > 0;
        
        if (this.esProductoNuevo) {
            // Caso 1: Producto nuevo
            if (!tieneImagen) {
                this.mostrarMensaje('danger', '⚠️ Debes seleccionar una imagen para el producto nuevo.');
                e.preventDefault();
                return false;
            }
        } else if (!this.tieneVariantes && this.hayCambiosCategorias) {
            // Caso 2: Producto existente sin variantes y con cambios en categorías
            if (!tieneImagen) {
                this.mostrarMensaje('danger', '⚠️ Has cambiado la categoría, género o subcategoría. Debes seleccionar una nueva imagen.');
                e.preventDefault();
                return false;
            }
        }
        
        // Validar imagen si se subió una
        if (tieneImagen) {
            const file = imagenInput.files[0];
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!allowedTypes.includes(file.type)) {
                this.mostrarMensaje('danger', '❌ Error: Solo se permiten archivos JPG, JPEG, PNG, GIF o WebP.');
                e.preventDefault();
                return false;
            }
            
            if (file.size > 15 * 1024 * 1024) {
                this.mostrarMensaje('danger', '❌ Error: La imagen no puede ser mayor a 15MB.');
                e.preventDefault();
                return false;
            }
        }
        
        this.mostrarMensaje('success', '✅ Validación exitosa. Guardando producto...');
        return true;
    }

    // Mostrar mensaje
    mostrarMensaje(tipo, texto) {
        const contenedor = document.getElementById('msgContainer');
        if (contenedor) {
            contenedor.innerHTML = `
                <div class="alert alert-${tipo} alert-dismissible fade show mt-2 shadow-sm">
                    <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
                    ${texto}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
        }
    }
}

// Inicializar automáticamente cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si el formulario existe en la página
    if (document.getElementById('formProducto') && typeof FORM_CONFIG !== 'undefined') {
        // Inicializar el gestor del formulario con la configuración de PHP
        window.productoFormManager = new ProductoFormManager(FORM_CONFIG);
    }
});