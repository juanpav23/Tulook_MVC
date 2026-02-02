<?php
// Manejar variables no definidas
$colores = $colores ?? [];
$totalColores = $totalColores ?? 0;
$coloresActivos = $coloresActivos ?? 0;
$coloresInactivos = $coloresInactivos ?? 0;
$coloresEnUso = $coloresEnUso ?? 0;
$modoBusqueda = $modoBusqueda ?? false;
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/colores.css">
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary-dark"><i class="fas fa-palette me-2"></i>Gestión de Colores</h2>
        <a href="<?= BASE_URL ?>?c=Color&a=crear" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Color
        </a>
    </div>

    <!-- Contenedor de alertas -->
    <div id="alert-container"></div>

    <!-- Mensajes de sesión -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['mensaje_tipo'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensaje'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card mb-4 stats-card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary-dark"><i class="fas fa-filter me-2"></i>Filtrar Colores</h5>
                <?php if (!empty($_GET['buscar']) || ($_GET['estado'] ?? 'todos') !== 'todos' || ($_GET['en_uso'] ?? 'todos') !== 'todos'): ?>
                    <span class="badge bg-primary text-light">Filtros activos</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="Color">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-3">
                    <label for="buscar" class="form-label text-primary-dark">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary-light border-primary"><i class="fas fa-search text-primary-dark"></i></span>
                        <input type="text" 
                               class="form-control border-primary" 
                               name="buscar" 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                               placeholder="Nombre o código...">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="estado" class="form-label text-primary-dark">Estado</label>
                    <select class="form-select border-primary" name="estado">
                        <option value="todos">Todos</option>
                        <option value="activos" <?= ($_GET['estado'] ?? '') === 'activos' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivos" <?= ($_GET['estado'] ?? '') === 'inactivos' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="en_uso" class="form-label text-primary-dark">En Uso</label>
                    <select class="form-select border-primary" name="en_uso">
                        <option value="todos">Todos</option>
                        <option value="en_uso" <?= ($_GET['en_uso'] ?? '') === 'en_uso' ? 'selected' : '' ?>>En uso</option>
                        <option value="no_usado" <?= ($_GET['en_uso'] ?? '') === 'no_usado' ? 'selected' : '' ?>>No usados</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i> Aplicar
                        </button>
                        <?php if (!empty($_GET['buscar']) || ($_GET['estado'] ?? 'todos') !== 'todos' || ($_GET['en_uso'] ?? 'todos') !== 'todos'): ?>
                            <a href="<?= BASE_URL ?>?c=Color&a=index" class="btn btn-outline-primary-light text-primary-dark">
                                <i class="fas fa-times me-1"></i> Limpiar filtros
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Total Colores</h5>
                    <h3 class="card-text text-primary-dark"><?= $totalColores ?></h3>
                    <small class="text-primary-light"><i class="fas fa-database me-1"></i> En sistema</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-primary">Activos</h5>
                    <h3 class="card-text text-primary"><?= $coloresActivos ?></h3>
                    <small class="text-primary-light"><i class="fas fa-check-circle me-1"></i> Disponibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-primary-light">Inactivos</h5>
                    <h3 class="card-text text-primary-light"><?= $coloresInactivos ?></h3>
                    <small class="text-primary-light"><i class="fas fa-times-circle me-1"></i> Ocultos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-success">En Uso</h5>
                    <h3 class="card-text text-success"><?= $coloresEnUso ?></h3>
                    <small class="text-primary-light"><i class="fas fa-box me-1"></i> Usados por productos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Colores -->
    <div class="card stats-card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary-dark"><i class="fas fa-table me-2"></i>Lista de Colores</h5>
                <span class="badge bg-primary text-light">
                    Mostrando <?= count($colores) ?> de <?= $totalColores ?> colores
                </span>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($colores)): ?>
                <div class="text-center py-5 no-results">
                    <i class="fas fa-palette fa-3x text-primary-light mb-3"></i>
                    <h5 class="text-primary-light">No hay colores registrados</h5>
                    <?php if ($modoBusqueda): ?>
                        <p class="text-primary-light">No hay resultados para los filtros aplicados</p>
                        <a href="<?= BASE_URL ?>?c=Color&a=index" class="btn btn-primary">
                            <i class="fas fa-times me-1"></i> Limpiar filtros
                        </a>
                    <?php else: ?>
                        <p class="text-primary-light">Comienza creando tu primer color</p>
                        <a href="<?= BASE_URL ?>?c=Color&a=crear" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Crear primer color
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="colors-table">
                        <thead class="table-primary-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Código HEX</th>
                                <th>Muestra</th>
                                <th>Estado</th>
                                <th>Uso</th>
                                <th>Productos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($colores as $color): ?>
                                <?php 
                                    $productosAsociados = $color['productos_asociados'] ?? 0;
                                    $estaEnUso = $productosAsociados > 0;
                                    $estaActivo = ($color['Activo'] ?? 1) == 1;
                                ?>
                                <tr class="hover-shadow-detalle">
                                    <td>
                                        <strong class="text-primary-dark"><?= htmlspecialchars($color['N_Color']) ?></strong>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($color['CodigoHex']) ?></code>
                                        <button class="btn btn-sm btn-outline-primary btn-copy-hex ms-2" 
                                                data-hex-code="<?= htmlspecialchars($color['CodigoHex']) ?>"
                                                title="Copiar código"
                                                onclick="copyHexCode('<?= htmlspecialchars($color['CodigoHex']) ?>', this)">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="color-muestra" 
                                             style="background-color: <?= $color['CodigoHex'] ?>"
                                             title="<?= htmlspecialchars($color['N_Color']) ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $estaActivo ? 'status-active' : 'status-inactive' ?>">
                                            <?= $estaActivo ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="usage-indicator <?= $estaEnUso ? 'usage-in-use' : 'usage-not-in-use' ?>"></span>
                                        <?php if ($estaEnUso): ?>
                                            <span class="text-success">En uso</span>
                                            <small class="text-primary-light">(<?= $productosAsociados ?>)</small>
                                        <?php else: ?>
                                            <span class="text-primary-light">No usado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($estaEnUso): ?>
                                            <button class="btn btn-sm btn-primary btn-view-products"
                                                    onclick="viewProducts(<?= $color['ID_Color'] ?>, '<?= htmlspecialchars($color['N_Color']) ?>')">
                                                <i class="fas fa-boxes me-1"></i> Ver productos
                                            </button>
                                        <?php else: ?>
                                            <span class="text-primary-light">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm action-buttons">
                                            <a href="<?= BASE_URL ?>?c=Color&a=editar&id=<?= $color['ID_Color'] ?>" 
                                            class="btn btn-outline-primary btn-editar" 
                                            title="Editar"
                                            data-id="<?= $color['ID_Color'] ?>"
                                            data-nombre="<?= htmlspecialchars($color['N_Color']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($estaActivo): ?>
                                                <?php if ($estaEnUso): ?>
                                                    <!-- Color en uso - Botón desactivado -->
                                                    <button class="btn btn-secondary btn-desactivar-disabled" 
                                                            title="No se puede desactivar (en uso por productos)"
                                                            disabled>
                                                        <i class="fas fa-toggle-on"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <!-- Color no usado - Se puede desactivar -->
                                                    <a href="<?= BASE_URL ?>?c=Color&a=cambiarEstado&id=<?= $color['ID_Color'] ?>&estado=0" 
                                                    class="btn btn-danger btn-desactivar" 
                                                    title="Desactivar"
                                                    data-id="<?= $color['ID_Color'] ?>"
                                                    data-nombre="<?= htmlspecialchars($color['N_Color']) ?>"
                                                    data-estado="0"
                                                    data-en-uso="<?= $estaEnUso ? '1' : '0' ?>">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <!-- Color inactivo - Siempre se puede activar -->
                                                <a href="<?= BASE_URL ?>?c=Color&a=cambiarEstado&id=<?= $color['ID_Color'] ?>&estado=1" 
                                                class="btn btn-success btn-activar" 
                                                title="Activar"
                                                data-id="<?= $color['ID_Color'] ?>"
                                                data-nombre="<?= htmlspecialchars($color['N_Color']) ?>"
                                                data-estado="1">
                                                    <i class="fas fa-toggle-off"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!$estaEnUso): ?>
                                                <a href="<?= BASE_URL ?>?c=Color&a=eliminar&id=<?= $color['ID_Color'] ?>" 
                                                class="btn btn-outline-danger btn-eliminar" 
                                                title="Eliminar"
                                                data-id="<?= $color['ID_Color'] ?>"
                                                data-nombre="<?= htmlspecialchars($color['N_Color']) ?>"
                                                data-en-uso="<?= $estaEnUso ? '1' : '0' ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary btn-eliminar-disabled" 
                                                        title="No se puede eliminar (en uso)"
                                                        disabled>
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Resumen -->
                <?php if ($modoBusqueda): ?>
                    <div class="alert alert-primary-light mt-3">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Mostrando <?= count($colores) ?> colores con los filtros aplicados:
                            <?php 
                            $filtrosAplicados = [];
                            if (!empty($_GET['buscar'])) $filtrosAplicados[] = "búsqueda: '{$_GET['buscar']}'";
                            if (!empty($_GET['estado'])) {
                                $estadoTexto = ($_GET['estado'] == 'activos') ? 'activos' : 'inactivos';
                                $filtrosAplicados[] = "estado: {$estadoTexto}";
                            }
                            if (!empty($_GET['en_uso'])) {
                                $usoTexto = ($_GET['en_uso'] == 'en_uso') ? 'en uso' : 'no usados';
                                $filtrosAplicados[] = "{$usoTexto}";
                            }
                            echo implode(', ', $filtrosAplicados);
                            ?>
                        </small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-primary-light mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Total: <strong><?= count($colores) ?></strong> colores registrados. 
                        <span class="ms-2">
                            <span class="status-badge status-active me-2">Activos: <?= $coloresActivos ?></span>
                            <span class="status-badge status-inactive me-2">Inactivos: <?= $coloresInactivos ?></span>
                            <span class="badge bg-success text-white">En uso: <?= $coloresEnUso ?></span>
                        </span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/colores.js"></script>
<script src="<?= BASE_URL ?>assets/js/color-messages.js"></script>