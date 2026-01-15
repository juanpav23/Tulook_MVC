<div class="container-fluid">
    <!-- HEADER MEJORADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary-dark mb-1">
                <i class="fas fa-palette me-2"></i> Gestión de Variantes
            </h2>
            <p class="text-muted mb-0">Administra y busca todas las variantes de productos</p>
        </div>
        <a href="<?= BASE_URL; ?>?c=Admin&a=productos" class="btn btn-variante-outline-secondary btn-lg">
            <i class="fas fa-arrow-left me-1"></i> Volver a Productos
        </a>
    </div>

    <!-- PANEL DE BÚSQUEDA Y FILTROS -->
    <div class="card card-variante mb-4 border-0 shadow-lg">
        <div class="card-header bg-primary text-light py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-search me-2"></i> Búsqueda Avanzada
                </h4>
                <span class="badge bg-light text-primary-dark fs-6">Filtros Activos</span>
            </div>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL; ?>?c=Admin&a=buscarVariantes" method="GET" class="row g-3">
                <input type="hidden" name="c" value="Admin">
                <input type="hidden" name="a" value="buscarVariantes">
                
                <!-- BÚSQUEDA PRINCIPAL -->
                <div class="col-md-12">
                    <label class="form-label fw-bold text-primary-dark">Búsqueda por texto</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               name="q" 
                               class="form-control-variante border-start-0 ps-0" 
                               placeholder="Buscar por nombre de producto, atributos o subcategoría..."
                               value="<?= htmlspecialchars($terminoBusqueda ?? '') ?>">
                    </div>
                </div>

                <!-- FILTROS EN GRID -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary-dark">
                        <i class="fas fa-filter me-1 text-primary"></i>Categoría
                    </label>
                    <select name="categoria" class="form-select-variante">
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
                    <label class="form-label fw-bold text-primary-dark">
                        <i class="fas fa-venus-mars me-1 text-primary"></i>Género
                    </label>
                    <select name="genero" class="form-select-variante">
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
                    <label class="form-label fw-bold text-primary-dark">
                        <i class="fas fa-tags me-1 text-primary"></i>Subcategoría
                    </label>
                    <select name="subcategoria" class="form-select-variante">
                        <option value="">Todas las subcategorías</option>
                        <?php foreach ($subcategorias as $sub): ?>
                            <option value="<?= $sub['ID_SubCategoria'] ?>" 
                                <?= ($filtrosAplicados['subcategoria'] ?? '') == $sub['ID_SubCategoria'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sub['SubCategoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- FILTRO POR ATRIBUTOS DINÁMICOS -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary-dark">
                        <i class="fas fa-cube me-1 text-primary"></i>Estado
                    </label>
                    <select name="estado" class="form-select-variante">
                        <option value="">Todos los estados</option>
                        <option value="1" <?= ($filtrosAplicados['estado'] ?? '') == '1' ? 'selected' : '' ?>>Activos</option>
                        <option value="0" <?= ($filtrosAplicados['estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="col-md-12 mt-3">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-variante-primary btn-lg px-4">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                        <a href="<?= BASE_URL; ?>?c=Admin&a=variantes" class="btn btn-variante-outline-secondary btn-lg px-4">
                            <i class="fas fa-refresh me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ESTADÍSTICAS -->
    <?php if (!empty($productos) || isset($terminoBusqueda)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm stats-card-variante">
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h2 class="fw-bold text-primary-dark mb-1"><?= count($productos) ?></h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-box me-1"></i>Productos Base
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end">
                                <h2 class="fw-bold text-primary-dark mb-1">
                                    <?= array_sum(array_column($productos, 'TotalVariantes')) ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-layer-group me-1"></i>Total Variantes
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end">
                                <h2 class="fw-bold text-primary-dark mb-1">
                                    <?php
                                    $todosAtributos = [];
                                    foreach ($productos as $prod) {
                                        if (!empty($prod['Atributos'])) {
                                            $atributosProd = array_map('trim', explode(',', $prod['Atributos']));
                                            $todosAtributos = array_merge($todosAtributos, $atributosProd);
                                        }
                                    }
                                    $atributosUnicos = array_unique(array_filter($todosAtributos));
                                    echo count($atributosUnicos);
                                    ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-palette me-1"></i>Atributos Únicos
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end">
                                <h2 class="fw-bold text-primary-dark mb-1">
                                    <?php
                                    $todasSubcategorias = [];
                                    foreach ($productos as $prod) {
                                        if (!empty($prod['SubCategoria'])) {
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
                        <div class="col-md-3">
                            <div>
                                <h2 class="fw-bold text-primary-dark mb-1">
                                    <?php
                                    $stockTotal = 0;
                                    foreach ($productos as $prod) {
                                        $stockTotal += $prod['StockTotal'] ?? 0;
                                    }
                                    echo $stockTotal;
                                    ?>
                                </h2>
                                <small class="text-muted fw-semibold">
                                    <i class="fas fa-cubes me-1"></i>Stock Total
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
        <div class="alert alert-variante alert-info alert-dismissible fade show mb-4" role="alert">
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
                    <span class="badge bg-primary ms-2"><?= count($productos) ?> productos</span>
                </div>
                <a href="<?= BASE_URL; ?>?c=Admin&a=variantes" class="btn btn-variante-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i> Ver todos
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- TARJETAS DE PRODUCTOS -->
    <div class="row">
        <?php if (empty($productos)): ?>
            <div class="col-12">
                <div class="card card-variante shadow-sm border-0 no-results-variante">
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
                            <a href="<?= BASE_URL; ?>?c=Admin&a=variantes" class="btn btn-variante-primary btn-lg">
                                <i class="fas fa-list me-1"></i> Ver todos los productos
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL; ?>?c=Admin&a=productoForm" class="btn btn-variante-primary btn-lg">
                                <i class="fas fa-plus-circle me-1"></i> Crear primer producto
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($productos as $producto): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card card-variante h-100 shadow-sm border-0 hover-lift-variante">
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
                                    class="img-fluid rounded-3 shadow product-image-variante" 
                                    style="max-height: 150px; object-fit: cover;"
                                    alt="<?= htmlspecialchars($producto['N_Articulo']); ?>"
                                    title="<?= htmlspecialchars($producto['N_Articulo']); ?>">
                            </div>

                            <!-- Información esencial -->
                            <div class="variantes-info">
                                <!-- Estadísticas rápidas -->
                                <div class="row text-center small mb-3">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <div class="fw-bold text-primary-dark fs-5"><?= $producto['TotalVariantes']; ?></div>
                                            <small class="text-muted">Variantes</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div>
                                            <div class="fw-bold text-primary-dark fs-5">
                                                <?= number_format($producto['StockTotal'] ?? 0) ?>
                                            </div>
                                            <small class="text-muted">Stock</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- ATRIBUTOS DISPONIBLES  -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted fw-semibold">
                                            <i class="fas fa-tags me-1"></i>Atributos disponibles:
                                        </small>
                                        <span class="badge bg-primary"><?= count($producto['AtributosDisponibles']) ?></span>
                                    </div>
                                    
                                    <div class="atributos-container-variante">
                                        <?php if (!empty($producto['AtributosDisponibles'])): ?>
                                            <?php 
                                            // Agrupar atributos por nombre
                                            $atributosAgrupados = [];
                                            foreach ($producto['AtributosDisponibles'] as $atributo) {
                                                $nombre = $atributo['nombre'];
                                                $valor = $atributo['valor'];
                                                if (!isset($atributosAgrupados[$nombre])) {
                                                    $atributosAgrupados[$nombre] = [];
                                                }
                                                if (!in_array($valor, $atributosAgrupados[$nombre])) {
                                                    $atributosAgrupados[$nombre][] = $valor;
                                                }
                                            }
                                            
                                            // Colores basados en #1B202D con variaciones
                                            $coloresAtributos = [
                                                'Color' => 'primary',
                                                'Medida' => 'primary-light',
                                                'Talla' => 'primary', 
                                                'Material' => 'primary-light',
                                                'Estilo' => 'primary',
                                                'Género' => 'primary-light',
                                                'Marca' => 'primary'
                                            ];
                                            ?>
                                            
                                            <?php foreach ($atributosAgrupados as $nombreAtributo => $valores): ?>
                                                <?php 
                                                $color = $coloresAtributos[$nombreAtributo] ?? 'primary-light';
                                                $textColor = $color === 'primary' ? 'text-light' : 'text-primary-dark';
                                                ?>
                                                
                                                <div class="atributo-grupo-variante mb-2 p-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="fw-semibold text-primary-dark small">
                                                            <i class="fas fa-tag me-1 text-<?= $color === 'primary' ? 'primary' : 'primary-light' ?>"></i>
                                                            <?= htmlspecialchars($nombreAtributo) ?>
                                                        </span>
                                                        <span class="badge bg-<?= $color ?> <?= $textColor ?> small">
                                                            <?= count($valores) ?> opciones
                                                        </span>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach ($valores as $valor): ?>
                                                            <span class="badge bg-<?= $color ?> <?= $textColor ?> border-0 small">
                                                                <?= htmlspecialchars($valor) ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-center py-3">
                                                <i class="fas fa-inbox fa-2x text-muted opacity-50 mb-2"></i>
                                                <p class="text-muted small mb-0">No hay atributos definidos</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Información de categorías -->
                                <div class="categorias-info">
                                    <div class="row text-center small">
                                        <div class="col-12 mb-2">
                                            <span class="badge bg-primary text-light w-100">
                                                <i class="fas fa-folder me-1"></i>
                                                <?= htmlspecialchars($producto['N_Categoria'] ?? 'Sin categoría') ?>
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <span class="badge bg-primary-light text-primary-dark w-100">
                                                <i class="fas fa-tag me-1"></i>
                                                <?= htmlspecialchars($producto['SubCategoria'] ?? 'Sin subcat.') ?>
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <span class="badge bg-primary text-light w-100">
                                                <i class="fas fa-venus-mars me-1"></i>
                                                <?= htmlspecialchars($producto['N_Genero'] ?? 'Sin género') ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="d-grid gap-2">
                                <a href="<?= BASE_URL; ?>?c=Admin&a=detalleProducto&id=<?= $producto['ID_Articulo']; ?>" 
                                class="btn btn-variante-primary btn-sm">
                                    <i class="fas fa-cog me-1"></i> Gestionar Variantes
                                </a>
                                <div class="btn-group-variantes w-100" role="group">
                                    <a href="<?= BASE_URL; ?>?c=Admin&a=productoForm&id=<?= $producto['ID_Articulo']; ?>" 
                                    class="btn btn-variante-outline-primary-light btn-sm flex-fill">
                                        <i class="fas fa-edit me-1"></i> Editar Producto
                                    </a>
                                    <a href="<?= BASE_URL; ?>?c=Admin&a=detalleProducto&id=<?= $producto['ID_Articulo']; ?>" 
                                    class="btn btn-variante-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Incluir CSS específico para variantes -->
<link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/variantesAdmin.css">