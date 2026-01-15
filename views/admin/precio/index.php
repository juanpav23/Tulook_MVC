<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/precio.css">
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary-dark"><i class="fas fa-tags me-2"></i>Gestión de Precios</h2>
        <a href="<?= BASE_URL ?>?c=Precio&a=crear" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Precio
        </a>
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
    <div class="card mb-4 stats-card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary-dark"><i class="fas fa-filter me-2"></i>Filtrar Precios</h5>
                <?php if (!empty($_GET['buscar']) || ($_GET['estado'] ?? 'todos') !== 'todos' || ($_GET['en_uso'] ?? 'todos') !== 'todos'): ?>
                    <span class="badge bg-primary text-light">Filtros activos</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="Precio">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-3">
                    <label for="buscar" class="form-label text-primary-dark">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary-light border-primary"><i class="fas fa-search text-white"></i></span>
                        <input type="text" 
                               class="form-control border-primary" 
                               name="buscar" 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                               placeholder="Valor...">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label for="estado" class="form-label text-primary-dark">Estado</label>
                    <select class="form-select border-primary" name="estado">
                        <option value="todos">Todos</option>
                        <option value="activos" <?= ($_GET['estado'] ?? '') === 'activos' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivos" <?= ($_GET['estado'] ?? '') === 'inactivos' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="en_uso" class="form-label text-primary-dark">En Uso</label>
                    <select class="form-select border-primary" name="en_uso">
                        <option value="todos">Todos</option>
                        <option value="si" <?= ($_GET['en_uso'] ?? '') === 'si' ? 'selected' : '' ?>>En uso</option>
                        <option value="no" <?= ($_GET['en_uso'] ?? '') === 'no' ? 'selected' : '' ?>>No en uso</option>
                    </select>
                </div>
                
                <div class="col-md-5">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i> Aplicar
                        </button>
                        <?php if (!empty($_GET['buscar']) || ($_GET['estado'] ?? 'todos') !== 'todos' || ($_GET['en_uso'] ?? 'todos') !== 'todos'): ?>
                            <a href="<?= BASE_URL ?>?c=Precio&a=index" class="btn btn-outline-primary-light text-primary-dark">
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
                    <h5 class="card-title text-primary-dark">Total</h5>
                    <h3 class="card-text text-primary-dark"><?= $estadisticas['total'] ?? 0 ?></h3>
                    <small class="text-primary-light">Precios registrados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Activos</h5>
                    <h3 class="card-text text-primary-dark"><?= $estadisticas['activos'] ?? 0 ?></h3>
                    <small class="text-primary-light">Disponibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Inactivos</h5>
                    <h3 class="card-text text-primary-dark"><?= $estadisticas['inactivos'] ?? 0 ?></h3>
                    <small class="text-primary-light">Ocultos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">En Uso</h5>
                    <h3 class="card-text text-primary-dark"><?= $estadisticas['en_uso'] ?? 0 ?></h3>
                    <small class="text-primary-light">Usados por productos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Precios -->
    <div class="card stats-card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary-dark"><i class="fas fa-table me-2"></i>Lista de Precios</h5>
                <span class="badge bg-primary text-light">
                    Mostrando <?= count($precios) ?> de <?= $estadisticas['total'] ?? 0 ?> precios
                </span>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($precios) && (isset($modoBusqueda) && $modoBusqueda)): ?>
                <!-- No hay resultados de búsqueda -->
                <div class="text-center py-5 no-results">
                    <i class="fas fa-search fa-3x text-primary-light mb-3"></i>
                    <h5 class="text-primary-light">No se encontraron precios</h5>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>?c=Precio&a=index" class="btn btn-primary me-2">
                            <i class="fas fa-list me-1"></i> Ver todos
                        </a>
                        <a href="<?= BASE_URL ?>?c=Precio&a=crear" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Crear precio
                        </a>
                    </div>
                </div>
            <?php elseif (empty($precios)): ?>
                <!-- No hay precios en el sistema -->
                <div class="text-center py-5 no-results">
                    <i class="fas fa-tags fa-3x text-primary-light mb-3"></i>
                    <h5 class="text-primary-light">No hay precios registrados</h5>
                    <p class="text-primary-light">Comienza creando tu primer precio.</p>
                    <a href="<?= BASE_URL ?>?c=Precio&a=crear" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Crear Primer Precio
                    </a>
                </div>
            <?php else: ?>
                <!-- Mostrar tabla normal -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary-dark">
                            <tr>
                                <th>Valor</th>
                                <th>Estado</th>
                                <th>Uso</th>
                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($precios as $precio): 
                                $enUso = $precio['en_uso'] ?? false;
                                $totalUso = $precio['total_uso'] ?? 0;
                            ?>
                                <tr class="hover-shadow-detalle">
                                    <td>
                                        <span class="fw-bold text-primary-dark fs-5">
                                            $<?= number_format($precio['Valor'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $precio['Activo'] ? 'bg-primary' : 'bg-secondary' ?>">
                                            <?= $precio['Activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($enUso): ?>
                                            <div class="uso-tag uso-si">
                                                <i class="fas fa-box me-1"></i>
                                                En uso
                                                <button class="btn btn-sm btn-link p-0 ms-1" 
                                                        onclick="verProductosPrecio(<?= $precio['ID_precio'] ?>, '<?= number_format($precio['Valor'], 2) ?>')"
                                                        title="Ver productos">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <small class="d-block text-primary-light mt-1">
                                                <?= $totalUso ?> productos
                                            </small>
                                        <?php else: ?>
                                            <div class="uso-tag uso-no">
                                                <i class="fas fa-box-open me-1"></i>
                                                No en uso
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-primary-light">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($precio['FechaAct'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Ver productos -->
                                            <?php if ($enUso): ?>
                                                <button class="btn btn-outline-primary" 
                                                        onclick="verProductosPrecio(<?= $precio['ID_precio'] ?>, '<?= number_format($precio['Valor'], 2) ?>')"
                                                        title="Ver productos">
                                                    <i class="fas fa-box"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Editar -->
                                            <a href="<?= BASE_URL ?>?c=Precio&a=editar&id=<?= $precio['ID_precio'] ?>" 
                                               class="btn btn-outline-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Activar/Desactivar -->
                                            <?php if ($precio['Activo'] && !$enUso): ?>
                                                <a href="#" 
                                                   class="btn btn-outline-primary" title="Desactivar"
                                                   onclick="confirmarCambiarEstado(event, '<?= number_format($precio['Valor'], 2) ?>', <?= $precio['ID_precio'] ?>, 0)">
                                                    <i class="fas fa-pause"></i>
                                                </a>
                                            <?php elseif (!$precio['Activo'] && !$enUso): ?>
                                                <a href="#" 
                                                   class="btn btn-outline-primary" title="Activar"
                                                   onclick="confirmarCambiarEstado(event, '<?= number_format($precio['Valor'], 2) ?>', <?= $precio['ID_precio'] ?>, 1)">
                                                    <i class="fas fa-play"></i>
                                                </a>
                                            <?php elseif ($enUso): ?>
                                                <button class="btn btn-outline-secondary" disabled title="No se puede cambiar estado (en uso)">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Migrar productos -->
                                            <?php if ($enUso && $precio['Activo']): ?>
                                                <button class="btn btn-outline-primary" 
                                                        onclick="migrarPrecio(<?= $precio['ID_precio'] ?>, '<?= number_format($precio['Valor'], 2) ?>')"
                                                        title="Migrar productos">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Eliminar -->
                                            <?php if (!$enUso): ?>
                                                <a href="#" 
                                                   class="btn btn-outline-danger" title="Eliminar"
                                                   onclick="confirmarEliminarPrecio(event, '<?= number_format($precio['Valor'], 2) ?>', <?= $precio['ID_precio'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary" disabled title="No se puede eliminar (en uso)">
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
                            Mostrando <?= count($precios) ?> precios con los filtros aplicados.
                        </small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-primary-light mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>
                            Valor mínimo: <strong>$<?= isset($estadisticas['valor_minimo']) ? number_format($estadisticas['valor_minimo'], 2) : '0.00' ?></strong> | 
                            Valor promedio: <strong>$<?= isset($estadisticas['valor_promedio']) ? number_format($estadisticas['valor_promedio'], 2) : '0.00' ?></strong> | 
                            Valor máximo: <strong>$<?= isset($estadisticas['valor_maximo']) ? number_format($estadisticas['valor_maximo'], 2) : '0.00' ?></strong>
                        </small>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/precio.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>