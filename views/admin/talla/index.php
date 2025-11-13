<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-ruler me-2"></i>Gestión de Tallas</h2>
        <a href="<?= BASE_URL ?>?c=Tallas&a=crear" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nueva Talla
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
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar y Filtrar Tallas</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Tallas&a=index" method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="Tallas">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-5">
                    <label for="buscar" class="form-label">Buscar por nombre</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="buscar" 
                               name="buscar" 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                               placeholder="Ej: M Única o 42">
                    </div>
                    <div class="form-text">Busca por nombre de talla</div>
                </div>
                
                <div class="col-md-3">
                    <label for="estado" class="form-label">Filtrar por estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos los estados</option>
                        <option value="activo" <?= ($_GET['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivo" <?= ($_GET['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                    </button>
                </div>
                
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>?c=Tallas&a=index" class="btn btn-outline-secondary w-100">
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
                                <strong><?= count($tallas) ?></strong> tallas encontradas
                                
                                <?php if (!empty($_GET['buscar'])): ?>
                                    <span class="badge bg-primary ms-2">
                                        Nombre: "<?= htmlspecialchars($_GET['buscar']) ?>"
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
                            <a href="<?= BASE_URL ?>?c=Tallas&a=index" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i> Ver todas las tallas
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
                    <h5 class="card-title">Total Tallas</h5>
                    <h3 class="card-text">
                        <?php 
                        $totalTallas = $this->tallaModel->obtenerTodas();
                        echo count($totalTallas);
                        ?>
                    </h3>
                    <small>
                        <?php if (isset($modoBusqueda) && $modoBusqueda): ?>
                            <i class="fas fa-filter"></i> Filtrado: <?= count($tallas) ?>
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
                    <h5 class="card-title">Tallas Activas</h5>
                    <h3 class="card-text">
                        <?= $this->tallaModel->contarActivas() ?>
                    </h3>
                    <small><i class="fas fa-check-circle"></i> Disponibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Tallas Inactivas</h5>
                    <h3 class="card-text">
                        <?= count($totalTallas) - $this->tallaModel->contarActivas() ?>
                    </h3>
                    <small><i class="fas fa-pause"></i> No disponibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Con Sobrecosto</h5>
                    <h3 class="card-text">
                        <?php
                        $tallasConSobrecosto = array_filter($tallas, function($talla) {
                            return ($talla['Sobrecosto'] ?? 0) > 0;
                        });
                        echo count($tallasConSobrecosto);
                        ?>
                    </h3>
                    <small><i class="fas fa-dollar-sign"></i> Con sobrecosto</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Tallas -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Tallas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($tallas) && (isset($modoBusqueda) && $modoBusqueda)): ?>
                <!-- CASO 1: No hay resultados de búsqueda -->
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron tallas</h5>
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
                        <a href="<?= BASE_URL ?>?c=Tallas&a=index" class="btn btn-primary me-2">
                            <i class="fas fa-list me-1"></i> Ver todas las tallas
                        </a>
                        <a href="<?= BASE_URL ?>?c=Tallas&a=crear" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i> Crear nueva talla
                        </a>
                    </div>
                    
                    <!-- Sugerencias de búsqueda -->
                    <div class="mt-4 p-3 bg-light rounded" style="max-width: 500px; margin: 0 auto;">
                        <h6 class="text-muted">Sugerencias:</h6>
                        <ul class="list-unstyled text-start text-muted">
                            <li><small><i class="fas fa-lightbulb me-2"></i>Intenta con un nombre diferente</small></li>
                            <li><small><i class="fas fa-lightbulb me-2"></i>Verifica que el filtro de estado sea correcto</small></li>
                            <li><small><i class="fas fa-lightbulb me-2"></i>Busca por parte del nombre (ej: "M" en lugar de "M Única")</small></li>
                        </ul>
                    </div>
                </div>
            <?php elseif (empty($tallas)): ?>
                <!-- CASO 2: No hay tallas en el sistema -->
                <div class="text-center py-4">
                    <i class="fas fa-ruler fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay tallas registradas</h5>
                    <p class="text-muted">Comienza creando tu primera talla.</p>
                    <a href="<?= BASE_URL ?>?c=Tallas&a=crear" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Crear Primera Talla
                    </a>
                </div>
            <?php else: ?>
                <!-- CASO 3: Hay tallas - Mostrar tabla normal -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Talla</th>
                                <th>Sobrecosto</th>
                                <th>Estado</th>
                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tallas as $talla): 
                                $terminoBusqueda = $_GET['buscar'] ?? '';
                                $coincidenciaNombre = !empty($terminoBusqueda) && 
                                                    stripos($talla['N_Talla'], $terminoBusqueda) !== false;
                                $esTallaIndefinida = $talla['ID_Talla'] == 1;
                                $activo = $talla['Activo'] ?? ($talla['activo'] ?? 1); // Manejo de diferentes nombres de campo
                                $estadoTexto = $activo ? 'Activo' : 'Inactivo';
                                $estadoClase = $activo ? 'success' : 'secondary';
                            ?>
                                <tr class="<?= $coincidenciaNombre ? 'table-warning' : '' ?> <?= $esTallaIndefinida ? 'table-info' : '' ?>">
                                    <td>
                                        <strong>#<?= $talla['ID_Talla'] ?></strong>
                                        <?php if ($coincidenciaNombre): ?>
                                            <span class="badge bg-warning ms-1" title="Coincidencia en búsqueda">
                                                <i class="fas fa-search"></i>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($esTallaIndefinida): ?>
                                            <span class="badge bg-info ms-1" title="Talla especial del sistema">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold">
                                            <?= htmlspecialchars($talla['N_Talla']) ?>
                                        </span>
                                        <?php if ($coincidenciaNombre): ?>
                                            <br>
                                            <small class="text-warning">
                                                <i class="fas fa-bullseye"></i> Coincide con "<?= htmlspecialchars($terminoBusqueda) ?>"
                                            </small>
                                        <?php endif; ?>
                                        <?php if ($esTallaIndefinida): ?>
                                            <br>
                                            <small class="text-info">
                                                <i class="fas fa-shield-alt"></i> Talla especial del sistema
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            $<?= number_format($talla['Sobrecosto'] ?? 0, 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $estadoClase ?>">
                                            <?= $estadoTexto ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($talla['FechaActualizacionSobrecosto'] ?? $talla['FechaAct'] ?? 'now')) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if (!$esTallaIndefinida): ?>
                                                <a href="<?= BASE_URL ?>?c=Tallas&a=editar&id=<?= $talla['ID_Talla'] ?>" 
                                                   class="btn btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <?php if ($activo): ?>
                                                    <a href="<?= BASE_URL ?>?c=Tallas&a=cambiarEstado&id=<?= $talla['ID_Talla'] ?>&estado=0" 
                                                       class="btn btn-outline-warning" title="Desactivar"
                                                       onclick="return confirm('¿Estás seguro de desactivar esta talla?')">
                                                        <i class="fas fa-pause"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= BASE_URL ?>?c=Tallas&a=cambiarEstado&id=<?= $talla['ID_Talla'] ?>&estado=1" 
                                                       class="btn btn-outline-success" title="Activar"
                                                       onclick="return confirm('¿Estás seguro de activar esta talla?')">
                                                        <i class="fas fa-play"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="btn btn-outline-secondary" title="Talla especial - No editable">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                                <span class="btn btn-outline-secondary" title="Talla especial - Estado fijo">
                                                    <i class="fas fa-shield-alt"></i>
                                                </span>
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
</div>