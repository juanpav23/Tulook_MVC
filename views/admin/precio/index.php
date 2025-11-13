<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tags me-2"></i>Gestión de Precios</h2>
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
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar y Filtrar Precios</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Precio&a=index" method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="Precio">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-4">
                    <label for="buscar" class="form-label">Buscar por valor</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="buscar" 
                               name="buscar" 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                               placeholder="Ej: 25000 o 25000.00">
                    </div>
                    <div class="form-text">Busca por valor exacto o parcial</div>
                </div>
                
                <div class="col-md-3">
                    <label for="estado" class="form-label">Filtrar por estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos los estados</option>
                        <option value="activo" <?= ($_GET['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivo" <?= ($_GET['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                    </button>
                </div>
                
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>?c=Precio&a=index" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i> Limpiar
                    </a>
                </div>
            </form>

            <!-- Resultados de búsqueda -->
            <?php if (isset($modoBusqueda) && $modoBusqueda): ?>
                <div class="mt-3 p-3 bg-light rounded">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2 text-primary"></i>
                                Resultados de búsqueda:
                                <strong><?= count($precios) ?></strong> precios encontrados
                                
                                <?php if (!empty($_GET['buscar'])): ?>
                                    <span class="badge bg-primary ms-2">
                                        Valor: "<?= htmlspecialchars($_GET['buscar']) ?>"
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($_GET['estado'])): ?>
                                    <span class="badge bg-<?= $_GET['estado'] === 'activo' ? 'success' : 'secondary' ?> ms-2">
                                        Estado: <?= $_GET['estado'] === 'activo' ? 'Activos' : 'Inactivos' ?>
                                    </span>
                                <?php endif; ?>
                            </h6>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?= BASE_URL ?>?c=Precio&a=index" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i> Ver todos los precios
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Estadísticas Mejoradas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Precios</h5>
                    <h3 class="card-text">
                        <?php 
                        $totalPrecios = $this->precioModel->obtenerTodos();
                        echo count($totalPrecios);
                        ?>
                    </h3>
                    <small>
                        <?php if (isset($modoBusqueda) && $modoBusqueda): ?>
                            <i class="fas fa-filter"></i> Filtrado: <?= count($precios) ?>
                        <?php else: ?>
                            <i class="fas fa-database"></i> En sistema
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Precios Activos</h5>
                    <h3 class="card-text">
                        <?= $this->precioModel->contarActivos() ?>
                    </h3>
                    <small><i class="fas fa-check-circle"></i> Disponibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Precios Inactivos</h5>
                    <h3 class="card-text">
                        <?= count($totalPrecios) - $this->precioModel->contarActivos() ?>
                    </h3>
                    <small><i class="fas fa-pause"></i> No disponibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Valor Promedio</h5>
                    <h3 class="card-text">
                        $<?php 
                        if (!empty($totalPrecios)) {
                            $suma = array_sum(array_column($totalPrecios, 'Valor'));
                            echo number_format($suma / count($totalPrecios), 2);
                        } else {
                            echo '0.00';
                        }
                        ?>
                    </h3>
                    <small><i class="fas fa-calculator"></i> Promedio general</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Precios -->
    <div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Precios</h5>
    </div>
    <div class="card-body">
        <?php if (empty($precios) && (isset($modoBusqueda) && $modoBusqueda)): ?>
            <!-- CASO 1: No hay resultados de búsqueda -->
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No se encontraron precios</h5>
                <p class="text-muted">
                    No hay resultados para 
                    <?php if (!empty($_GET['buscar'])): ?>
                        "<strong><?= htmlspecialchars($_GET['buscar']) ?></strong>"
                    <?php endif; ?>
                    <?php if (!empty($_GET['buscar']) && !empty($_GET['estado'])): ?>
                        y estado "<strong><?= $_GET['estado'] === 'activo' ? 'Activos' : 'Inactivos' ?></strong>"
                    <?php elseif (!empty($_GET['estado'])): ?>
                        con estado "<strong><?= $_GET['estado'] === 'activo' ? 'Activos' : 'Inactivos' ?></strong>"
                    <?php endif; ?>
                </p>
                <div class="mt-3">
                    <a href="<?= BASE_URL ?>?c=Precio&a=index" class="btn btn-primary me-2">
                        <i class="fas fa-list me-1"></i> Ver todos los precios
                    </a>
                    <a href="<?= BASE_URL ?>?c=Precio&a=crear" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Crear nuevo precio
                    </a>
                </div>
                
                <!-- Sugerencias de búsqueda -->
                <div class="mt-4 p-3 bg-light rounded" style="max-width: 500px; margin: 0 auto;">
                    <h6 class="text-muted">Sugerencias:</h6>
                    <ul class="list-unstyled text-start text-muted">
                        <li><small><i class="fas fa-lightbulb me-2"></i>Intenta con un valor diferente</small></li>
                        <li><small><i class="fas fa-lightbulb me-2"></i>Verifica que el filtro de estado sea correcto</small></li>
                        <li><small><i class="fas fa-lightbulb me-2"></i>Busca solo por número (ej: 25000 en lugar de 25000.00)</small></li>
                    </ul>
                </div>
            </div>
        <?php elseif (empty($precios)): ?>
            <!-- CASO 2: No hay precios en el sistema -->
            <div class="text-center py-4">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay precios registrados</h5>
                <p class="text-muted">Comienza creando tu primer precio.</p>
                <a href="<?= BASE_URL ?>?c=Precio&a=crear" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Crear Primer Precio
                </a>
            </div>
        <?php else: ?>
            <!-- CASO 3: Hay precios - Mostrar tabla normal -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Valor</th>
                            <th>Estado</th>
                            <th>Última Actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($precios as $precio): 
                            $terminoBusqueda = $_GET['buscar'] ?? '';
                            $coincidenciaValor = !empty($terminoBusqueda) && 
                                                (strpos($precio['Valor'], $terminoBusqueda) !== false || 
                                                 $precio['Valor'] == $terminoBusqueda);
                        ?>
                            <tr class="<?= $coincidenciaValor ? 'table-warning' : '' ?>">
                                <td>
                                    <strong>#<?= $precio['ID_precio'] ?></strong>
                                    <?php if ($coincidenciaValor): ?>
                                        <span class="badge bg-warning ms-1" title="Coincidencia en búsqueda">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">
                                        $<?= number_format($precio['Valor'], 2) ?>
                                    </span>
                                    <?php if ($coincidenciaValor): ?>
                                        <br>
                                        <small class="text-warning">
                                            <i class="fas fa-bullseye"></i> Coincide con "<?= htmlspecialchars($terminoBusqueda) ?>"
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $precio['Activo'] ? 'success' : 'secondary' ?>">
                                        <?= $precio['Activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($precio['FechaAct'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>?c=Precio&a=editar&id=<?= $precio['ID_precio'] ?>" 
                                           class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if ($precio['Activo']): ?>
                                            <a href="<?= BASE_URL ?>?c=Precio&a=cambiarEstado&id=<?= $precio['ID_precio'] ?>&estado=0" 
                                               class="btn btn-outline-warning" title="Desactivar"
                                               onclick="return confirm('¿Estás seguro de desactivar este precio?')">
                                                <i class="fas fa-pause"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>?c=Precio&a=cambiarEstado&id=<?= $precio['ID_precio'] ?>&estado=1" 
                                               class="btn btn-outline-success" title="Activar"
                                               onclick="return confirm('¿Estás seguro de activar este precio?')">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>