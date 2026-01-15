<div class="container mt-4">
    <!-- Mensajes de sesión PHP - CORREGIDO -->
    <?php 
    // Mostrar mensajes de sesión si existen
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Mostrar mensajes de CRUD
    if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-<?= $_SESSION['msg_type'] ?? 'info' ?> alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="fas <?= ($_SESSION['msg_type'] ?? '') == 'success' ? 'fa-check-circle' : 
                           (($_SESSION['msg_type'] ?? '') == 'danger' ? 'fa-exclamation-triangle' : 
                           (($_SESSION['msg_type'] ?? '') == 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle')) ?> me-2"></i>
            <?= $_SESSION['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="clearSessionMessages()"></button>
        </div>
        <?php 
        // Limpiar mensajes después de mostrarlos
        unset($_SESSION['msg']);
        unset($_SESSION['msg_type']);
        ?>
    <?php endif; ?>

    <h2 class="fw-bold mb-4" style="color: var(--primary-dark);">
        <i class="fas <?= isset($articulo) ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i> 
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
                                <select name="ID_Precio" class="form-select" required id="ID_Precio">
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
                            <label class="form-label fw-bold">
                                <?= isset($articulo) ? 'Actualizar imagen (opcional)' : 'Seleccionar imagen *' ?>
                            </label>
                            <input type="file" name="foto" class="form-control" id="imagenProducto" 
                                accept=".jpg,.jpeg,.png,.gif,.webp" 
                                <?= !isset($articulo) ? 'required' : '' ?>>
                            <small class="form-text text-muted">
                                Formatos permitidos: JPG, JPEG, PNG, GIF, WebP. Tamaño máximo: 15MB
                                <?php if (isset($articulo)): ?>
                                    <br><strong>Deja vacío para mantener la imagen actual</strong>
                                <?php endif; ?>
                            </small>
                        </div>

                        <!-- Ruta generada automáticamente - MEJORADO -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ruta de almacenamiento</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Ruta completa:</span>
                                <input type="text" class="form-control bg-light" id="rutaGenerada" readonly
                                    style="font-family: monospace; font-size: 0.9rem;" 
                                    title="Ruta completa donde se almacenará la imagen">
                            </div>
                            <input type="hidden" name="Foto" id="fotoFinal" value="<?= htmlspecialchars($articulo['Foto'] ?? '') ?>">
                            <small class="form-text text-muted">
                                La ruta se generará automáticamente según categoría, género y subcategoría
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
                <button type="submit" class="btn btn-success btn-lg shadow" id="submitButton">
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

<!-- Incluir CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/producto_form.css">
<!-- Incluir JavaScript con configuración PHP -->
<script>
// Configuración desde PHP para JavaScript
const FORM_CONFIG = {
    tieneVariantes: <?= isset($tieneVariantes) && $tieneVariantes ? 'true' : 'false' ?>,
    baseUrl: '<?= BASE_URL ?>',
    articuloId: <?= isset($articulo['ID_Articulo']) ? $articulo['ID_Articulo'] : 'null' ?>,
    categoriaInicial: <?= isset($articulo['ID_Categoria']) ? $articulo['ID_Categoria'] : 'null' ?>,
    generoInicial: <?= isset($articulo['ID_Genero']) ? $articulo['ID_Genero'] : 'null' ?>,
    subcategoriaInicial: <?= isset($articulo['ID_SubCategoria']) ? $articulo['ID_SubCategoria'] : 'null' ?>,
    
    // ⭐⭐ Asegúrate que esto sea 'false' para productos existentes ⭐⭐
    esProductoNuevo: <?= empty($articulo['ID_Articulo']) ? 'true' : 'false' ?>,
    
    rutaImagenActual: '<?= isset($articulo['Foto']) ? htmlspecialchars($articulo['Foto']) : '' ?>'
};

</script>
<script src="<?= BASE_URL ?>assets/js/FormProductos.js"></script>