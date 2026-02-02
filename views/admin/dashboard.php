<?php
// =============================
// VIEWS/ADMIN/DASHBOARD.PHP - DISEÑO MODERNIZADO
// =============================

// Obtener estadísticas adicionales (solo con tablas existentes)
$stats = [
    // Estadísticas básicas
    'total_articulos' => $counts['articulos'] ?? 0,
    'total_productos' => $counts['productos'] ?? 0,
    'total_usuarios' => $counts['usuarios'] ?? 0,
    'total_ventas' => $counts['ventas'] ?? 0,
    
    // Estadísticas de productos
    'productos_activos' => (int)$this->db->query("SELECT COUNT(*) FROM articulo WHERE Activo = 1")->fetchColumn(),
    'productos_inactivos' => (int)$this->db->query("SELECT COUNT(*) FROM articulo WHERE Activo = 0")->fetchColumn(),
    'stock_total' => (int)$this->db->query("SELECT SUM(Cantidad) FROM producto")->fetchColumn(),
    
    // Estadísticas de variantes
    'total_variantes' => (int)$this->db->query("SELECT COUNT(*) FROM producto")->fetchColumn(),
    'variantes_con_stock' => (int)$this->db->query("SELECT COUNT(*) FROM producto WHERE Cantidad > 0")->fetchColumn(),
    'variantes_sin_stock' => (int)$this->db->query("SELECT COUNT(*) FROM producto WHERE Cantidad = 0")->fetchColumn(),
    
    // Estadísticas de usuarios
    'usuarios_activos' => (int)$this->db->query("SELECT COUNT(*) FROM usuario WHERE Activo = 1")->fetchColumn(),
    'usuarios_inactivos' => (int)$this->db->query("SELECT COUNT(*) FROM usuario WHERE Activo = 0")->fetchColumn(),
    'administradores' => (int)$this->db->query("SELECT COUNT(*) FROM usuario WHERE ID_Rol = 1")->fetchColumn(),
    'editores' => (int)$this->db->query("SELECT COUNT(*) FROM usuario WHERE ID_Rol = 2")->fetchColumn(),
    'clientes' => (int)$this->db->query("SELECT COUNT(*) FROM usuario WHERE ID_Rol = 3")->fetchColumn(),
    
    // Estadísticas de categorías (solo tablas que existen)
    'total_categorias' => (int)$this->db->query("SELECT COUNT(*) FROM categoria")->fetchColumn(),
    'total_subcategorias' => (int)$this->db->query("SELECT COUNT(*) FROM subcategoria")->fetchColumn(),
];

