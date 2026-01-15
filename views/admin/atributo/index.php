<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-list-alt me-2"></i>Gestión de Atributos</h2>
        <a href="<?= BASE_URL ?>?c=Atributo&a=crear" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Atributo
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
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtrar Atributos</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Atributo&a=index" method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="Atributo">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-3">
                    <label for="buscar" class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="buscar" 
                               name="buscar" 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                               placeholder="Valor o tipo...">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= $tipo['ID_TipoAtributo'] ?>" 
                                <?= ($_GET['tipo'] ?? '') == $tipo['ID_TipoAtributo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['Nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="activo" <?= ($_GET['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivo" <?= ($_GET['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Atributos</h5>
                    <h3 class="card-text"><?= $estadisticas['total'] ?? 0 ?></h3>
                    <small><i class="fas fa-database"></i> En sistema</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Activos</h5>
                    <h3 class="card-text"><?= $estadisticas['activos'] ?? 0 ?></h3>
                    <small><i class="fas fa-check-circle"></i> Disponibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Inactivos</h5>
                    <h3 class="card-text"><?= $estadisticas['inactivos'] ?? 0 ?></h3>
                    <small><i class="fas fa-pause"></i> No disponibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Tipos</h5>
                    <h3 class="card-text"><?= $estadisticas['tipos_diferentes'] ?? 0 ?></h3>
                    <small><i class="fas fa-tags"></i> Diferentes</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Atributos -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Lista de Atributos</h5>
        </div>
        <div class="card-body">
            <?php if (empty($atributos)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay atributos registrados</h5>
                    <?php if (isset($modoBusqueda) && $modoBusqueda): ?>
                        <p class="text-muted">No hay resultados para los filtros aplicados</p>
                        <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-primary">
                            <i class="fas fa-times me-1"></i> Limpiar filtros
                        </a>
                    <?php else: ?>
                        <p class="text-muted">Comienza creando tu primer atributo</p>
                        <a href="<?= BASE_URL ?>?c=Atributo&a=crear" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Crear primer atributo
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($atributos as $attr): ?>
                                <tr>
                                    <td>#<?= $attr['ID_AtributoValor'] ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= htmlspecialchars($attr['TipoNombre']) ?>
                                        </span>
                                        <?php if (!empty($attr['TipoDescripcion'])): ?>
                                            <br><small class="text-muted"><?= $attr['TipoDescripcion'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($attr['Valor']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info" title="Orden automático basado en el valor">
                                            <i class="fas fa-robot me-1"></i> Auto
                                        </span>
                                        <br>
                                        <small class="text-muted">#<?= $attr['Orden'] ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $attr['Activo'] ? 'success' : 'secondary' ?>">
                                            <?= $attr['Activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>?c=Atributo&a=detalle&id=<?= $attr['ID_AtributoValor'] ?>" 
                                            class="btn btn-outline-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>?c=Atributo&a=editar&id=<?= $attr['ID_AtributoValor'] ?>" 
                                            class="btn btn-outline-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($attr['Activo']): ?>
                                                <a href="<?= BASE_URL ?>?c=Atributo&a=cambiarEstado&id=<?= $attr['ID_AtributoValor'] ?>&estado=0" 
                                                class="btn btn-outline-warning" title="Desactivar"
                                                onclick="return confirm('¿Estás seguro de desactivar este atributo?')">
                                                    <i class="fas fa-pause"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= BASE_URL ?>?c=Atributo&a=cambiarEstado&id=<?= $attr['ID_AtributoValor'] ?>&estado=1" 
                                                class="btn btn-outline-success" title="Activar"
                                                onclick="return confirm('¿Estás seguro de activar este atributo?')">
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
</div>