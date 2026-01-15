<?php
// =============================
// VIEWS/ADMIN/INDEX.PHP - DISEÑO MODERNIZADO BASADO EN DASHBOARD
// =============================
?>

<!-- Incluir el CSS del dashboard -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/AdminFavoritos.css">

<div class="dashboard-body">
    <!-- HEADER MODERNO -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="welcome-text">
                            <h1>Análisis de Favoritos</h1>
                            <p>
                                Estadísticas y tendencias de productos favoritos
                                <span class="badge badge-custom ms-2 bg-dark">
                                    <?= $_SESSION['rol'] == 1 ? 'Administrador' : 'Editor' ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="current-time">
                        <small>ID: <?php echo htmlspecialchars($_SESSION['ID_Usuario'] ?? '-'); ?></small><br>
                        <small><?php echo date('d/m/Y H:i:s'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container">
        <!-- TARJETAS DE ESTADÍSTICAS PRINCIPALES (CON MÁS COLORES) -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card primary">
                    <div class="card-icon" style="background: rgba(58, 74, 86, 0.42); color: #1B202D;">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-number"><?= $estadisticas['total_favoritos'] ?? 0 ?></div>
                    <div class="stat-label">Total de Favoritos</div>
                    <div class="stat-details">
                        <small>
                            <i class="fas fa-chart-line me-1" style="color: #1B202D;"></i>
                            En todo el sistema
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card accent">
                    <div class="card-icon" style="background: rgba(58, 74, 86, 0.42); color: #1B202D;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?= $estadisticas['total_usuarios_favoritos'] ?? 0 ?></div>
                    <div class="stat-label">Usuarios Activos</div>
                    <div class="stat-details">
                        <small>
                            <i class="fas fa-user-check me-1" style="color: #1B202D;"></i>
                            Con favoritos registrados
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card info">
                    <div class="card-icon" style="background: rgba(58, 74, 86, 0.42); color: #1B202D;">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="stat-number"><?= $estadisticas['total_productos_con_favoritos'] ?? 0 ?></div>
                    <div class="stat-label">Productos Favoritos</div>
                    <div class="stat-details">
                        <small>
                            <i class="fas fa-star me-1" style="color: #1B202D;"></i>
                            Productos únicos
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card secondary">
                    <div class="card-icon" style="background: rgba(58, 74, 86, 0.42); color: #1B202D;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="stat-number"><?= number_format($estadisticas['promedio_favoritos_por_producto'] ?? 0, 1) ?></div>
                    <div class="stat-label">Promedio por Producto</div>
                    <div class="stat-details">
                        <small>
                            <i class="fas fa-calculator me-1" style="color: #1B202D;"></i>
                            Favoritos por producto
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE ACCESO RÁPIDO -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="quick-access-section">
                    <div class="section-header">
                        <h3><i class="fas fa-bolt me-2"></i>Navegación Rápida</h3>
                        <div class="section-actions">
                            <a href="#productos-destacados" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-star me-1"></i>Ir a Destacados
                            </a>
                        </div>
                    </div>
                    <div class="quick-actions-grid">
                        <a href="#productos-destacados" class="quick-action-btn">
                            <div class="action-icon" style="background: rgba(231, 76, 60, 0.1); color: #1B202D;">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="action-text">
                                <h6>Productos Destacados</h6>
                                <small>Más y menos favoritos</small>
                            </div>
                        </a>
                        <a href="#estadisticas-detalladas" class="quick-action-btn">
                            <div class="action-icon" style="background: rgba(52, 152, 219, 0.1); color: #1B202D;">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="action-text">
                                <h6>Estadísticas</h6>
                                <small>Ver métricas generales</small>
                            </div>
                        </a>
                        <a href="#graficos-distribucion" class="quick-action-btn">
                            <div class="action-icon" style="background: rgba(46, 204, 113, 0.1); color: #1B202D;">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="action-text">
                                <h6>Gráficos</h6>
                                <small>Distribución y análisis</small>
                            </div>
                        </a>
                        <a href="#listas-top" class="quick-action-btn">
                            <div class="action-icon" style="background: rgba(241, 196, 15, 0.1); color: #1B202D;">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="action-text">
                                <h6>Top Productos</h6>
                                <small>Ranking completo</small>
                            </div>
                        </a>
                        <a href="#filtros-section" class="quick-action-btn">
                            <div class="action-icon" style="background: rgba(155, 89, 182, 0.1); color: #1B202D;">
                                <i class="fas fa-filter"></i>
                            </div>
                            <div class="action-text">
                                <h6>Filtros</h6>
                                <small>Buscar y filtrar</small>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>?c=Admin&a=dashboard" class="quick-action-btn">
                            <div class="action-icon" style="background: rgba(149, 165, 166, 0.1); color: #95a5a6;">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <div class="action-text">
                                <h6>Dashboard</h6>
                                <small>Volver al panel principal</small>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>" class="quick-action-btn">
                            <div class="action-icon" style="background: rgba(52, 73, 94, 0.1); color: #34495e;">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="action-text">
                                <h6>Ir a la Tienda</h6>
                                <small>Ver tienda pública</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS AVANZADOS -->
        <div id="filtros-section" class="row mb-4">
            <div class="col-12">
                <div class="section-header-modern">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-2"><i class="fas fa-filter me-2"></i>Filtros Avanzados</h3>
                            <p class="text-muted mb-0">Busca y filtra productos por diferentes criterios</p>
                        </div>
                        <div class="section-actions">
                            <a href="#" class="btn btn-sm btn-outline-dark" onclick="scrollToTop()">
                                <i class="fas fa-arrow-up me-1"></i>Volver arriba
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>Buscar y Filtrar Productos
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- MENSAJES DEL SISTEMA -->
                        <?php if (isset($_SESSION['mensaje'])): ?>
                            <div class="alert alert-<?= $_SESSION['mensaje_tipo'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <div><?= $_SESSION['mensaje'] ?></div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
                        <?php endif; ?>

                        <form action="<?= BASE_URL ?>?c=FavoritoStats&a=index" method="get" class="row g-3">
                            <input type="hidden" name="c" value="FavoritoStats">
                            <input type="hidden" name="a" value="index">
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="buscar" class="form-label">
                                        <i class="fas fa-search me-1"></i>Buscar Producto
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="buscar" 
                                               name="buscar" 
                                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                                               placeholder="Ingresa nombre del producto...">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="categoria" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Categoría
                                    </label>
                                    <select class="form-select" id="categoria" name="categoria">
                                        <option value="">Todas las categorías</option>
                                        <?php if (!empty($categorias)): ?>
                                            <?php foreach ($categorias as $categoria): ?>
                                                <option value="<?= htmlspecialchars($categoria) ?>" 
                                                    <?= ($_GET['categoria'] ?? '') === $categoria ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($categoria) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="subcategoria" class="form-label">
                                        <i class="fas fa-layer-group me-1"></i>Subcategoría
                                    </label>
                                    <select class="form-select" id="subcategoria" name="subcategoria">
                                        <option value="">Todas las subcategorías</option>
                                        <?php if (!empty($subcategorias)): ?>
                                            <?php foreach ($subcategorias as $subcategoria): ?>
                                                <option value="<?= htmlspecialchars($subcategoria) ?>" 
                                                    <?= ($_GET['subcategoria'] ?? '') === $subcategoria ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($subcategoria) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="genero" class="form-label">
                                        <i class="fas fa-venus-mars me-1"></i>Género
                                    </label>
                                    <select class="form-select" id="genero" name="genero">
                                        <option value="">Todos los géneros</option>
                                        <?php if (!empty($generos)): ?>
                                            <?php foreach ($generos as $genero): ?>
                                                <option value="<?= htmlspecialchars($genero) ?>" 
                                                    <?= ($_GET['genero'] ?? '') === $genero ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($genero) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-eye-slash me-1"></i>Opciones
                                    </label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="incluir_inactivos" name="incluir_inactivos" value="1" 
                                            <?= (isset($_GET['incluir_inactivos']) ? (bool)$_GET['incluir_inactivos'] : false) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="incluir_inactivos">
                                            Mostrar productos inactivos
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-dark">
                                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                                    </button>
                                    <a href="<?= BASE_URL ?>?c=FavoritoStats&a=index" class="btn btn-outline-dark">
                                        <i class="fas fa-times me-1"></i> Limpiar Filtros
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados de búsqueda -->
        <?php if (isset($modoBusqueda) && $modoBusqueda): ?>
            <?php if (!empty($resultadosBusqueda)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="content-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-search me-2"></i>Resultados de Búsqueda
                                <span class="badge bg-light text-dark ms-2"><?= count($resultadosBusqueda) ?> productos</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr class="bg-dark text-white">
                                            <th>Producto</th>
                                            <th>Imagen</th>
                                            <th>Categoría</th>
                                            <th>Subcategoría</th>
                                            <th>Género</th>
                                            <th>Favoritos</th>
                                            <th>Estado</th>
                                            <th>Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resultadosBusqueda as $producto): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($producto['Nombre']) ?></strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $rutaImagen = trim($producto['Foto'] ?? '');
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
                                                         class="rounded" style="width: 50px; height: 50px; object-fit: cover;"
                                                         alt="<?= htmlspecialchars($producto['Nombre']); ?>">
                                                </td>
                                                <td><?= htmlspecialchars($producto['N_Categoria'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($producto['SubCategoria'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($producto['N_Genero'] ?? 'N/A') ?></td>
                                                <td>
                                                    <span class="badge bg-dark">
                                                        <?= $producto['total_favoritos'] ?> <i class="fas fa-heart"></i>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= ($producto['Activo'] ?? 1) ? 'success' : 'secondary' ?>">
                                                        <?= ($producto['Activo'] ?? 1) ? 'Activo' : 'Inactivo' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>$<?= number_format($producto['Precio_Final'] ?? 0, 2) ?></strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="content-card">
                        <div class="card-body text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-search fa-3x" style="color: #95a5a6;"></i>
                            </div>
                            <h5 class="fw-bold">No se encontraron productos</h5>
                            <p class="text-muted mb-0">No hay resultados para los filtros aplicados.</p>
                            <a href="<?= BASE_URL ?>?c=FavoritoStats&a=index" class="btn btn-outline-dark mt-3">
                                <i class="fas fa-times me-1"></i> Limpiar filtros
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- PRODUCTOS DESTACADOS -->
        <div id="productos-destacados" class="row mb-4">
            <div class="col-12">
                <div class="section-header-modern">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-2"><i class="fas fa-star me-2"></i>Productos Destacados</h3>
                            <p class="text-muted mb-0">Productos más y menos favoritos</p>
                        </div>
                        <div class="section-actions">
                            <a href="#filtros-section" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-arrow-up me-1"></i>Ir a Filtros
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Producto Más Favorito -->
            <div class="col-lg-6 mb-3">
                <div class="content-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #212736ff, #1B202D); color: white;">
                        <h5 class="mb-0">
                            <i class="fas fa-crown me-2"></i>Producto Más Favorito
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($productoMasFavorito)): ?>
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <?php
                                    $rutaImagen = trim($productoMasFavorito['Foto'] ?? '');
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
                                         class="rounded" style="width: 100px; height: 100px; object-fit: cover;"
                                         alt="<?= htmlspecialchars($productoMasFavorito['Nombre'] ?? ''); ?>">
                                </div>
                                <div class="col">
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($productoMasFavorito['Nombre'] ?? '') ?></h5>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge" style="background: #646d82ff;">
                                            <i class="fas fa-heart me-1"></i>
                                            <?= $productoMasFavorito['total_favoritos'] ?? 0 ?> favoritos
                                        </span>
                                        <span class="badge" style="background: #3f5386ff;">
                                            <i class="fas fa-tag me-1"></i>
                                            <?= htmlspecialchars($productoMasFavorito['N_Categoria'] ?? 'N/A') ?>
                                        </span>
                                        <span class="badge" style="background: #171c2bff;">
                                            <i class="fas fa-venus-mars me-1"></i>
                                            <?= htmlspecialchars($productoMasFavorito['N_Genero'] ?? 'N/A') ?>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="h5 mb-0 fw-bold">$<?= number_format($productoMasFavorito['Precio_Final'] ?? 0, 2) ?></span>
                                        <span class="badge" style="background-color: #1B202D;">
                                            <?= ($productoMasFavorito['Activo'] ?? 1) ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-heart-broken fa-3x" style="color: #95a5a6;"></i>
                                </div>
                                <h6 class="fw-bold">No hay datos</h6>
                                <p class="text-muted mb-0">No se encontraron productos favoritos</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Producto Menos Favorito -->
            <div class="col-lg-6 mb-3">
                <div class="content-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #1f2637bd, #1B202D); color: white;">
                        <h5 class="mb-0">
                            <i class="fas fa-arrow-down me-2"></i>Producto Menos Favorito
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($productoMenosFavorito)): ?>
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <?php
                                    $rutaImagen = trim($productoMenosFavorito['Foto'] ?? '');
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
                                         class="rounded" style="width: 100px; height: 100px; object-fit: cover;"
                                         alt="<?= htmlspecialchars($productoMenosFavorito['Nombre'] ?? ''); ?>">
                                </div>
                                <div class="col">
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($productoMenosFavorito['Nombre'] ?? '') ?></h5>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge" style="background: <?= ($productoMenosFavorito['total_favoritos'] ?? 0) == 0 ? '#1B202D' : '#1b202da7' ?>;">
                                            <i class="fas fa-heart me-1"></i>
                                            <?= $productoMenosFavorito['total_favoritos'] ?? 0 ?> favoritos
                                        </span>
                                        <span class="badge" style="background: #324267ff;">
                                            <i class="fas fa-tag me-1"></i>
                                            <?= htmlspecialchars($productoMenosFavorito['N_Categoria'] ?? 'N/A') ?>
                                        </span>
                                        <span class="badge" style="background: #5b647aff;">
                                            <i class="fas fa-venus-mars me-1"></i>
                                            <?= htmlspecialchars($productoMenosFavorito['N_Genero'] ?? 'N/A') ?>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="h5 mb-0 fw-bold">$<?= number_format($productoMenosFavorito['Precio_Final'] ?? 0, 2) ?></span>
                                        <span class="badge" style="background-color: <?= ($productoMenosFavorito['Activo'] ?? 1) ? '#1B202D' : '#6c757d' ?>;">
                                            <?= ($productoMenosFavorito['Activo'] ?? 1) ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-heart-broken fa-3x" style="color: #95a5a6;"></i>
                                </div>
                                <h6 class="fw-bold">No hay datos</h6>
                                <p class="text-muted mb-0">No se encontraron productos</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS DE DISTRIBUCIÓN -->
        <?php if (!empty($distribucionCategoria) || !empty($distribucionGenero) || !empty($distribucionSubcategoria)): ?>
        <div id="graficos-distribucion" class="row mb-4">
            <div class="col-12">
                <div class="section-header-modern">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-2"><i class="fas fa-chart-pie me-2"></i>Gráficos de Distribución</h3>
                            <p class="text-muted mb-0">Distribución de favoritos por categoría, género y subcategoría</p>
                        </div>
                        <div class="section-actions">
                            <a href="#estadisticas-detalladas" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-arrow-up me-1"></i>Ir a Estadísticas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($distribucionCategoria)): ?>
            <div class="col-md-4 mb-3">
                <div class="content-card h-100">
                    <div class="card-header" style="background: linear-gradient(135deg, #161b27ff, #656c7fff); color: white;">
                        <h5 class="mb-0">
                            <i class="fas fa-tags me-2"></i>Por Categoría
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartCategorias" height="250"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($distribucionGenero)): ?>
            <div class="col-md-4 mb-3">
                <div class="content-card h-100">
                    <div class="card-header" style="background: linear-gradient(135deg, #656c7fff, #161b27ff); color: white;">
                        <h5 class="mb-0">
                            <i class="fas fa-venus-mars me-2"></i>Por Género
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartGeneros" height="250"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($distribucionSubcategoria)): ?>
            <div class="col-md-4 mb-3">
                <div class="content-card h-100">
                    <div class="card-header" style="background: linear-gradient(135deg, #161b27ff, #656c7fff); color: white;">
                        <h5 class="mb-0">
                            <i class="fas fa-layer-group me-2"></i>Top Subcategorías
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartSubcategorias" height="250"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- RANKING DE PRODUCTOS -->
        <div id="listas-top" class="row mb-4">
            <div class="col-12">
                <div class="section-header-modern">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-2"><i class="fas fa-trophy me-2"></i>Ranking de Productos</h3>
                            <p class="text-muted mb-0">Top 10 productos más y menos favoritos</p>
                        </div>
                        <div class="section-actions">
                            <a href="#graficos-distribucion" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-arrow-up me-1"></i>Ir a Gráficos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top 10 Más Favoritos -->
            <div class="col-md-6 mb-3">
                <div class="content-card h-100">
                    <div class="card-header" style="background: linear-gradient(135deg, #171c29ff, #404657ff); color: white;">
                        <h5 class="mb-0">
                            <i class="fas fa-crown me-2"></i>Top 10 Más Favoritos
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($masFavoritos)): ?>
                            <div class="dashboard-list">
                                <?php foreach ($masFavoritos as $index => $producto): ?>
                                    <li class="list-item">
                                        <div class="d-flex align-items-center">
                                            <?php
                                            $rutaImagen = trim($producto['Foto'] ?? '');
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
                                                 class="item-image" 
                                                 alt="<?= htmlspecialchars($producto['Nombre']); ?>">
                                            <div class="item-info">
                                                <h6><?= htmlspecialchars($producto['Nombre']) ?></h6>
                                                <small>
                                                    <?= htmlspecialchars($producto['N_Categoria'] ?? 'N/A') ?> • 
                                                    <?= $producto['total_favoritos'] ?> favoritos
                                                </small>
                                            </div>
                                        </div>
                                        <span class="badge badge-custom" style="background: #232a3bff; color: white;">#<?= $index + 1 ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-heart-broken fa-3x" style="color: #95a5a6;"></i>
                                </div>
                                <h6 class="fw-bold">No hay datos</h6>
                                <p class="text-muted mb-0">No se encontraron productos favoritos</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Top 10 Menos Favoritos -->
            <div class="col-md-6 mb-3">
                <div class="content-card h-100">
                    <div class="card-header" style="background: linear-gradient(135deg, #404657ff, #171c29ff); color: white;">
                        <h5 class="mb-0">
                            <i class="fas fa-arrow-down me-2"></i>Top 10 Menos Favoritos
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($menosFavoritos)): ?>
                            <div class="dashboard-list">
                                <?php foreach ($menosFavoritos as $index => $producto): ?>
                                    <li class="list-item">
                                        <div class="d-flex align-items-center">
                                            <?php
                                            $rutaImagen = trim($producto['Foto'] ?? '');
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
                                                 class="item-image" 
                                                 alt="<?= htmlspecialchars($producto['Nombre']); ?>">
                                            <div class="item-info">
                                                <h6><?= htmlspecialchars($producto['Nombre']) ?></h6>
                                                <small>
                                                    <?= htmlspecialchars($producto['N_Categoria'] ?? 'N/A') ?> • 
                                                    <?= $producto['total_favoritos'] ?> favoritos
                                                </small>
                                            </div>
                                        </div>
                                        <span class="badge badge-custom" style="background: <?= $producto['total_favoritos'] == 0 ? '#232a3bff' : '#3f485eff' ?>; color: white;">
                                            #<?= $index + 1 ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-heart-broken fa-3x" style="color: #95a5a6;"></i>
                                </div>
                                <h6 class="fw-bold">No hay datos</h6>
                                <p class="text-muted mb-0">No se encontraron productos</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTÓN PARA VOLVER AL INICIO -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <div class="back-to-top-section">
                    <a href="#" class="btn btn-dark btn-lg back-to-top-btn" onclick="scrollToTop()">
                        <i class="fas fa-arrow-up me-2"></i>Volver al Inicio
                    </a>
                    <p class="text-muted mt-2 mb-0">
                        <small>Análisis de favoritos | <?php echo date('d/m/Y H:i:s'); ?></small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para gráficos -->
<?php if (!empty($distribucionCategoria) || !empty($distribucionGenero) || !empty($distribucionSubcategoria)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Paleta de colores mejorada con más variedad
    const palette = {
        primary: [
            '#e74c3c', // Rojo
            '#3498db', // Azul
            '#2ecc71', // Verde
            '#f1c40f', // Amarillo
            '#9b59b6', // Púrpura
            '#1abc9c', // Turquesa
            '#34495e', // Azul oscuro
            '#e67e22', // Naranja
            '#95a5a6', // Gris
            '#16a085'  // Verde oscuro
        ],
        light: [
            'rgba(231, 76, 60, 0.1)',
            'rgba(52, 152, 219, 0.1)',
            'rgba(46, 204, 113, 0.1)',
            'rgba(241, 196, 15, 0.1)',
            'rgba(155, 89, 182, 0.1)',
            'rgba(26, 188, 156, 0.1)',
            'rgba(52, 73, 94, 0.1)',
            'rgba(230, 126, 34, 0.1)',
            'rgba(149, 165, 166, 0.1)',
            'rgba(22, 160, 133, 0.1)'
        ]
    };

    <?php if (!empty($distribucionCategoria)): ?>
    const ctxCategorias = document.getElementById('chartCategorias')?.getContext('2d');
    if (ctxCategorias) {
        new Chart(ctxCategorias, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { 
                    return "'" . addslashes($item['categoria']) . "'"; 
                }, $distribucionCategoria)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_map(function($item) { 
                        return $item['total_favoritos']; 
                    }, $distribucionCategoria)); ?>],
                    backgroundColor: palette.primary.slice(0, <?php echo count($distribucionCategoria); ?>),
                    borderColor: 'white',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: {
                                family: 'Poppins',
                                size: 11
                            },
                            color: '#1B202D'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(27, 32, 45, 0.95)',
                        titleFont: {
                            family: 'Montserrat',
                            size: 12
                        },
                        bodyFont: {
                            family: 'Poppins',
                            size: 11
                        },
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} favoritos (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }
    <?php endif; ?>

    <?php if (!empty($distribucionGenero)): ?>
    const ctxGeneros = document.getElementById('chartGeneros')?.getContext('2d');
    if (ctxGeneros) {
        new Chart(ctxGeneros, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { 
                    return "'" . addslashes($item['genero']) . "'"; 
                }, $distribucionGenero)); ?>],
                datasets: [{
                    label: 'Favoritos',
                    data: [<?php echo implode(',', array_map(function($item) { 
                        return $item['total_favoritos']; 
                    }, $distribucionGenero)); ?>],
                    backgroundColor: palette.primary.slice(0, <?php echo count($distribucionGenero); ?>).map(color => 
                        Chart.helpers.color(color).alpha(0.8).rgbString()
                    ),
                    borderColor: palette.primary.slice(0, <?php echo count($distribucionGenero); ?>),
                    borderWidth: 1,
                    borderRadius: 6,
                    hoverBackgroundColor: palette.primary.slice(0, <?php echo count($distribucionGenero); ?>)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: 'Poppins',
                                size: 11
                            },
                            color: '#1B202D'
                        },
                        title: {
                            display: true,
                            text: 'Cantidad de Favoritos',
                            font: {
                                family: 'Montserrat',
                                size: 12,
                                weight: '600'
                            },
                            color: '#1B202D'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Poppins',
                                size: 11
                            },
                            color: '#1B202D'
                        },
                        title: {
                            display: true,
                            text: 'Géneros',
                            font: {
                                family: 'Montserrat',
                                size: 12,
                                weight: '600'
                            },
                            color: '#1B202D'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(27, 32, 45, 0.95)',
                        titleFont: {
                            family: 'Montserrat',
                            size: 12
                        },
                        bodyFont: {
                            family: 'Poppins',
                            size: 11
                        },
                        padding: 12
                    }
                }
            }
        });
    }
    <?php endif; ?>

    <?php if (!empty($distribucionSubcategoria)): ?>
    const ctxSubcategorias = document.getElementById('chartSubcategorias')?.getContext('2d');
    if (ctxSubcategorias) {
        new Chart(ctxSubcategorias, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { 
                    return "'" . addslashes($item['subcategoria']) . "'"; 
                }, $distribucionSubcategoria)); ?>],
                datasets: [{
                    label: 'Favoritos',
                    data: [<?php echo implode(',', array_map(function($item) { 
                        return $item['total_favoritos']; 
                    }, $distribucionSubcategoria)); ?>],
                    backgroundColor: palette.primary.slice(0, <?php echo count($distribucionSubcategoria); ?>).map(color => 
                        Chart.helpers.color(color).alpha(0.9).rgbString()
                    ),
                    borderColor: palette.primary.slice(0, <?php echo count($distribucionSubcategoria); ?>).map(color => 
                        Chart.helpers.color(color).darken(0.2).rgbString()
                    ),
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: 'Poppins',
                                size: 11
                            },
                            color: '#1B202D'
                        },
                        title: {
                            display: true,
                            text: 'Cantidad de Favoritos',
                            font: {
                                family: 'Montserrat',
                                size: 12,
                                weight: '600'
                            },
                            color: '#1B202D'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Poppins',
                                size: 11
                            },
                            color: '#1B202D'
                        },
                        title: {
                            display: true,
                            text: 'Subcategorías',
                            font: {
                                family: 'Montserrat',
                                size: 12,
                                weight: '600'
                            },
                            color: '#1B202D'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(27, 32, 45, 0.95)',
                        titleFont: {
                            family: 'Montserrat',
                            size: 12
                        },
                        bodyFont: {
                            family: 'Poppins',
                            size: 11
                        },
                        padding: 12,
                        callbacks: {
                            title: function(tooltipItems) {
                                return tooltipItems[0].label;
                            },
                            label: function(context) {
                                return `${context.parsed.x} favoritos`;
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
});

// Funciones de navegación
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const offset = 80;
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;
        
        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Navegación suave para todos los enlaces internos
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                scrollToElement(href.substring(1));
            }
        });
    });

    // Mostrar/ocultar botón de volver arriba al hacer scroll
    const backToTopBtn = document.querySelector('.back-to-top-btn');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTopBtn.style.opacity = '1';
            backToTopBtn.style.visibility = 'visible';
        } else {
            backToTopBtn.style.opacity = '0';
            backToTopBtn.style.visibility = 'hidden';
        }
    });
});
</script>
<?php endif; ?>