// Obtener últimos productos agregados
$ultimosProductos = $this->db->query("
    SELECT a.ID_Articulo, a.N_Articulo, a.Foto, a.Activo, 
           c.N_Categoria, g.N_Genero, s.SubCategoria,
           COUNT(p.ID_Producto) as total_variantes
    FROM articulo a
    LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
    LEFT JOIN genero g ON a.ID_Genero = g.ID_Genero
    LEFT JOIN subcategoria s ON a.ID_SubCategoria = s.ID_SubCategoria
    LEFT JOIN producto p ON a.ID_Articulo = p.ID_Articulo
    GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, a.Activo, c.N_Categoria, g.N_Genero, s.SubCategoria
    ORDER BY a.ID_Articulo DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos con stock bajo
$stockBajo = $this->db->query("
    SELECT a.ID_Articulo, a.N_Articulo, a.Foto, a.Activo,
           c.N_Categoria, COUNT(p.ID_Producto) as total_variantes,
           COALESCE(SUM(p.Cantidad), 0) as stock_total
    FROM articulo a
    LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
    LEFT JOIN producto p ON a.ID_Articulo = p.ID_Articulo
    WHERE a.Activo = 1
    GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, a.Activo, c.N_Categoria
    HAVING stock_total <= 10 AND stock_total > 0
    ORDER BY stock_total ASC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos sin stock
$sinStock = $this->db->query("
    SELECT a.ID_Articulo, a.N_Articulo, a.Foto,
           c.N_Categoria, COUNT(p.ID_Producto) as total_variantes,
           COALESCE(SUM(p.Cantidad), 0) as stock_total
    FROM articulo a
    LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
    LEFT JOIN producto p ON a.ID_Articulo = p.ID_Articulo
    GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria
    HAVING stock_total = 0
    ORDER BY a.N_Articulo ASC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener variantes más recientes CON IMAGEN DEL ARTÍCULO
$ultimasVariantes = $this->db->query("
    SELECT p.ID_Producto, a.N_Articulo, a.Foto,
           p.ValorAtributo1, p.ValorAtributo2, p.ValorAtributo3,
           p.Cantidad, p.Porcentaje, p.Nombre_Producto
    FROM producto p
    LEFT JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
    ORDER BY p.ID_Producto DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Incluir el CSS del dashboard -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">

<div class="dashboard-body">
    <!-- HEADER MODERNO -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo substr(htmlspecialchars($_SESSION['Nombre_Completo'] ?? 'A'), 0, 1); ?>
                        </div>
                        <div class="welcome-text">
                            <h1>Panel de Administración</h1>
                            <p>
                                Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['Nombre_Completo'] ?? 'Administrador'); ?></strong>
                                <span class="badge badge-custom ms-2 bg-dark">
                                    <?= $_SESSION['rol'] == 1 ? 'Administrador' : 'Editor' ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="current-time">
                        <small><?php echo date('d/m/Y H:i:s'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container">
        <!-- TARJETAS DE ESTADÍSTICAS PRINCIPALES -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card primary">
                    <div class="card-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="stat-number"><?= $stats['total_articulos'] ?></div>
                    <div class="stat-label">Productos Base</div>
                    <div class="stat-details">
                        <small>
                            <i class="fas fa-check-circle me-1"></i>
                            <?= $stats['productos_activos'] ?> activos
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card accent">
                    <div class="card-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="stat-number"><?= $stats['total_variantes'] ?></div>
                    <div class="stat-label">Variantes</div>
                    <div class="stat-details">
                        <small>
                            <i class="fas fa-cubes me-1"></i>
                            <?= $stats['variantes_con_stock'] ?> con stock
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card info">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?= $stats['total_usuarios'] ?></div>
                    <div class="stat-label">Usuarios</div>
                    <div class="stat-details">
                        <small>
                            <i class="fas fa-user-shield me-1"></i>
                            <?= $stats['administradores'] ?> administradores
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card secondary">
                    <div class="card-icon">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="stat-number"><?= $stats['stock_total'] ?></div>
                    <div class="stat-label">Stock Total</div>
                    <div class="stat-details">
                        <small>
                            <i class="fas fa-warehouse me-1"></i>
                            Unidades disponibles
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
                        <h3><i class="fas fa-bolt me-2"></i>Acceso Rápido</h3>
                        <div class="section-actions">
                            <a href="#productos-section" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-box me-1"></i>Ir a Productos
                            </a>
                        </div>
                    </div>
                    <div class="quick-actions-grid">
                        <a href="<?= BASE_URL ?>?c=Admin&a=productoForm" class="quick-action-btn">
                            <div class="action-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="action-text">
                                <h6>Nuevo Producto</h6>
                                <small>Crear nuevo artículo</small>
                            </div>
                        </a>
                        <a href="#productos-section" class="quick-action-btn">
                            <div class="action-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="action-text">
                                <h6>Últimos Productos</h6>
                                <small>Ver productos recientes</small>
                            </div>
                        </a>
                        <a href="#variantes-section" class="quick-action-btn">
                            <div class="action-icon">
                                <i class="fas fa-palette"></i>
                            </div>
                            <div class="action-text">
                                <h6>Últimas Variantes</h6>
                                <small>Ver variantes recientes</small>
                            </div>
                        </a>
                        <a href="#stock-section" class="quick-action-btn">
                            <div class="action-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="action-text">
                                <h6>Control de Stock</h6>
                                <small>Alertas de inventario</small>
                            </div>
                        </a>
                        <?php if ($_SESSION['rol'] == 1): ?>
                        <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" class="quick-action-btn">
                            <div class="action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="action-text">
                                <h6>Gestión Usuarios</h6>
                                <small>Administrar usuarios</small>
                            </div>
                        </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>?c=FavoritoStats&a=index" class="quick-action-btn">
                            <div class="action-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="action-text">
                                <h6>Estadísticas Favoritos</h6>
                                <small>Ver análisis de favoritos</small>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>" class="quick-action-btn">
                            <div class="action-icon">
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

        <!-- SECCIÓN DE PRODUCTOS RECIENTES -->
        <div id="productos-section" class="row mb-4">
            <div class="col-12">
                <div class="section-header-modern">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-2"><i class="fas fa-clock me-2"></i>Productos Recientes</h3>
                            <p class="text-muted mb-0">Últimos productos agregados al sistema</p>
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

        <!-- ÚLTIMOS PRODUCTOS AGREGADOS -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-box me-2"></i>Últimos Productos Agregados
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ultimosProductos)): ?>
                            <div class="row g-3">
                                <?php foreach ($ultimosProductos as $producto): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="product-card">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
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
                                                         class="product-image"
                                                         alt="<?= htmlspecialchars($producto['N_Articulo']); ?>">
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($producto['N_Articulo']); ?></h6>
                                                        <span class="badge ms-2 bg-<?= $producto['Activo'] ? 'dark' : 'secondary' ?>">
                                                            <?= $producto['Activo'] ? 'Activo' : 'Inactivo' ?>
                                                        </span>
                                                    </div>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-tag me-1"></i>
                                                        <?= htmlspecialchars($producto['N_Categoria']); ?>
                                                    </small>
                                                    <small class="text-muted">
                                                        <i class="fas fa-layer-group me-1"></i>
                                                        <?= $producto['total_variantes'] ?> variantes
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-box-open fa-3x"></i>
                                </div>
                                <h6 class="fw-bold">No hay productos recientes</h6>
                                <p class="text-muted mb-0">Agrega nuevos productos para verlos aquí</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE VARIANTES RECIENTES -->
        <div id="variantes-section" class="row mb-4">
            <div class="col-12">
                <div class="section-header-modern">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-2"><i class="fas fa-palette me-2"></i>Variantes Recientes</h3>
                            <p class="text-muted mb-0">Últimas variantes de productos agregadas</p>
                        </div>
                        <div class="section-actions">
                            <a href="#productos-section" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-arrow-up me-1"></i>Ir a Productos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ÚLTIMAS VARIANTES AGREGADAS -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-palette me-2"></i>Últimas Variantes Agregadas
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ultimasVariantes)): ?>
                            <div class="row g-3">
                                <?php foreach ($ultimasVariantes as $variante): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="variant-card">
                                            <div class="row align-items-center mb-3">
                                                <div class="col-auto">
                                                    <?php
                                                    $rutaImagen = trim($variante['Foto'] ?? '');
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
                                                         class="variant-image"
                                                         alt="<?= htmlspecialchars($variante['N_Articulo']); ?>">
                                                </div>
                                                <div class="col">
                                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($variante['N_Articulo']); ?></h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-cube me-1"></i>
                                                        ID: <?= $variante['ID_Producto'] ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="variant-info">
                                                <div class="variant-details mb-3">
                                                    <?php
                                                    $atributos = [];
                                                    if (!empty($variante['ValorAtributo1'])) $atributos[] = $variante['ValorAtributo1'];
                                                    if (!empty($variante['ValorAtributo2'])) $atributos[] = $variante['ValorAtributo2'];
                                                    if (!empty($variante['ValorAtributo3'])) $atributos[] = $variante['ValorAtributo3'];
                                                    if (!empty($atributos)): ?>
                                                        <small class="text-muted d-block mb-2">
                                                            <i class="fas fa-tags me-1"></i>
                                                            <?= implode(' • ', $atributos) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($variante['Nombre_Producto'])): ?>
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-tag me-1"></i>
                                                            <?= htmlspecialchars($variante['Nombre_Producto']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-dark">
                                                        <i class="fas fa-box me-1"></i>
                                                        <?= $variante['Cantidad'] ?> unidades
                                                    </span>
                                                    <?php if (!empty($variante['Porcentaje'])): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-percentage me-1"></i>
                                                            <?= $variante['Porcentaje'] ?>%
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-palette fa-3x"></i>
                                </div>
                                <h6 class="fw-bold">No hay variantes recientes</h6>
                                <p class="text-muted mb-0">Agrega nuevas variantes para verlas aquí</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE CONTROL DE STOCK -->
        <div id="stock-section" class="row mb-4">
            <div class="col-12">
                <div class="section-header-modern">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-2"><i class="fas fa-boxes me-2"></i>Control de Stock</h3>
                            <p class="text-muted mb-0">Monitoreo de inventario y alertas de stock</p>
                        </div>
                        <div class="section-actions">
                            <a href="#variantes-section" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-arrow-up me-1"></i>Ir a Variantes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ALERTAS DE STOCK -->
        <div class="row mb-4">
            <!-- Stock Bajo -->
            <div class="col-lg-6 mb-3">
                <div class="alert-card stock-alert">
                    <div class="card-header">
                        <i class="fas fa-exclamation-circle"></i> Stock Bajo (≤ 10 unidades)
                        <span class="badge badge-light ms-2"><?= count($stockBajo) ?> productos</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($stockBajo)): ?>
                            <div class="row g-3">
                                <?php foreach ($stockBajo as $producto): ?>
                                    <div class="col-12">
                                        <div class="alert-list-item">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
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
                                                         class="rounded"
                                                         style="width: 50px; height: 50px; object-fit: cover;"
                                                         alt="<?= htmlspecialchars($producto['N_Articulo']); ?>">
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <span class="stock-indicator low"></span>
                                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($producto['N_Articulo']); ?></h6>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="fas fa-tag me-1"></i>
                                                            <?= htmlspecialchars($producto['N_Categoria']); ?>
                                                            <span class="mx-2">•</span>
                                                            <i class="fas fa-layer-group me-1"></i>
                                                            <?= $producto['total_variantes'] ?> variantes
                                                        </small>
                                                        <span class="badge badge-dark">
                                                            <i class="fas fa-box me-1"></i>
                                                            <?= $producto['stock_total'] ?> unidades
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-check-circle fa-3x"></i>
                                </div>
                                <h6 class="fw-bold">¡Todo en orden!</h6>
                                <p class="text-muted mb-0">Todo el stock está en niveles normales</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sin Stock -->
            <div class="col-lg-6 mb-3">
                <div class="alert-card stock-out">
                    <div class="card-header">
                        <i class="fas fa-times-circle"></i> Sin Stock
                        <span class="badge badge-light ms-2"><?= count($sinStock) ?> productos</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($sinStock)): ?>
                            <div class="row g-3">
                                <?php foreach ($sinStock as $producto): ?>
                                    <div class="col-12">
                                        <div class="alert-list-item">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
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
                                                         class="rounded"
                                                         style="width: 50px; height: 50px; object-fit: cover;"
                                                         alt="<?= htmlspecialchars($producto['N_Articulo']); ?>">
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <span class="stock-indicator out"></span>
                                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($producto['N_Articulo']); ?></h6>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="fas fa-tag me-1"></i>
                                                            <?= htmlspecialchars($producto['N_Categoria']); ?>
                                                            <span class="mx-2">•</span>
                                                            <i class="fas fa-layer-group me-1"></i>
                                                            <?= $producto['total_variantes'] ?> variantes
                                                        </small>
                                                        <span class="badge badge-dark">
                                                            <i class="fas fa-exclamation-circle me-1"></i>
                                                            Agotado
                                                        </span>
                                                    </div>
                                                    <div class="mt-2">
                                                        <small>
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            Requiere atención inmediata
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-check-circle fa-3x"></i>
                                </div>
                                <h6 class="fw-bold">Stock completo</h6>
                                <p class="text-muted mb-0">Todos los productos tienen stock disponible</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTÓN PARA IR AL INICIO -->
        <div class="row">
            <div class="col-12 text-center">
                <div class="back-to-top-section">
                    <a href="#" class="btn btn-dark btn-lg back-to-top-btn" onclick="scrollToTop()">
                        <i class="fas fa-arrow-up me-2"></i>Volver al Inicio
                    </a>
                    <p class="text-muted mt-2 mb-0">
                        <small>ID de sesión: <?php echo htmlspecialchars($_SESSION['ID_Usuario'] ?? '-'); ?> | Último acceso: <?php echo date('d/m/Y H:i:s'); ?></small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para navegación suave -->
<script>
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Navegación rápida desde el menú
document.addEventListener('DOMContentLoaded', function() {
    // Enlaces internos con scroll suave
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