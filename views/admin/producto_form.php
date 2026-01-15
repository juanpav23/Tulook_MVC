<div class="container mt-4">
    <h2 class="fw-bold text-primary mb-4">
        <i class="fas <?= isset($articulo) ? 'fa-edit' : 'fa-plus-circle' ?>"></i> 
        <?= isset($articulo) ? 'Editar Producto Base' : 'Nuevo Producto Base' ?>
    </h2>

    <!-- Mensajes de sesión -->
    <?php if (isset($tieneVariantes) && $tieneVariantes): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Producto con variantes:</strong> La categoría, subcategoría y género están bloqueadas porque este producto ya tiene variantes creadas. 
            Solo puedes modificar el nombre, precio, imagen y estado.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Contenedor de mensajes en tiempo real -->
    <div id="msgContainer"></div>

    <form id="formProducto" action="<?= BASE_URL ?>?c=Admin&a=saveProducto" method="post" enctype="multipart/form-data" class="shadow-sm p-4 rounded border bg-light">
        <?php if (isset($articulo['ID_Articulo'])): ?>
            <input type="hidden" name="ID_Articulo" value="<?= $articulo['ID_Articulo'] ?>">
            <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($articulo['Foto'] ?? '') ?>">
        <?php endif; ?>

        <!-- Información Básica -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-info-circle"></i> Información Básica del Producto
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="N_Articulo" class="form-label fw-bold">Nombre del Producto *</label>
                            <input type="text" name="N_Articulo" id="N_Articulo" class="form-control" required
                                value="<?= htmlspecialchars($articulo['N_Articulo'] ?? '') ?>" 
                                placeholder="Ej: Camiseta Básica, Jeans Clásico...">
                        </div>

                        <div class="row">
                            <!-- Categoría -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Categoría <?= $tieneVariantes ? '<span class="text-muted">(Bloqueado)</span>' : '*'; ?></label>
                                <select name="ID_Categoria" class="form-select" required id="ID_Categoria" 
                                        <?= $tieneVariantes ? 'disabled' : '' ?>>
                                    <option value="" disabled selected>
                                        <?= $tieneVariantes ? 'Bloqueado - Producto tiene variantes' : 'Seleccionar...' ?>
                                    </option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['ID_Categoria'] ?>" 
                                            <?= (isset($articulo['ID_Categoria']) && $articulo['ID_Categoria'] == $cat['ID_Categoria']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['N_Categoria']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($tieneVariantes): ?>
                                    <input type="hidden" name="ID_Categoria" value="<?= $articulo['ID_Categoria'] ?? '' ?>">
                                <?php endif; ?>
                            </div>

                            <!-- Género -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Género <?= $tieneVariantes ? '<span class="text-muted">(Bloqueado)</span>' : '*'; ?></label>
                                <select name="ID_Genero" class="form-select" required id="ID_Genero" 
                                        <?= $tieneVariantes ? 'disabled' : '' ?>>
                                    <option value="" disabled selected>
                                        <?= $tieneVariantes ? 'Bloqueado - Producto tiene variantes' : 'Seleccionar...' ?>
                                    </option>
                                    <?php foreach ($generos as $g): ?>
                                        <option value="<?= $g['ID_Genero'] ?>" 
                                            <?= (isset($articulo['ID_Genero']) && $articulo['ID_Genero'] == $g['ID_Genero']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($g['N_Genero']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($tieneVariantes): ?>
                                    <input type="hidden" name="ID_Genero" value="<?= $articulo['ID_Genero'] ?? '' ?>">
                                <?php endif; ?>
                            </div>

                            <!-- Subcategoría -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Subcategoría <?= $tieneVariantes ? '<span class="text-muted">(Bloqueado)</span>' : '*'; ?></label>
                                <select name="ID_SubCategoria" class="form-select" required id="ID_SubCategoria" 
                                        <?= $tieneVariantes ? 'disabled' : '' ?>>
                                    <option value="" disabled selected>
                                        <?= $tieneVariantes ? 'Bloqueado - Producto tiene variantes' : 'Primero selecciona categoría y género' ?>
                                    </option>
                                    <?php if (isset($articulo['ID_SubCategoria'])): ?>
                                        <?php foreach ($subcats as $sub): ?>
                                            <option value="<?= $sub['ID_SubCategoria'] ?>" 
                                                <?= ($articulo['ID_SubCategoria'] == $sub['ID_SubCategoria']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sub['SubCategoria']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if ($tieneVariantes): ?>
                                    <input type="hidden" name="ID_SubCategoria" value="<?= $articulo['ID_SubCategoria'] ?? '' ?>">
                                <?php endif; ?>
                                <div id="loadingSubcategorias" class="spinner-border spinner-border-sm text-primary d-none mt-1" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <small class="form-text <?= $tieneVariantes ? 'text-danger fw-bold' : 'text-muted' ?>" id="subcategoriaInfo">
                                    <?= $tieneVariantes ? 'Este producto tiene variantes. No se pueden modificar categorías.' : 'Selecciona categoría y género para ver las subcategorías disponibles' ?>
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Precio Base *</label>
                                <select name="ID_Precio" class="form-select" required>
                                    <option value="" disabled selected>Seleccionar...</option>
                                    <?php foreach ($precios as $p): ?>
                                        <option value="<?= $p['ID_precio'] ?>" 
                                            <?= (isset($articulo['ID_Precio']) && $articulo['ID_Precio'] == $p['ID_precio']) ? 'selected' : '' ?>>
                                            <?= $p['ID_precio'] ?> - $<?= number_format($p['Valor'], 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="Activo" class="form-check-input" id="activo"
                                        <?= (isset($articulo['Activo']) && $articulo['Activo'] == 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="activo">Producto Activo</label>
                                </div>
                                <small class="form-text text-muted">
                                    Los colores y tallas se definen en las variantes del producto.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SISTEMA DE SUBIDA DE IMAGEN MEJORADO -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <i class="fas fa-upload"></i> Gestión de Imagen del Producto
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center bg-light h-100">
                            <strong class="d-block">Categoría</strong>
                            <span class="badge bg-primary mt-2" id="badgeCategoria">-</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center bg-light h-100">
                            <strong class="d-block">Género</strong>
                            <span class="badge bg-info mt-2" id="badgeGenero">-</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center bg-light h-100">
                            <strong class="d-block">Subcategoría</strong>
                            <span class="badge bg-warning text-dark mt-2" id="badgeSubcategoria">-</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center bg-light h-100">
                            <strong class="d-block">Estado Ruta</strong>
                            <span class="badge bg-secondary mt-2" id="badgeEstado">Incompleto</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Columna para subir imagen -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Seleccionar imagen <?= !isset($articulo) ? '*' : '' ?></label>
                            <input type="file" name="foto" class="form-control" id="imagenProducto" 
                                   accept=".jpg,.jpeg,.png,.gif" 
                                   onchange="procesarSubidaImagen()"
                                   <?= !isset($articulo) ? 'required' : '' ?>>
                            <small class="form-text text-muted">
                                Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 2MB
                                <?php if (isset($articulo)): ?>
                                    <br><strong>Deja vacío para mantener la imagen actual</strong>
                                <?php endif; ?>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del archivo *</label>
                            <input type="text" class="form-control" id="nombreArchivo" required
                                   placeholder="Ej: camiseta_basica_negra.jpg"
                                   oninput="generarRutaCompleta()"
                                   value="<?= isset($articulo['Foto']) ? basename($articulo['Foto']) : '' ?>">
                            <small class="form-text text-muted">
                                Incluye la extensión (.jpg, .png, etc.)
                            </small>
                        </div>

                        <!-- Ruta generada automáticamente -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ruta generada automáticamente</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">ImgProducto/</span>
                                <input type="text" class="form-control bg-light" id="rutaGenerada" readonly>
                            </div>
                            <input type="hidden" name="Foto" id="fotoFinal" value="<?= htmlspecialchars($articulo['Foto'] ?? '') ?>">
                            <small class="form-text text-muted">
                                Esta ruta se completará automáticamente al seleccionar categoría, género, subcategoría y nombre del archivo
                            </small>
                        </div>
                    </div>

                    <!-- Columna para previsualización -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Vista previa de la imagen</label>
                        <div class="border rounded p-3 text-center bg-light h-100" style="min-height: 200px;">
                            <?php 
                            $rutaImagenActual = '';
                            if (isset($articulo['Foto'])) {
                                $rutaImagenActual = trim($articulo['Foto'] ?? '');
                                if ($rutaImagenActual !== '') {
                                    if (!preg_match('/^https?:/i', $rutaImagenActual) && !str_starts_with($rutaImagenActual, 'ImgProducto/')) {
                                        $rutaImagenActual = 'ImgProducto/' . ltrim($rutaImagenActual, '/');
                                    }
                                    $rutaImagenActual = BASE_URL . ltrim($rutaImagenActual, '/');
                                } else {
                                    $rutaImagenActual = BASE_URL . 'assets/img/sin_imagen.png';
                                }
                            } else {
                                $rutaImagenActual = BASE_URL . 'assets/img/sin_imagen.png';
                            }
                            ?>
                            <img id="vistaPreviaImg" src="<?= htmlspecialchars($rutaImagenActual); ?>" 
                                 alt="Vista previa" 
                                 class="img-fluid rounded"
                                 style="max-height: 180px; max-width: 100%; object-fit: contain;">
                            <div class="mt-2">
                                <small class="text-muted" id="infoArchivo">
                                    <?= isset($articulo['Foto']) ? 'Imagen actual del producto' : 'Selecciona una imagen para previsualizar' ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex justify-content-between align-items-center mt-4 p-3 bg-white rounded border">
            <div>
                <button type="submit" class="btn btn-success btn-lg shadow">
                    <i class="fas fa-save"></i> <?= isset($articulo) ? 'Actualizar Producto' : 'Guardar Producto' ?>
                </button>
                <a href="<?= BASE_URL ?>?c=Admin&a=productos" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
            
            <?php if (isset($articulo['ID_Articulo'])): ?>
                <a href="<?= BASE_URL ?>?c=Admin&a=detalleProducto&id=<?= $articulo['ID_Articulo'] ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-eye"></i> Ver Detalles y Variantes
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<style>
.mini-alerta {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    margin-bottom: 0;
    border-radius: 0.25rem;
}

.mini-alerta .btn-close {
    padding: 0.5rem;
    font-size: 0.7rem;
}

.btn-close-sm {
    padding: 0.25rem !important;
    font-size: 0.6rem !important;
}

/* Estilo para opciones deshabilitadas en selects */
select option:disabled {
    color: #6c757d;
    background-color: #f8f9fa;
}

/* Estilo para selects cuando tienen valor válido */
select:valid {
    border-color: #198754;
}

select:invalid {
    border-color: #dc3545;
}

/* Estilo para la opción seleccionada por defecto */
select option[disabled][selected] {
    color: #6c757d;
    font-style: italic;
}

/* Indicador de campo obligatorio */
.form-label.fw-bold::after {
    content: " *";
    color: #dc3545;
}

/* Estilos para campos bloqueados */
select:disabled, select.bg-light {
    background-color: #f8f9fa !important;
    color: #6c757d !important;
    border-color: #dee2e6 !important;
    cursor: not-allowed !important;
    opacity: 0.7 !important;
}

/* Indicador visual para campos bloqueados */
.field-blocked {
    position: relative;
}

.field-blocked::after {
    content: "🔒";
    position: absolute;
    right: 35px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.2em;
    z-index: 100;
}

/* Para el select bloqueado */
select.field-blocked {
    padding-right: 60px;
}

/* Estilo para mensajes de bloqueo */
.text-danger.fw-bold {
    font-weight: 700 !important;
    color: #dc3545 !important;
}
</style>

<!-- SCRIPT DE VALIDACIÓN Y SISTEMA DE SUBIDA DE IMÁGENES -->
<script>
// ===== FUNCIONES PARA VALIDACIÓN EN TIEMPO REAL =====
function validarCampoEnTiempoReal(campo, mensaje) {
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

// ===== FUNCIONES PARA MANEJO DE SELECTS =====
function configurarSelects() {
    // Verificar si el producto tiene variantes
    const tieneVariantes = <?= isset($tieneVariantes) && $tieneVariantes ? 'true' : 'false' ?>;
    
    if (tieneVariantes) {
        console.log('🚫 Producto tiene variantes - Bloques activados');
        
        // IDs de selects a bloquear
        const selectsBloqueados = ['ID_Categoria', 'ID_Genero', 'ID_SubCategoria'];
        
        selectsBloqueados.forEach(id => {
            const select = document.getElementById(id);
            if (select) {
                // Deshabilitar select
                select.disabled = true;
                
                // Agregar estilos visuales claros
                select.classList.add('bg-light', 'text-muted', 'field-blocked');
                select.style.cursor = 'not-allowed';
                select.style.opacity = '0.7';
                
                // Crear un clon limpio sin event listeners
                const newSelect = select.cloneNode(true);
                select.parentNode.replaceChild(newSelect, select);
                
                // Agregar atributos de solo lectura
                newSelect.setAttribute('readonly', 'readonly');
                newSelect.setAttribute('aria-disabled', 'true');
                
                // Mensaje claro en el select
                const defaultOption = newSelect.querySelector('option[disabled][selected]');
                if (defaultOption) {
                    defaultOption.textContent = 'Bloqueado - Producto tiene variantes';
                }
            }
        });
        
        return;
    }
    
    // Configurar normalmente solo si NO tiene variantes
    const selects = document.querySelectorAll('select');
    selects.forEach(select => {
        // Agregar evento change para validación
        select.addEventListener('change', function() {
            if (this.value && this.value !== '') {
                const fieldName = this.name;
                let message = '';
                
                switch(fieldName) {
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
                        message = 'Precio base seleccionado correctamente';
                        break;
                    default:
                        message = 'Opción seleccionada correctamente';
                }
                
                validarCampoEnTiempoReal(this, message);
            }
        });
    });
}

// ===== FUNCIONES PARA CARGA DINÁMICA DE SUBCATEGORÍAS =====
function verificarYCargarSubcategorias() {
    const tieneVariantes = <?= isset($tieneVariantes) && $tieneVariantes ? 'true' : 'false' ?>;
    
    if (tieneVariantes) {
        console.log('🚫 Producto tiene variantes - Subcategorías no se cargan');
        return;
    }
    
    const categoriaSelect = document.getElementById('ID_Categoria');
    const generoSelect = document.getElementById('ID_Genero');
    const subcategoriaSelect = document.getElementById('ID_SubCategoria');
    
    const categoria = categoriaSelect.value;
    const genero = generoSelect.value;
    
    if (categoria && genero) {
        console.log('Cargando subcategorías para:', { categoria, genero });
        cargarSubcategorias(categoria, genero);
    } else {
        // Si falta categoría o género, deshabilitar subcategoría
        subcategoriaSelect.innerHTML = '<option value="" disabled selected>Selecciona categoría y género primero</option>';
        subcategoriaSelect.disabled = true;
        const infoElement = document.getElementById('subcategoriaInfo');
        if (infoElement) {
            infoElement.textContent = 'Selecciona categoría y género para ver las subcategorías disponibles';
            infoElement.className = 'form-text text-muted';
        }
        
        actualizarBadges();
        generarRutaCompleta();
    }
}

function cargarSubcategorias(idCategoria, idGenero) {
    const tieneVariantes = <?= isset($tieneVariantes) && $tieneVariantes ? 'true' : 'false' ?>;
    
    if (tieneVariantes) {
        console.log('🚫 Producto tiene variantes - No se cargan subcategorías');
        return;
    }
    
    const subcategoriaSelect = document.getElementById('ID_SubCategoria');
    const loadingElement = document.getElementById('loadingSubcategorias');
    const infoElement = document.getElementById('subcategoriaInfo');
    
    console.log('Cargando subcategorías para categoría:', idCategoria, 'y género:', idGenero);
    
    // Mostrar loading
    loadingElement.classList.remove('d-none');
    subcategoriaSelect.disabled = true;
    infoElement.textContent = 'Cargando subcategorías disponibles...';
    
    // Realizar petición AJAX
    fetch(`<?= BASE_URL ?>?c=Admin&a=getSubcategoriasByCategoria&id_categoria=${idCategoria}&id_genero=${idGenero}`)
        .then(response => {
            console.log('Respuesta recibida:', response.status);
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Subcategorías recibidas:', data);
            
            // Limpiar select y agregar opción por defecto
            subcategoriaSelect.innerHTML = '<option value="" disabled selected>Seleccionar subcategoría...</option>';
            
            // Llenar con nuevas opciones
            if (data.length > 0) {
                data.forEach(subcategoria => {
                    const option = document.createElement('option');
                    option.value = subcategoria.ID_SubCategoria;
                    option.textContent = subcategoria.SubCategoria;
                    
                    // Mantener selección actual si existe
                    const currentSubcat = '<?= $articulo['ID_SubCategoria'] ?? '' ?>';
                    if (currentSubcat && currentSubcat == subcategoria.ID_SubCategoria) {
                        option.selected = true;
                    }
                    
                    subcategoriaSelect.appendChild(option);
                });
                
                infoElement.textContent = `${data.length} subcategoría(s) disponible(s)`;
                subcategoriaSelect.disabled = false;
                
                // Si hay una selección actual, mostrar mini alerta
                if (subcategoriaSelect.value) {
                    setTimeout(() => {
                        validarCampoEnTiempoReal(subcategoriaSelect, 'Subcategoría seleccionada correctamente');
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
            
            // Actualizar interfaz
            actualizarBadges();
            generarRutaCompleta();
        })
        .catch(error => {
            console.error('Error al cargar subcategorías:', error);
            mostrarMensaje('danger', '❌ Error al cargar las subcategorías. Intenta nuevamente.');
            infoElement.textContent = 'Error al cargar subcategorías';
            
            // Restaurar opción por defecto
            subcategoriaSelect.innerHTML = '<option value="" disabled selected>Error al cargar subcategorías</option>';
        })
        .finally(() => {
            // Ocultar loading
            loadingElement.classList.add('d-none');
        });
}

// ===== FUNCIONES PARA GENERAR RUTAS AUTOMÁTICAS =====
function generarRutaCompleta() {
    const categoria = document.getElementById('badgeCategoria').textContent;
    const genero = document.getElementById('badgeGenero').textContent;
    const subcategoria = document.getElementById('badgeSubcategoria').textContent;
    const nombreArchivo = document.getElementById('nombreArchivo').value.trim();
    
    console.log('Generando ruta:', { categoria, genero, subcategoria, nombreArchivo });
    
    // Verificar si todos los campos están completos
    const rutaCompleta = categoria !== '-' && genero !== '-' && subcategoria !== '-' && subcategoria !== 'Selecciona categoría y género primero' && nombreArchivo !== '';
    
    if (rutaCompleta) {
        const ruta = `${categoria}/${genero}/${subcategoria}/${nombreArchivo}`;
        document.getElementById('rutaGenerada').value = ruta;
        document.getElementById('fotoFinal').value = `ImgProducto/${ruta}`;
        document.getElementById('badgeEstado').textContent = 'Completo';
        document.getElementById('badgeEstado').className = 'badge bg-success mt-2';
        console.log('✅ Ruta generada:', ruta);
    } else {
        document.getElementById('rutaGenerada').value = '';
        document.getElementById('fotoFinal').value = '';
        document.getElementById('badgeEstado').textContent = 'Incompleto';
        document.getElementById('badgeEstado').className = 'badge bg-secondary mt-2';
        console.log('❌ Ruta incompleta');
    }
}

// ===== FUNCIONES PARA ACTUALIZAR BADGES =====
function actualizarBadges() {
    const categoriaSelect = document.getElementById('ID_Categoria');
    const generoSelect = document.getElementById('ID_Genero');
    const subcategoriaSelect = document.getElementById('ID_SubCategoria');
    
    // Mapeo de IDs a nombres para categorías
    const categoriasMap = {
        <?php foreach ($categorias as $cat): ?>
            '<?= $cat['ID_Categoria'] ?>': '<?= htmlspecialchars($cat['N_Categoria']) ?>',
        <?php endforeach; ?>
    };
    
    // Mapeo de IDs a nombres para géneros
    const generosMap = {
        <?php foreach ($generos as $gen): ?>
            '<?= $gen['ID_Genero'] ?>': '<?= htmlspecialchars($gen['N_Genero']) ?>',
        <?php endforeach; ?>
    };
    
    // Obtener nombre de subcategoría seleccionada
    let subcategoriaNombre = '-';
    if (subcategoriaSelect.value && subcategoriaSelect.options[subcategoriaSelect.selectedIndex]) {
        subcategoriaNombre = subcategoriaSelect.options[subcategoriaSelect.selectedIndex].textContent;
    }
    
    // Actualizar badges
    const categoriaNombre = categoriasMap[categoriaSelect.value] || '-';
    const generoNombre = generosMap[generoSelect.value] || '-';
    
    document.getElementById('badgeCategoria').textContent = categoriaNombre;
    document.getElementById('badgeGenero').textContent = generoNombre;
    document.getElementById('badgeSubcategoria').textContent = subcategoriaNombre;
    
    console.log('Badges actualizados:', { categoriaNombre, generoNombre, subcategoriaNombre });
}

// ===== FUNCIONES PARA SUBIDA DE IMÁGENES =====
function procesarSubidaImagen() {
    const fileInput = document.getElementById('imagenProducto');
    const nombreArchivoInput = document.getElementById('nombreArchivo');
    const vistaPreviaImg = document.getElementById('vistaPreviaImg');
    const infoArchivo = document.getElementById('infoArchivo');
    
    console.log('Procesando subida de imagen...');
    
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        console.log('Archivo seleccionado:', file.name, file.size, file.type);
        
        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            mostrarMensaje('danger', '❌ Error: Solo se permiten archivos JPG, JPEG, PNG o GIF.');
            fileInput.value = '';
            return;
        }
        
        // Validar tamaño (2MB máximo)
        if (file.size > 2 * 1024 * 1024) {
            mostrarMensaje('danger', '❌ Error: La imagen no puede ser mayor a 2MB.');
            fileInput.value = '';
            return;
        }
        
        // Auto-completar el nombre del archivo si está vacío
        if (!nombreArchivoInput.value.trim()) {
            const nombreBase = file.name.replace(/\.[^/.]+$/, "");
            const extension = file.name.split('.').pop().toLowerCase();
            nombreArchivoInput.value = `${nombreBase}.${extension}`;
            console.log('Nombre auto-completado:', nombreArchivoInput.value);
        }
        
        // Mostrar vista previa
        const reader = new FileReader();
        reader.onload = function(e) {
            console.log('✅ Imagen cargada para previsualización');
            vistaPreviaImg.src = e.target.result;
            infoArchivo.textContent = `Archivo: ${file.name} (${(file.size/1024).toFixed(1)} KB)`;
            infoArchivo.className = 'text-success';
            
            // Forzar redibujado de la imagen
            vistaPreviaImg.style.display = 'none';
            vistaPreviaImg.offsetHeight; // Trigger reflow
            vistaPreviaImg.style.display = 'block';
        };
        
        reader.onerror = function(error) {
            console.error('❌ Error al leer archivo:', error);
            mostrarMensaje('danger', '❌ Error al cargar la imagen para previsualización.');
        };
        
        reader.readAsDataURL(file);
        
        // Generar ruta automáticamente
        generarRutaCompleta();
        
        mostrarMensaje('success', '✅ Imagen cargada correctamente. Verifica la ruta generada.');
    } else {
        console.log('❌ No se seleccionó archivo');
        // Restaurar imagen por defecto si no hay archivo
        vistaPreviaImg.src = '<?= BASE_URL ?>assets/img/sin_imagen.png';
        infoArchivo.textContent = 'Selecciona una imagen para previsualizar';
        infoArchivo.className = 'text-muted';
    }
}

function mostrarMensaje(tipo, texto) {
    const contenedor = document.getElementById('msgContainer');
    contenedor.innerHTML = `
        <div class="alert alert-${tipo} alert-dismissible fade show mt-2 shadow-sm">
            <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
            ${texto}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    console.log('Mensaje:', texto);
}

// ===== INICIALIZACIÓN PRINCIPAL =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Script cargado correctamente');
    
    const tieneVariantes = <?= isset($tieneVariantes) && $tieneVariantes ? 'true' : 'false' ?>;
    
    // Cargar datos iniciales si estamos editando
    <?php if (isset($articulo)): ?>
    console.log('📦 Cargando datos del producto para edición');
    
    // Solo cargar subcategorías si NO tiene variantes
    if (!tieneVariantes) {
        const categoriaInicial = document.getElementById('ID_Categoria').value;
        const generoInicial = document.getElementById('ID_Genero').value;
        
        if (categoriaInicial && generoInicial) {
            console.log('🔄 Cargando subcategorías iniciales...');
            setTimeout(() => {
                cargarSubcategorias(categoriaInicial, generoInicial);
            }, 500);
        }
    } else {
        console.log('🚫 Producto con variantes - Subcategorías no se recargan');
    }
    <?php endif; ?>
    
    // Configurar selects (incluye bloqueo si tiene variantes)
    configurarSelects();
    
    // Solo configurar event listeners si NO tiene variantes
    if (!tieneVariantes) {
        // Escuchar cambios en categoría y género para cargar subcategorías
        document.getElementById('ID_Categoria').addEventListener('change', function() {
            console.log('Categoría cambiada:', this.value);
            verificarYCargarSubcategorias();
            
            // Validación en tiempo real
            if (this.value) {
                validarCampoEnTiempoReal(this, 'Categoría seleccionada correctamente');
            }
        });
        
        document.getElementById('ID_Genero').addEventListener('change', function() {
            console.log('Género cambiado:', this.value);
            verificarYCargarSubcategorias();
            
            // Validación en tiempo real
            if (this.value) {
                validarCampoEnTiempoReal(this, 'Género seleccionado correctamente');
            }
        });
        
        document.getElementById('ID_SubCategoria').addEventListener('change', function() {
            console.log('Subcategoría cambiada:', this.value);
            actualizarBadges();
            generarRutaCompleta();
            
            // Validación en tiempo real
            if (this.value) {
                validarCampoEnTiempoReal(this, 'Subcategoría seleccionada correctamente');
            }
        });
    } else {
        console.log('🚫 Event listeners desactivados - Producto tiene variantes');
        
        // Forzar badges con valores actuales
        actualizarBadges();
        generarRutaCompleta();
    }
    
    // Escuchar cambios en otros selects importantes
    document.querySelector('select[name="ID_Precio"]').addEventListener('change', function() {
        if (this.value) {
            validarCampoEnTiempoReal(this, 'Precio base seleccionado correctamente');
        }
    });
    
    // Escuchar cambios en el nombre del archivo
    document.getElementById('nombreArchivo').addEventListener('input', function() {
        console.log('Nombre archivo cambiado:', this.value);
        generarRutaCompleta();
    });

    // Escuchar cambios en el input de archivo
    document.getElementById('imagenProducto').addEventListener('change', function() {
        console.log('Archivo seleccionado:', this.files[0]?.name);
        procesarSubidaImagen();
    });
    
    // Mostrar mini alertas para valores ya seleccionados al cargar la página
    setTimeout(() => {
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
                    validarCampoEnTiempoReal(select, message);
                }
            }
        });
        
        // Validar nombre si ya existe
        const nombreInput = document.getElementById('N_Articulo');
        if (nombreInput.value.trim()) {
            validarCampoEnTiempoReal(nombreInput, 'Nombre del producto válido');
        }
        
        // Validar nombre de archivo si ya existe
        const nombreArchivoInput = document.getElementById('nombreArchivo');
        if (nombreArchivoInput.value.trim()) {
            validarCampoEnTiempoReal(nombreArchivoInput, 'Nombre de archivo válido');
        }
    }, 500);
});

// ===== VALIDACIÓN DEL FORMULARIO AL ENVIAR =====
document.getElementById('formProducto').onsubmit = function(e) {
    const tieneVariantes = <?= isset($tieneVariantes) && $tieneVariantes ? 'true' : 'false' ?>;
    
    // Si tiene variantes, validar que no se hayan modificado los campos bloqueados
    if (tieneVariantes) {
        const categoriaOriginal = '<?= $articulo['ID_Categoria'] ?? '' ?>';
        const generoOriginal = '<?= $articulo['ID_Genero'] ?? '' ?>';
        const subcategoriaOriginal = '<?= $articulo['ID_SubCategoria'] ?? '' ?>';
        
        const categoriaActual = document.getElementById('ID_Categoria').value;
        const generoActual = document.getElementById('ID_Genero').value;
        const subcategoriaActual = document.getElementById('ID_SubCategoria').value;
        
        if (categoriaOriginal !== categoriaActual || 
            generoOriginal !== generoActual || 
            subcategoriaOriginal !== subcategoriaActual) {
            
            e.preventDefault();
            mostrarMensaje('danger', '❌ No se pueden modificar la categoría, género o subcategoría porque este producto tiene variantes creadas.');
            return false;
        }
    }
    
    const categoria = document.getElementById('ID_Categoria').value;
    const genero = document.getElementById('ID_Genero').value;
    const subcategoria = document.getElementById('ID_SubCategoria').value;
    const precio = document.querySelector('select[name="ID_Precio"]').value;
    const nombre = document.getElementById('N_Articulo').value;
    const imagenInput = document.getElementById('imagenProducto');
    const fotoFinal = document.getElementById('fotoFinal').value;
    const nombreArchivo = document.getElementById('nombreArchivo').value;
    
    console.log('Validando formulario...', { 
        categoria, genero, subcategoria, precio, nombre, 
        fotoFinal, nombreArchivo 
    });
    
    // Limpiar mensajes anteriores
    document.getElementById('msgContainer').innerHTML = '';
    
    // Validaciones básicas
    if (!categoria) {
        mostrarMensaje('danger', '⚠️ Debes seleccionar una categoría.');
        e.preventDefault();
        return false;
    }
    
    if (!genero) {
        mostrarMensaje('danger', '⚠️ Debes seleccionar un género.');
        e.preventDefault();
        return false;
    }
    
    if (!subcategoria) {
        mostrarMensaje('danger', '⚠️ Debes seleccionar una subcategoría.');
        e.preventDefault();
        return false;
    }
    
    if (!precio) {
        mostrarMensaje('danger', '⚠️ Debes seleccionar un precio base.');
        e.preventDefault();
        return false;
    }
    
    if (!nombre.trim()) {
        mostrarMensaje('danger', '⚠️ El nombre del producto es obligatorio.');
        e.preventDefault();
        return false;
    }
    
    if (!nombreArchivo.trim()) {
        mostrarMensaje('danger', '⚠️ El nombre del archivo es obligatorio.');
        e.preventDefault();
        return false;
    }
    
    // Validar que la ruta esté completa
    if (!fotoFinal) {
        mostrarMensaje('danger', '⚠️ Completa la categoría, género, subcategoría y nombre del archivo para generar la ruta de la imagen.');
        e.preventDefault();
        return false;
    }
    
    // Validar imagen (solo para productos nuevos)
    const esProductoNuevo = <?= empty($articulo['ID_Articulo']) ? 'true' : 'false' ?>;
    if (esProductoNuevo && (!imagenInput.files || imagenInput.files.length === 0)) {
        mostrarMensaje('danger', '⚠️ Debes seleccionar una imagen para el producto.');
        e.preventDefault();
        return false;
    }
    
    mostrarMensaje('success', '✅ Validación exitosa. Guardando producto...');
    return true;
};
</script>