<div class="container-fluid">
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

    <!-- HEADER MEJORADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--primary-dark);">
                <i class="fas fa-box-open me-2"></i> Gestión de Productos Base
            </h2>
            <p class="text-muted mb-0">Administra y busca todos los productos base del sistema</p>
        </div>
        <a href="<?= BASE_URL; ?>?c=Admin&a=productoForm" class="btn btn-success btn-lg shadow" style="background-color: var(--secondary-blue); border-color: var(--secondary-blue);">
            <i class="fas fa-plus-circle me-1"></i> Nuevo Producto
        </a>
    </div>

    <!-- PANEL DE BÚSQUEDA Y FILTROS MEJORADO -->
    <div class="card mb-4 border-0 shadow-lg">
        <div class="card-header bg-gradient-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-search me-2"></i> Búsqueda Avanzada
                </h4>
                <span class="badge bg-white text-primary fs-6">Filtros Activos</span>
            </div>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL; ?>?c=Admin&a=buscarProductos" method="GET" class="row g-3" id="searchForm">
                <input type="hidden" name="c" value="Admin">
                <input type="hidden" name="a" value="buscarProductos">
                
                <!-- BÚSQUEDA PRINCIPAL -->
                <div class="col-md-12">
                    <label class="form-label fw-bold text-dark">Búsqueda por texto (opcional)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                            name="q" 
                            class="form-control border-start-0 ps-0" 
                            placeholder="Buscar productos por nombre, categoría, subcategoría o género..."
                            value="<?= htmlspecialchars($terminoBusqueda ?? '') ?>"
                            id="searchInput">
                    </div>
                </div>

                <!-- FILTROS EN GRID -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark">
                        <i class="fas fa-filter me-1 text-primary"></i>Categoría
                    </label>
                    <select name="categoria" class="form-select" id="filterCategoria">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['ID_Categoria'] ?>" 
                                <?= ($filtrosAplicados['categoria'] ?? '') == $cat['ID_Categoria'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['N_Categoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark">
                        <i class="fas fa-venus-mars me-1 text-info"></i>Género
                    </label>
                    <select name="genero" class="form-select" id="filterGenero">
                        <option value="">Todos los géneros</option>
                        <?php foreach ($generos as $gen): ?>
                            <option value="<?= $gen['ID_Genero'] ?>" 
                                <?= ($filtrosAplicados['genero'] ?? '') == $gen['ID_Genero'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($gen['N_Genero']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark">
                        <i class="fas fa-tags me-1 text-warning"></i>Subcategoría
                    </label>
                    <select name="subcategoria" class="form-select" id="filterSubcategoria">
                        <option value="">Todas las subcategorías</option>
                        <?php foreach ($subcategorias as $sub): ?>
                            <option value="<?= $sub['ID_SubCategoria'] ?>" 
                                <?= ($filtrosAplicados['subcategoria'] ?? '') == $sub['ID_SubCategoria'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sub['SubCategoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark">
                        <i class="fas fa-power-off me-1 text-success"></i>Estado
                    </label>
                    <select name="estado" class="form-select" id="filterEstado">
                        <option value="">Todos los estados</option>
                        <option value="1" <?= ($filtrosAplicados['estado'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
                        <option value="0" <?= ($filtrosAplicados['estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="col-md-12 mt-3">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary btn-lg px-4" id="searchButton">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                        <a href="<?= BASE_URL; ?>?c=Admin&a=productos" class="btn btn-outline-secondary btn-lg px-4" id="clearButton">
                            <i class="fas fa-refresh me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ESTADÍSTICAS MEJORADAS -->
    <?php if (!empty($articulos) || isset($terminoBusqueda)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm stats-card">
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h2 class="fw-bold text-primary mb-1"><?= count($articulos) ?></h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-box me-1"></i>Productos Encontrados
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end">
                                <h2 class="fw-bold text-success mb-1">
                                    <?= count(array_filter($articulos, fn($a) => !empty($a['Activo']))) ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-check-circle me-1"></i>Productos Activos
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end">
                                <h2 class="fw-bold text-warning mb-1">
                                    <?= count(array_filter($articulos, fn($a) => empty($a['Activo']))) ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-pause-circle me-1"></i>Productos Inactivos
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end">
                                <h2 class="fw-bold text-info mb-1">
                                    <?= count(array_unique(array_column($articulos, 'SubCategoria'))) ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-tags me-1"></i>Subcategorías
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <h2 class="fw-bold text-info mb-1">
                                    <?= array_sum(array_column($articulos, 'Cantidad')) ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-cubes me-1"></i>Total en Stock
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- RESULTADOS DE BÚSQUEDA -->
    <?php if (isset($terminoBusqueda) || !empty($_GET)): ?>
        <div class="alert alert-gradient alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Resultados de búsqueda:</strong> 
                    <?php if (!empty($terminoBusqueda)): ?>
                        <span class="fw-semibold">"<?= htmlspecialchars($terminoBusqueda) ?>"</span>
                    <?php endif; ?>
                    <?php if (!empty($_GET['categoria']) || !empty($_GET['genero']) || !empty($_GET['subcategoria']) || !empty($_GET['estado'])): ?>
                        <span class="ms-2">con filtros aplicados</span>
                    <?php endif; ?>
                    <span class="badge bg-primary ms-2"><?= count($articulos) ?> productos</span>
                </div>
                <a href="<?= BASE_URL; ?>?c=Admin&a=productos" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-times me-1"></i> Ver todos
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- TABLA DE PRODUCTOS -->
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-light py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-list me-2"></i>
                    <?= isset($terminoBusqueda) ? 'Productos Encontrados' : 'Todos los Productos Base' ?>
                </h5>
                <div>
                    <span class="badge bg-primary fs-6">Total: <?= count($articulos) ?></span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($articulos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="productosTable">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">Producto</th>
                                <th width="120">Categoría</th>
                                <th width="120">SubCategoría</th>
                                <th width="100">Género</th>
                                <th width="100">Foto</th>
                                <th width="100" class="text-center">Precio Base</th>
                                <th width="100" class="text-center">Estado</th>
                                <th width="180" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articulos as $a): ?>
                                <tr class="hover-shadow">
                                    <td class="ps-3">
                                        <div class="fw-semibold"><?= htmlspecialchars($a['N_Articulo']); ?></div>
                                        <small class="text-muted">
                                            ID: <?= (int)$a['ID_Articulo']; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                            <?= htmlspecialchars($a['N_Categoria']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                            <?= htmlspecialchars($a['SubCategoria']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                            <?= htmlspecialchars($a['N_Genero']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $foto = trim($a['Foto'] ?? '');
                                            if ($foto !== '') {
                                                if (!preg_match('/^https?:/i', $foto) && !str_starts_with($foto, 'ImgProducto/')) {
                                                    $foto = 'ImgProducto/' . ltrim($foto, '/');
                                                }
                                                $rutaFoto = BASE_URL . ltrim($foto, '/');
                                            } else {
                                                $rutaFoto = BASE_URL . 'assets/img/sin_imagen.png';
                                            }
                                        ?>
                                        <img src="<?= htmlspecialchars($rutaFoto); ?>"
                                            class="img-thumbnail rounded-3 shadow-sm product-image"
                                            style="width:80px; height:60px; object-fit:cover;"
                                            alt="<?= htmlspecialchars($a['N_Articulo']); ?>">
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-success">
                                            $<?= number_format($a['PrecioBase'] ?? 0, 2) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- INTERRUPTOR MEJORADO PARA ACTIVAR/DESACTIVAR -->
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" role="switch" 
                                                   id="activo_<?= $a['ID_Articulo'] ?>"
                                                   <?= !empty($a['Activo']) ? 'checked' : '' ?>
                                                   onchange="toggleEstadoProducto(<?= $a['ID_Articulo'] ?>, this.checked)">
                                            <label class="form-check-label" for="activo_<?= $a['ID_Articulo'] ?>">
                                                <span class="badge <?= !empty($a['Activo']) ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                                                    <?= !empty($a['Activo']) ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a class="btn btn-sm btn-outline-primary rounded-pill"
                                            href="<?= BASE_URL; ?>?c=Admin&a=productoForm&id=<?= (int)$a['ID_Articulo']; ?>"
                                            title="Editar producto"
                                            data-bs-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a class="btn btn-sm btn-outline-info rounded-pill"
                                            href="<?= BASE_URL; ?>?c=Admin&a=detalleProducto&id=<?= (int)$a['ID_Articulo']; ?>"
                                            title="Ver detalle y variantes"
                                            data-bs-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a class="btn btn-sm btn-outline-danger rounded-pill"
                                            href="<?= BASE_URL; ?>?c=Admin&a=deleteProducto&id=<?= (int)$a['ID_Articulo']; ?>"
                                            onclick="return confirm('¿Estás seguro de eliminar este producto y todas sus variantes?');"
                                            title="Eliminar producto"
                                            data-bs-toggle="tooltip">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 no-results">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted opacity-50"></i>
                    </div>
                    <h5 class="text-muted fw-semibold">
                        <?= isset($terminoBusqueda) ? 'No se encontraron productos que coincidan con tu búsqueda.' : 'No hay productos registrados.' ?>
                    </h5>
                    <p class="text-muted mb-4">
                        <?= isset($terminoBusqueda) ? 'Intenta con otros términos de búsqueda o ajusta los filtros.' : 'Comienza creando tu primer producto base.' ?>
                    </p>
                    <?php if (isset($terminoBusqueda)): ?>
                        <a href="<?= BASE_URL; ?>?c=Admin&a=productos" class="btn btn-primary btn-lg">
                            <i class="fas fa-list me-1"></i> Ver todos los productos
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL; ?>?c=Admin&a=productoForm" class="btn btn-success btn-lg">
                            <i class="fas fa-plus-circle me-1"></i> Crear primer producto
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Incluir CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/productosAdmin.css">
<!-- Incluir JavaScript -->
<script>
// Función para limpiar mensajes de sesión
function clearSessionMessages() {
    fetch('<?= BASE_URL ?>?c=Admin&a=clearMessages');
}

// Función para cambiar estado del producto con interruptor
function toggleEstadoProducto(idProducto, estado) {
    if (confirm(`¿Estás seguro de ${estado ? 'activar' : 'desactivar'} este producto?`)) {
        fetch(`<?= BASE_URL ?>?c=Admin&a=toggleEstadoProducto&id=${idProducto}&estado=${estado ? 1 : 0}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar badge visualmente
                    const badge = document.querySelector(`#activo_${idProducto}`).nextElementSibling.querySelector('.badge');
                    if (estado) {
                        badge.className = 'badge bg-success rounded-pill';
                        badge.textContent = 'Activo';
                    } else {
                        badge.className = 'badge bg-secondary rounded-pill';
                        badge.textContent = 'Inactivo';
                    }
                    
                    // Mostrar mensaje de éxito
                    showToast(data.message, 'success');
                } else {
                    // Revertir el interruptor si hay error
                    document.querySelector(`#activo_${idProducto}`).checked = !estado;
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.querySelector(`#activo_${idProducto}`).checked = !estado;
                showToast('Error al cambiar el estado', 'danger');
            });
    } else {
        // Revertir el interruptor si el usuario cancela
        document.querySelector(`#activo_${idProducto}`).checked = !estado;
    }
}

// Función para mostrar toast
function showToast(message, type = 'info') {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
    toastContainer.style.zIndex = '1050';
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    const toastBody = document.createElement('div');
    toastBody.className = 'd-flex';
    
    toastBody.innerHTML = `
        <div class="toast-body">
            ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    `;
    
    toast.appendChild(toastBody);
    toastContainer.appendChild(toast);
    document.body.appendChild(toastContainer);
    
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toastContainer.remove();
    });
}
</script>
<script src="<?= BASE_URL ?>assets/js/productos.js"></script>