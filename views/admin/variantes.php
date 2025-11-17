<div class="container-fluid">
    <!-- HEADER MEJORADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-1">
                <i class="fas fa-palette me-2"></i> Gestión de Variantes
            </h2>
            <p class="text-muted mb-0">Administra y busca todas las variantes de productos</p>
        </div>
        <a href="<?= BASE_URL; ?>?c=Admin&a=productos" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-arrow-left me-1"></i> Volver a Productos
        </a>
    </div>

    <!-- PANEL DE BÚSQUEDA Y FILTROS MEJORADO -->
    <div class="card mb-4 border-0 shadow-lg">
        <div class="card-header bg-gradient-info text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-search me-2"></i> Búsqueda Avanzada
                </h4>
                <span class="badge bg-white text-info fs-6">Filtros Activos</span>
            </div>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL; ?>?c=Admin&a=buscarVariantes" method="GET" class="row g-3">
                <input type="hidden" name="c" value="Admin">
                <input type="hidden" name="a" value="buscarVariantes">
                
                <!-- BÚSQUEDA PRINCIPAL -->
                <div class="col-md-12">
                    <label class="form-label fw-bold text-dark">Búsqueda por texto</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               name="q" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Buscar por nombre de producto, color, talla o subcategoría..."
                               value="<?= htmlspecialchars($terminoBusqueda ?? '') ?>">
                    </div>
                </div>

                <!-- FILTROS EN GRID -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark">
                        <i class="fas fa-filter me-1 text-primary"></i>Categoría
                    </label>
                    <select name="categoria" class="form-select">
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
                    <select name="genero" class="form-select">
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
                    <select name="subcategoria" class="form-select">
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
                        <i class="fas fa-palette me-1 text-success"></i>Color
                    </label>
                    <select name="color" class="form-select">
                        <option value="">Todos los colores</option>
                        <?php foreach ($colores as $col): ?>
                            <option value="<?= $col['ID_Color'] ?>" 
                                <?= ($filtrosAplicados['color'] ?? '') == $col['ID_Color'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($col['N_Color']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark">
                        <i class="fas fa-ruler me-1 text-warning"></i>Talla
                    </label>
                    <select name="talla" class="form-select">
                        <option value="">Todas las tallas</option>
                        <?php foreach ($tallas as $tal): ?>
                            <option value="<?= $tal['ID_Talla'] ?>" 
                                <?= ($filtrosAplicados['talla'] ?? '') == $tal['ID_Talla'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tal['N_Talla']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="col-md-12 mt-3">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-info btn-lg px-4">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                        <a href="<?= BASE_URL; ?>?c=Admin&a=variantes" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="fas fa-refresh me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ESTADÍSTICAS MEJORADAS -->
    <?php if (!empty($productos) || isset($terminoBusqueda)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h2 class="fw-bold text-primary mb-1"><?= count($productos) ?></h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-box me-1"></i>Productos Base
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end">
                                <h2 class="fw-bold text-success mb-1">
                                    <?= array_sum(array_column($productos, 'TotalVariantes')) ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-layer-group me-1"></i>Total Variantes
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end">
                                <h2 class="fw-bold text-info mb-1">
                                    <?php
                                    $todosColores = [];
                                    foreach ($productos as $prod) {
                                        if ($prod['Colores']) {
                                            $coloresProd = array_map('trim', explode(',', $prod['Colores']));
                                            $todosColores = array_merge($todosColores, $coloresProd);
                                        }
                                    }
                                    $coloresUnicos = array_unique(array_filter($todosColores));
                                    echo count($coloresUnicos);
                                    ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-palette me-1"></i>Colores Únicos
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end">
                                <h2 class="fw-bold text-warning mb-1">
                                    <?php
                                    $todasTallas = [];
                                    foreach ($productos as $prod) {
                                        if ($prod['Tallas']) {
                                            $tallasProd = array_map('trim', explode(',', $prod['Tallas']));
                                            $todasTallas = array_merge($todasTallas, $tallasProd);
                                        }
                                    }
                                    $tallasUnicas = array_unique(array_filter($todasTallas));
                                    echo count($tallasUnicas);
                                    ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-ruler me-1"></i>Tallas Únicas
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <h2 class="fw-bold text-warning mb-1">
                                    <?php
                                    $todasSubcategorias = [];
                                    foreach ($productos as $prod) {
                                        if ($prod['SubCategoria']) {
                                            $todasSubcategorias[] = $prod['SubCategoria'];
                                        }
                                    }
                                    $subcategoriasUnicas = array_unique(array_filter($todasSubcategorias));
                                    echo count($subcategoriasUnicas);
                                    ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-tags me-1"></i>Subcategorías Únicas
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
        <div class="alert alert-gradient-info alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Resultados de búsqueda:</strong> 
                    <?php if (!empty($terminoBusqueda)): ?>
                        <span class="fw-semibold">"<?= htmlspecialchars($terminoBusqueda) ?>"</span>
                    <?php endif; ?>
                    <?php if (!empty($_GET['categoria']) || !empty($_GET['genero']) || !empty($_GET['subcategoria']) || !empty($_GET['color']) || !empty($_GET['talla'])): ?>
                        <span class="ms-2">con filtros aplicados</span>
                    <?php endif; ?>
                    <span class="badge bg-info ms-2"><?= count($productos) ?> productos</span>
                </div>
                <a href="<?= BASE_URL; ?>?c=Admin&a=variantes" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-times me-1"></i> Ver todos
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- TARJETAS DE PRODUCTOS MEJORADAS -->
    <div class="row">
        <?php if (empty($productos)): ?>
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-search fa-4x text-muted opacity-50"></i>
                        </div>
                        <h5 class="text-muted fw-semibold">
                            <?= isset($terminoBusqueda) ? 'No se encontraron variantes que coincidan con tu búsqueda.' : 'No hay productos registrados.' ?>
                        </h5>
                        <p class="text-muted mb-4">
                            <?= isset($terminoBusqueda) ? 'Intenta con otros términos de búsqueda o ajusta los filtros.' : 'Comienza creando tu primer producto base.' ?>
                        </p>
                        <?php if (isset($terminoBusqueda)): ?>
                            <a href="<?= BASE_URL; ?>?c=Admin&a=variantes" class="btn btn-info btn-lg">
                                <i class="fas fa-list me-1"></i> Ver todos los productos
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL; ?>?c=Admin&a=productoForm" class="btn btn-success btn-lg">
                                <i class="fas fa-plus-circle me-1"></i> Crear primer producto
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($productos as $producto): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-lift">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                            <strong class="text-truncate" title="<?= htmlspecialchars($producto['N_Articulo']); ?>">
                                <?= htmlspecialchars($producto['N_Articulo']); ?>
                            </strong>
                            <span class="badge bg-primary">#<?= $producto['ID_Articulo']; ?></span>
                        </div>
                        
                        <div class="card-body">
                            <!-- Imagen principal -->
                            <div class="text-center mb-3">
                                <?php
                                $rutaImagen = trim($producto['FotoPrincipal'] ?? '');
                                if ($rutaImagen !== '') {
                                    if (!preg_match('/^https?:/i', $rutaImagen) && !str_starts_with($rutaImagen, 'ImgProducto/')) {
                                        $rutaImagen = 'ImgProducto/' . ltrim($rutaImagen, '/');
                                    }
                                    $rutaImagen = BASE_URL . ltrim($rutaImagen, '/');
                                } else {
                                    $rutaImagen = BASE_URL . 'assets/img/sin_imagen.png';
                                }
                                ?>
                                <img src="<?= htmlspecialchars($rutaImagen); ?>" 
                                     class="img-fluid rounded-3 shadow" 
                                     style="max-height: 150px; object-fit: cover;"
                                     alt="<?= htmlspecialchars($producto['N_Articulo']); ?>"
                                     title="<?= htmlspecialchars($producto['N_Articulo']); ?>">
                            </div>

                            <!-- Información de variantes -->
                            <div class="variantes-info">
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                    <span class="text-muted">Variantes:</span>
                                    <span class="badge bg-<?= $producto['TotalVariantes'] > 0 ? 'success' : 'secondary'; ?> rounded-pill">
                                        <?= $producto['TotalVariantes']; ?>
                                    </span>
                                </div>
                                
                                <?php if ($producto['Colores']): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-1">
                                        <i class="fas fa-palette me-1"></i>Colores:
                                    </small>
                                    <div class="fw-semibold text-truncate small" title="<?= htmlspecialchars($producto['Colores']); ?>">
                                        <?= htmlspecialchars($producto['Colores']); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($producto['Tallas']): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-1">
                                        <i class="fas fa-ruler me-1"></i>Tallas:
                                    </small>
                                    <div class="fw-semibold text-truncate small" title="<?= htmlspecialchars($producto['Tallas']); ?>">
                                        <?= htmlspecialchars($producto['Tallas']); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Información adicional -->
                                <div class="row text-center small">
                                    <div class="col-4">
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <?= htmlspecialchars($producto['N_Categoria'] ?? 'Sin categoría') ?>
                                        </span>
                                    </div>
                                    <div class="col-4">
                                        <span class="badge bg-warning bg-opacity-10 text-warning">
                                            <?= htmlspecialchars($producto['SubCategoria'] ?? 'Sin subcategoría') ?>
                                        </span>
                                    </div>
                                    <div class="col-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <?= htmlspecialchars($producto['N_Genero'] ?? 'Sin género') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="btn-group w-100" role="group">
                                <a href="<?= BASE_URL; ?>?c=Admin&a=detalleProducto&id=<?= $producto['ID_Articulo']; ?>" 
                                   class="btn btn-info btn-sm rounded-start">
                                    <i class="fas fa-cog me-1"></i> Gestionar
                                </a>
                                <a href="<?= BASE_URL; ?>?c=Admin&a=productoForm&id=<?= $producto['ID_Articulo']; ?>" 
                                   class="btn btn-outline-secondary btn-sm rounded-end">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.alert-gradient-info {
    background: linear-gradient(135deg, #0a2143ff 0%, #56677cff 100%);
    color: white;
    border: none;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #0a2143ff 0%, #56677cff 100%) !important;
}

.card {
    border-radius: 15px;
}

.btn {
    border-radius: 10px;
}

.badge {
    font-size: 0.75em;
}
</style>