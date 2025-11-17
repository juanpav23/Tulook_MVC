<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-heart me-2"></i>Estadísticas de Favoritos</h2>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['mensaje_tipo'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensaje'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar y Filtrar Favoritos</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=FavoritoStats&a=index" method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="FavoritoStats">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-3">
                    <label for="buscar" class="form-label">Buscar producto</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="buscar" 
                               name="buscar" 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                               placeholder="Nombre del producto...">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label for="categoria" class="form-label">Categoría</label>
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
                
                <div class="col-md-2">
                    <label for="subcategoria" class="form-label">Subcategoría</label>
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
                
                <div class="col-md-2">
                    <label for="genero" class="form-label">Género</label>
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
                
                <div class="col-md-2">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="incluir_inactivos" name="incluir_inactivos" value="1" 
                            <?= (isset($_GET['incluir_inactivos']) ? (bool)$_GET['incluir_inactivos'] : false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="incluir_inactivos">
                            Incluir inactivos
                        </label>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="col-md-3">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-filter me-1"></i> Aplicar Filtros
                        </button>
                        <a href="<?= BASE_URL ?>?c=FavoritoStats&a=index" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <!-- Resultados de búsqueda -->
            <?php if (isset($modoBusqueda) && $modoBusqueda && !empty($resultadosBusqueda)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-search me-2"></i>Resultados de Búsqueda
                                <span class="badge bg-light text-dark ms-2"><?= count($resultadosBusqueda) ?> productos</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
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
                                                    <?php if (!empty($producto['Foto'])): ?>
                                                        <img src="<?= BASE_URL . $producto['Foto'] ?>" 
                                                            alt="<?= htmlspecialchars($producto['Nombre']) ?>" 
                                                            class="img-fluid rounded" 
                                                            style="max-height: 50px; max-width: 50px;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                            style="height: 50px; width: 50px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($producto['N_Categoria'] ?? $producto['categoria'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($producto['SubCategoria'] ?? $producto['subcategoria'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($producto['N_Genero'] ?? $producto['genero'] ?? 'N/A') ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $producto['total_favoritos'] > 0 ? 'success' : 'secondary' ?>">
                                                        <?= $producto['total_favoritos'] ?> ❤️
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= ($producto['Activo'] ?? 1) ? 'success' : 'danger' ?>">
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
            <?php elseif (isset($modoBusqueda) && $modoBusqueda && empty($resultadosBusqueda)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron productos</h5>
                            <p class="text-muted">No hay resultados para los filtros aplicados.</p>
                            <a href="<?= BASE_URL ?>?c=FavoritoStats&a=index" class="btn btn-primary">
                                <i class="fas fa-times me-1"></i> Limpiar filtros
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Estadísticas Principales - PRODUCTO MÁS Y MENOS FAVORITO -->
    <div class="row mb-4">
        <!-- Producto Más Favorito -->
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-crown me-2"></i>Producto Más Favorito</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($productoMasFavorito)): ?>
                        <div class="mb-3">
                            <img src="<?= BASE_URL . ($productoMasFavorito['Foto'] ?? '') ?>" 
                                 alt="<?= htmlspecialchars($productoMasFavorito['Nombre'] ?? '') ?>" 
                                 class="img-fluid rounded" 
                                 style="max-height: 150px; max-width: 150px;">
                        </div>
                        <h5 class="text-success"><?= htmlspecialchars($productoMasFavorito['Nombre'] ?? '') ?></h5>
                        <p class="mb-1">
                            <span class="badge bg-primary"><?= $productoMasFavorito['total_favoritos'] ?? 0 ?> ❤️</span>
                            <span class="badge bg-secondary"><?= $productoMasFavorito['N_Categoria'] ?? 'N/A' ?></span>
                            <span class="badge bg-info"><?= $productoMasFavorito['N_Genero'] ?? 'N/A' ?></span>
                        </p>
                        <p class="text-muted mb-0">
                            <strong>$<?= number_format($productoMasFavorito['Precio_Final'] ?? 0, 2) ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="text-muted">No hay productos favoritos</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Producto Menos Favorito -->
        <div class="col-md-6">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-arrow-down me-2"></i>Producto Menos Favorito</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($productoMenosFavorito)): ?>
                        <div class="mb-3">
                            <img src="<?= BASE_URL . ($productoMenosFavorito['Foto'] ?? '') ?>" 
                                 alt="<?= htmlspecialchars($productoMenosFavorito['Nombre'] ?? '') ?>" 
                                 class="img-fluid rounded" 
                                 style="max-height: 150px; max-width: 150px;">
                        </div>
                        <h5 class="text-warning"><?= htmlspecialchars($productoMenosFavorito['Nombre'] ?? '') ?></h5>
                        <p class="mb-1">
                            <span class="badge bg-primary"><?= $productoMenosFavorito['total_favoritos'] ?? 0 ?> ❤️</span>
                            <span class="badge bg-secondary"><?= $productoMenosFavorito['N_Categoria'] ?? 'N/A' ?></span>
                            <span class="badge bg-info"><?= $productoMenosFavorito['N_Genero'] ?? 'N/A' ?></span>
                        </p>
                        <p class="text-muted mb-0">
                            <strong>$<?= number_format($productoMenosFavorito['Precio_Final'] ?? 0, 2) ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="text-muted">No hay productos favoritos</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Distribución -->
    <?php if (!empty($distribucionCategoria) || !empty($distribucionGenero) || !empty($distribucionSubcategoria)): ?>
    <div class="row mb-4">
        <!-- Distribución por Categoría -->
        <?php if (!empty($distribucionCategoria)): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribución por Categoría</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartCategorias" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Distribución por Género -->
        <?php if (!empty($distribucionGenero)): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Distribución por Género</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartGeneros" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Distribución por Subcategoría -->
        <?php if (!empty($distribucionSubcategoria)): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Top 10 Subcategorías</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartSubcategorias" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5>Total Favoritos</h5>
                    <h2><?= $estadisticas['total_favoritos'] ?? 0 ?></h2>
                    <small><i class="fas fa-heart"></i> En sistema</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5>Usuarios Activos</h5>
                    <h2><?= $estadisticas['total_usuarios_favoritos'] ?? 0 ?></h2>
                    <small><i class="fas fa-users"></i> Con favoritos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h5>Productos Favoritos</h5>
                    <h2><?= $estadisticas['total_productos_con_favoritos'] ?? 0 ?></h2>
                    <small><i class="fas fa-box-open"></i> Únicos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h5>Promedio por Producto</h5>
                    <h2><?= number_format($estadisticas['promedio_favoritos_por_producto'] ?? 0, 1) ?></h2>
                    <small><i class="fas fa-chart-line"></i> Favoritos/producto</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Listados de Productos - TOPS 10 -->
    <div class="row">
        <!-- Productos Más Favoritos -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 10 Más Favoritos</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($masFavoritos)): ?>
                        <div class="list-group">
                            <?php foreach ($masFavoritos as $index => $producto): ?>
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-2">
                                            <span class="badge bg-<?= $index < 3 ? 'warning' : 'primary' ?> fs-6">
                                                #<?= $index + 1 ?>
                                            </span>
                                        </div>
                                        <div class="col-3">
                                            <?php if (!empty($producto['Foto'])): ?>
                                            <img src="<?= BASE_URL . $producto['Foto'] ?>" 
                                                 alt="<?= htmlspecialchars($producto['Nombre']) ?>" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 50px;">
                                            <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 50px; width: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-7">
                                            <h6 class="mb-1"><?= htmlspecialchars($producto['Nombre']) ?></h6>
                                            <small class="text-muted">
                                                <span class="badge bg-success"><?= $producto['total_favoritos'] ?> ❤️</span>
                                                <span class="badge bg-secondary"><?= $producto['N_Categoria'] ?? 'N/A' ?></span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No hay productos favoritos</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Productos Menos Favoritos -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-arrow-down me-2"></i>Top 10 Menos Favoritos</h5>
                    <small class="text-muted">Incluye productos sin favoritos</small>
                </div>
                <div class="card-body">
                    <?php if (!empty($menosFavoritos)): ?>
                        <div class="list-group">
                            <?php foreach ($menosFavoritos as $index => $producto): ?>
                                <div class="list-group-item <?= $producto['total_favoritos'] == 0 ? 'bg-light' : '' ?>">
                                    <div class="row align-items-center">
                                        <div class="col-2">
                                            <span class="badge bg-<?= $producto['total_favoritos'] == 0 ? 'danger' : 'secondary' ?> fs-6">
                                                #<?= $index + 1 ?>
                                            </span>
                                        </div>
                                        <div class="col-3">
                                            <?php if (!empty($producto['Foto'])): ?>
                                            <img src="<?= BASE_URL . $producto['Foto'] ?>" 
                                                alt="<?= htmlspecialchars($producto['Nombre']) ?>" 
                                                class="img-fluid rounded" 
                                                style="max-height: 50px;">
                                            <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 50px; width: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-7">
                                            <h6 class="mb-1"><?= htmlspecialchars($producto['Nombre']) ?></h6>
                                            <small class="text-muted">
                                                <span class="badge bg-<?= $producto['total_favoritos'] == 0 ? 'danger' : 'warning' ?>">
                                                    <?= $producto['total_favoritos'] ?> ❤️
                                                    <?= $producto['total_favoritos'] == 0 ? ' (Sin favoritos)' : '' ?>
                                                </span>
                                                <span class="badge bg-secondary"><?= $producto['N_Categoria'] ?? 'N/A' ?></span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No hay productos</p>
                    <?php endif; ?>
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
    // Paleta de colores para gráficos
    const colores = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', 
        '#FF9F40', '#FF6384', '#C9CBCF', '#7CFC00', '#8A2BE2',
        '#DC143C', '#00CED1', '#FFD700', '#32CD32', '#6A5ACD',
        '#FF69B4', '#00FF7F', '#1E90FF', '#FFDAB9', '#DA70D6'
    ];

    <?php if (!empty($distribucionCategoria)): ?>
    // Gráfico de Categorías (Circular)
    const ctxCategorias = document.getElementById('chartCategorias')?.getContext('2d');
    if (ctxCategorias) {
        const chartCategorias = new Chart(ctxCategorias, {
            type: 'pie',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return "'" . addslashes($item['categoria']) . "'"; }, $distribucionCategoria)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_map(function($item) { return $item['total_favoritos']; }, $distribucionCategoria)); ?>],
                    backgroundColor: colores.slice(0, <?php echo count($distribucionCategoria); ?>),
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    },
                    title: {
                        display: true,
                        text: 'Favoritos por Categoría',
                        font: {
                            size: 14
                        }
                    },
                    tooltip: {
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
                }
            }
        });
    }
    <?php endif; ?>

    <?php if (!empty($distribucionGenero)): ?>
    // Gráfico de Géneros (Barras con colores diferentes)
    const ctxGeneros = document.getElementById('chartGeneros')?.getContext('2d');
    if (ctxGeneros) {
        const chartGeneros = new Chart(ctxGeneros, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return "'" . addslashes($item['genero']) . "'"; }, $distribucionGenero)); ?>],
                datasets: [{
                    label: 'Favoritos',
                    data: [<?php echo implode(',', array_map(function($item) { return $item['total_favoritos']; }, $distribucionGenero)); ?>],
                    backgroundColor: colores.slice(0, <?php echo count($distribucionGenero); ?>),
                    borderColor: colores.slice(0, <?php echo count($distribucionGenero); ?>).map(color => color.replace('0.8', '1')),
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad de Favoritos'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Géneros'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Favoritos por Género',
                        font: {
                            size: 14
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.y} favoritos`;
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>

    <?php if (!empty($distribucionSubcategoria)): ?>
    // Gráfico de Subcategorías (Barras horizontales)
    const ctxSubcategorias = document.getElementById('chartSubcategorias')?.getContext('2d');
    if (ctxSubcategorias) {
        const chartSubcategorias = new Chart(ctxSubcategorias, {
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
                    backgroundColor: colores.slice(0, <?php echo count($distribucionSubcategoria); ?>),
                    borderColor: colores.slice(0, <?php echo count($distribucionSubcategoria); ?>).map(color => color.replace('0.8', '1')),
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                indexAxis: 'y', // Hace el gráfico horizontal
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad de Favoritos'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Subcategorías'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Top 10 Subcategorías Más Populares',
                        font: {
                            size: 14
                        }
                    },
                    tooltip: {
                        callbacks: {
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
</script>
<?php endif; ?>