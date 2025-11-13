<?php
// views/admin/variantes.php
// SOLO código de vista - NO clases
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-palette me-2"></i> Gestión de Variantes e Imágenes</h2>
        <a href="<?= BASE_URL; ?>?c=Admin&a=productos" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a Productos
        </a>
    </div>

    <div class="row">
        <?php if (empty($productos)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay productos registrados. 
                    <a href="<?= BASE_URL; ?>?c=Admin&a=productoForm" class="alert-link">Crear primer producto</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($productos as $producto): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <strong class="text-truncate"><?= htmlspecialchars($producto['N_Articulo']); ?></strong>
                            <span class="badge bg-primary">ID: <?= $producto['ID_Articulo']; ?></span>
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
                                     class="img-fluid rounded" 
                                     style="max-height: 150px; object-fit: cover;"
                                     alt="<?= htmlspecialchars($producto['N_Articulo']); ?>">
                            </div>

                            <!-- Información de variantes -->
                            <div class="variantes-info">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total variantes:</span>
                                    <span class="badge bg-<?= $producto['TotalVariantes'] > 0 ? 'success' : 'secondary'; ?>">
                                        <?= $producto['TotalVariantes']; ?>
                                    </span>
                                </div>
                                
                                <?php if ($producto['Colores']): ?>
                                <div class="mb-2">
                                    <small class="text-muted">Colores:</small>
                                    <div class="fw-semibold text-truncate"><?= htmlspecialchars($producto['Colores']); ?></div>
                                </div>
                                <?php endif; ?>

                                <?php if ($producto['Tallas']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Tallas:</small>
                                    <div class="fw-semibold text-truncate"><?= htmlspecialchars($producto['Tallas']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-footer bg-white">
                            <div class="btn-group w-100" role="group">
                                <a href="<?= BASE_URL; ?>?c=Admin&a=detalleProducto&id=<?= $producto['ID_Articulo']; ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-cog me-1"></i> Gestionar
                                </a>
                                <a href="<?= BASE_URL; ?>?c=Admin&a=productoForm&id=<?= $producto['ID_Articulo']; ?>" 
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Estadísticas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Resumen de Variantes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-primary"><?= count($productos); ?></h3>
                                <small class="text-muted">Productos Base</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-success">
                                    <?= array_sum(array_column($productos, 'TotalVariantes')); ?>
                                </h3>
                                <small class="text-muted">Total Variantes</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-info">
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
                                </h3>
                                <small class="text-muted">Colores Únicos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-warning">
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
                                </h3>
                                <small class="text-muted">Tallas Únicas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
