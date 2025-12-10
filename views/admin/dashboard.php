<?php
// =============================
// VIEWS/ADMIN/DASHBOARD.PHP - CORREGIDO
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
    
    // Estadísticas de categorías
    'total_categorias' => (int)$this->db->query("SELECT COUNT(*) FROM categoria")->fetchColumn(),
    'total_subcategorias' => (int)$this->db->query("SELECT COUNT(*) FROM subcategoria")->fetchColumn(),
    'total_colores' => (int)$this->db->query("SELECT COUNT(*) FROM color")->fetchColumn(),
    'total_tallas' => (int)$this->db->query("SELECT COUNT(*) FROM talla")->fetchColumn(),
    'total_precios' => (int)$this->db->query("SELECT COUNT(*) FROM precio")->fetchColumn(),
];

// Obtener productos más populares (basado en stock y actividad)
$productosPopulares = $this->db->query("
    SELECT a.ID_Articulo, a.N_Articulo, a.Foto, a.Activo,
           c.N_Categoria, COUNT(p.ID_Producto) as total_variantes,
           COALESCE(SUM(p.Cantidad), 0) as stock_total
    FROM articulo a
    LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
    LEFT JOIN producto p ON a.ID_Articulo = p.ID_Articulo
    WHERE a.Activo = 1
    GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, a.Activo, c.N_Categoria
    ORDER BY stock_total DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$productosMenosPopulares = $this->db->query("
    SELECT a.ID_Articulo, a.N_Articulo, a.Foto, a.Activo,
           c.N_Categoria, COUNT(p.ID_Producto) as total_variantes,
           COALESCE(SUM(p.Cantidad), 0) as stock_total
    FROM articulo a
    LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
    LEFT JOIN producto p ON a.ID_Articulo = p.ID_Articulo
    WHERE a.Activo = 1
    GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, a.Activo, c.N_Categoria
    ORDER BY stock_total ASC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

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

// Obtener productos con stock bajo (basado en variantes)
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

// Obtener variantes más recientes
$ultimasVariantes = $this->db->query("
    SELECT p.ID_Producto, a.N_Articulo, 
           p.ValorAtributo1, p.ValorAtributo2, p.ValorAtributo3,
           p.Cantidad, p.Porcentaje, p.Nombre_Producto
    FROM producto p
    LEFT JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
    ORDER BY p.ID_Producto DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <!-- HEADER DEL DASHBOARD -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-primary mb-2">
                <i class="fas fa-tachometer-alt me-2"></i>Panel de Administración
            </h1>
            <p class="text-muted mb-0">
                Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['Nombre_Completo'] ?? 'Administrador'); ?></strong> 
                <span class="badge bg-<?= $_SESSION['rol'] == 1 ? 'danger' : 'warning' ?> ms-2">
                    <?= $_SESSION['rol'] == 1 ? 'Administrador' : 'Editor' ?>
                </span>
            </p>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">Usuario ID: <?php echo htmlspecialchars($_SESSION['ID_Usuario'] ?? '-'); ?></small>
            <small class="text-muted"><?php echo date('d/m/Y H:i:s'); ?></small>
        </div>
    </div>

    <!-- RESUMEN DE ESTADISTICAS -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Resumen del Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="border-end">
                                <h4 class="text-primary fw-bold"><?= $stats['total_colores'] ?></h4>
                                <small class="text-muted">Colores Disponibles</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border-end">
                                <h4 class="text-success fw-bold"><?= $stats['total_tallas'] ?></h4>
                                <small class="text-muted">Tallas Configuradas</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border-end">
                                <h4 class="text-warning fw-bold"><?= $stats['total_precios'] ?></h4>
                                <small class="text-muted">Precios Base</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div>
                                <h4 class="text-danger fw-bold"><?= $stats['variantes_sin_stock'] ?></h4>
                                <small class="text-muted">Variantes Sin Stock</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TARJETAS DE ESTADÍSTICAS PRINCIPALES -->
    <div class="row mb-4">
        <!-- Productos Base -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Productos Base</h5>
                            <h2 class="mb-0"><?= $stats['total_articulos'] ?></h2>
                        </div>
                        <i class="fas fa-box-open fa-2x opacity-50"></i>
                    </div>
                    <div class="mt-2">
                        <small>
                            <i class="fas fa-check-circle text-success me-1"></i>
                            <?= $stats['productos_activos'] ?> activos
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Variantes -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Variantes</h5>
                            <h2 class="mb-0"><?= $stats['total_variantes'] ?></h2>
                        </div>
                        <i class="fas fa-palette fa-2x opacity-50"></i>
                    </div>
                    <div class="mt-2">
                        <small>
                            <i class="fas fa-cubes me-1"></i>
                            <?= $stats['variantes_con_stock'] ?> con stock
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usuarios -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Usuarios</h5>
                            <h2 class="mb-0"><?= $stats['total_usuarios'] ?></h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                    <div class="mt-2">
                        <small>
                            <i class="fas fa-user-shield me-1"></i>
                            <?= $stats['administradores'] ?> admin
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventas -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-warning text-dark h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Ventas</h5>
                            <h2 class="mb-0"><?= $stats['total_ventas'] ?></h2>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                    </div>
                    <div class="mt-2">
                        <small>
                            <i class="fas fa-chart-line me-1"></i>
                            Total registradas
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Total -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-dark text-white h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Stock Total</h5>
                            <h2 class="mb-0"><?= $stats['stock_total'] ?></h2>
                        </div>
                        <i class="fas fa-cubes fa-2x opacity-50"></i>
                    </div>
                    <div class="mt-2">
                        <small>
                            <i class="fas fa-warehouse me-1"></i>
                            Unidades en sistema
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categorías -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-secondary text-white h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Categorías</h5>
                            <h2 class="mb-0"><?= $stats['total_categorias'] ?></h2>
                        </div>
                        <i class="fas fa-tags fa-2x opacity-50"></i>
                    </div>
                    <div class="mt-2">
                        <small>
                            <i class="fas fa-layer-group me-1"></i>
                            <?= $stats['total_subcategorias'] ?> subcategorías
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE ACCIONES RÁPIDAS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-bolt me-2 text-warning"></i>Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-xl-2 col-md-4 col-sm-6">
                            <a href="<?= BASE_URL ?>?c=Admin&a=productoForm" class="btn btn-success w-100 h-100 py-3">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                Nuevo Producto
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6">
                            <a href="<?= BASE_URL ?>?c=Admin&a=productos" class="btn btn-primary w-100 h-100 py-3">
                                <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                Gestionar Productos
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6">
                            <a href="<?= BASE_URL ?>?c=Admin&a=variantes" class="btn btn-info w-100 h-100 py-3">
                                <i class="fas fa-palette fa-2x mb-2"></i><br>
                                Variantes
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6">
                            <a href="<?= BASE_URL ?>?c=Tallas&a=index" class="btn btn-warning w-100 h-100 py-3">
                                <i class="fas fa-ruler fa-2x mb-2"></i><br>
                                Gestión Tallas
                            </a>
                        </div>
                        <?php if ($_SESSION['rol'] == 1): ?>
                        <div class="col-xl-2 col-md-4 col-sm-6">
                            <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" class="btn btn-danger w-100 h-100 py-3">
                                <i class="fas fa-users fa-2x mb-2"></i><br>
                                Gestión Usuarios
                            </a>
                        </div>
                        <?php endif; ?>
                        <div class="col-xl-2 col-md-4 col-sm-6">
                            <a href="<?= BASE_URL ?>" class="btn btn-secondary w-100 h-100 py-3">
                                <i class="fas fa-store fa-2x mb-2"></i><br>
                                Ir a la Tienda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE PRODUCTOS Y VARIANTES -->
    <div class="row">
        <!-- Últimos Productos Agregados -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Últimos Productos Agregados
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($ultimosProductos)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($ultimosProductos as $producto): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
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
                                             class="rounded me-3" 
                                             style="width: 40px; height: 40px; object-fit: cover;"
                                             alt="<?= htmlspecialchars($producto['N_Articulo']); ?>">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($producto['N_Articulo']); ?></h6>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($producto['N_Categoria']); ?> • 
                                                <?= $producto['total_variantes'] ?> variantes
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-<?= $producto['Activo'] ? 'success' : 'secondary' ?>">
                                        <?= $producto['Activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No hay productos recientes</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Últimas Variantes -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>Últimas Variantes Agregadas
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($ultimasVariantes)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($ultimasVariantes as $variante): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($variante['N_Articulo']); ?></h6>
                                            <small class="text-muted">
                                                <?php
                                                $atributos = [];
                                                if (!empty($variante['ValorAtributo1'])) $atributos[] = $variante['ValorAtributo1'];
                                                if (!empty($variante['ValorAtributo2'])) $atributos[] = $variante['ValorAtributo2'];
                                                if (!empty($variante['ValorAtributo3'])) $atributos[] = $variante['ValorAtributo3'];
                                                echo !empty($atributos) ? implode(' • ', $atributos) : 'Sin atributos';
                                                ?>
                                                <?php if (!empty($variante['Nombre_Producto'])): ?>
                                                    • <?= htmlspecialchars($variante['Nombre_Producto']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?= $variante['Cantidad'] > 0 ? 'primary' : 'secondary' ?>">
                                                <?= $variante['Cantidad'] ?> unidades
                                            </span>
                                            <br>
                                            <small class="text-muted">+<?= $variante['Porcentaje'] ?>%</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No hay variantes recientes</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ALERTAS DE STOCK -->
    <div class="row">
        <!-- Stock Bajo -->
        <div class="col-lg-6 mb-4">
            <div class="card border-warning h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Stock Bajo (≤ 10 unidades)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stockBajo)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($stockBajo as $producto): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($producto['N_Articulo']); ?></h6>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($producto['N_Categoria']); ?> • 
                                            <?= $producto['total_variantes'] ?> variantes
                                        </small>
                                    </div>
                                    <span class="badge bg-warning"><?= $producto['stock_total'] ?> unidades</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">✅ Todo el stock está en niveles normales</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sin Stock -->
        <div class="col-lg-6 mb-4">
            <div class="card border-danger h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-times-circle me-2"></i>Sin Stock
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($sinStock)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($sinStock as $producto): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($producto['N_Articulo']); ?></h6>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($producto['N_Categoria']); ?> • 
                                            <?= $producto['total_variantes'] ?> variantes
                                        </small>
                                    </div>
                                    <span class="badge bg-danger">Agotado</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">✅ Todos los productos tienen stock</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.card {
    border-radius: 10px;
}

.btn {
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

.list-group-item {
    border: none;
    padding: 1rem 0.5rem;
}

.list-group-item:first-child {
    padding-top: 0;
}

.list-group-item:last-child {
    padding-bottom: 0;
}

.border-end {
    border-right: 1px solid #dee2e6 !important;
}

@media (max-width: 768px) {
    .border-end {
        border-right: none !important;
        border-bottom: 1px solid #dee2e6 !important;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
}
</style>