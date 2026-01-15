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
                               placeholder="Buscar por nombre de producto, atributos o subcategoría..."
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

                <!-- FILTRO POR ATRIBUTOS DINÁMICOS -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark">
                        <i class="fas fa-cube me-1 text-success"></i>Estado
                    </label>
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="1" <?= ($filtrosAplicados['estado'] ?? '') == '1' ? 'selected' : '' ?>>Activos</option>
                        <option value="0" <?= ($filtrosAplicados['estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactivos</option>
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
                                <h2 class="fw-bold text-warning mb-1">
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
                                <h2 class="fw-bold text-danger mb-1">
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
        <div class="alert alert-gradient-info alert-dismissible fade show mb-4" role="alert">
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
                    <span class="badge bg-info ms-2"><?= count($productos) ?> productos</span>
                </div>
                <a href="<?= BASE_URL; ?>?c=Admin&a=variantes" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-times me-1"></i> Ver todos
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- TARJETAS DE PRODUCTOS -->
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

                            <!-- Información esencial -->
                            <div class="variantes-info">
                                <!-- Estadísticas rápidas -->
                                <div class="row text-center small mb-3">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <div class="fw-bold text-primary fs-5"><?= $producto['TotalVariantes']; ?></div>
                                            <small class="text-muted">Variantes</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div>
                                            <div class="fw-bold text-<?= ($producto['StockTotal'] ?? 0) > 0 ? 'success' : 'danger'; ?> fs-5">
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
                                        <span class="badge bg-info"><?= count($producto['AtributosDisponibles']) ?></span>
                                    </div>
                                    
                                    <div class="atributos-container">
                                        <?php if (!empty($producto['AtributosDisponibles'])): ?>
                                            <?php 
                                            // CORRECCIÓN: Agrupar atributos por nombre CORRECTAMENTE
                                            $atributosAgrupados = [];
                                            foreach ($producto['AtributosDisponibles'] as $atributo) {
                                                $nombre = $atributo['nombre'];
                                                $valor = $atributo['valor'];
                                                if (!isset($atributosAgrupados[$nombre])) {
                                                    $atributosAgrupados[$nombre] = [];
                                                }
                                                // CORRECCIÓN: Solo agregar si no existe ya este valor
                                                if (!in_array($valor, $atributosAgrupados[$nombre])) {
                                                    $atributosAgrupados[$nombre][] = $valor;
                                                }
                                            }
                                            
                                            // Colores predefinidos para diferentes atributos
                                            $coloresAtributos = [
                                                'Color' => 'primary',
                                                'Medida' => 'warning',
                                                'Talla' => 'success', 
                                                'Material' => 'info',
                                                'Estilo' => 'secondary',
                                                'Género' => 'dark',
                                                'Marca' => 'danger'
                                            ];
                                            ?>
                                            
                                            <?php foreach ($atributosAgrupados as $nombreAtributo => $valores): ?>
                                                <?php 
                                                // Determinar color basado en el nombre del atributo
                                                $color = $coloresAtributos[$nombreAtributo] ?? 'light';
                                                $textColor = in_array($color, ['light', 'warning']) ? 'text-dark' : 'text-white';
                                                ?>
                                                
                                                <div class="atributo-grupo mb-2 p-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="fw-semibold text-dark small">
                                                            <i class="fas fa-tag me-1 text-<?= $color ?>"></i>
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
                                            <span class="badge bg-info bg-opacity-10 text-info w-100">
                                                <i class="fas fa-folder me-1"></i>
                                                <?= htmlspecialchars($producto['N_Categoria'] ?? 'Sin categoría') ?>
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <span class="badge bg-warning bg-opacity-10 text-warning w-100">
                                                <i class="fas fa-tag me-1"></i>
                                                <?= htmlspecialchars($producto['SubCategoria'] ?? 'Sin subcat.') ?>
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <span class="badge bg-primary bg-opacity-10 text-primary w-100">
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
                                class="btn btn-info btn-sm">
                                    <i class="fas fa-cog me-1"></i> Gestionar Variantes
                                </a>
                                <div class="btn-group w-100" role="group">
                                    <a href="<?= BASE_URL; ?>?c=Admin&a=productoForm&id=<?= $producto['ID_Articulo']; ?>" 
                                    class="btn btn-outline-secondary btn-sm flex-fill">
                                        <i class="fas fa-edit me-1"></i> Editar Producto
                                    </a>
                                    <a href="<?= BASE_URL; ?>?c=Admin&a=detalleProducto&id=<?= $producto['ID_Articulo']; ?>" 
                                    class="btn btn-outline-primary btn-sm">
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

.atributo-grupo {
    transition: all 0.2s ease;
}

.atributo-grupo:hover {
    background-color: #e9ecef !important;
    border-color: #ced4da !important;
}
</style>