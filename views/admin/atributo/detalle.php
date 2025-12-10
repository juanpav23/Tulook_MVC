<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-info-circle me-2"></i>
            Detalles del Atributo
        </h2>
        <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Tarjeta de Información -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tag me-2"></i>Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">ID:</th>
                                    <td><strong>#<?= $atributo['ID_AtributoValor'] ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Tipo:</th>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= htmlspecialchars($atributo['TipoNombre']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Valor:</th>
                                    <td>
                                        <span class="fw-bold fs-5">
                                            <?= htmlspecialchars($atributo['Valor']) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Orden:</th>
                                    <td>
                                        <span class="badge bg-light text-dark fs-6">
                                            <?= $atributo['Orden'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        <span class="badge bg-<?= $atributo['Activo'] ? 'success' : 'secondary' ?> fs-6">
                                            <?= $atributo['Activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>En uso:</th>
                                    <td>
                                        <?php if (count($productos) > 0): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Usado por <?= count($productos) ?> productos
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No en uso</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos que usan este atributo -->
            <?php if (!empty($productos)): ?>
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">
                            <i class="fas fa-boxes me-2"></i>
                            Productos que usan este atributo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Artículo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productos as $producto): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($producto['Nombre_Producto']) ?></td>
                                            <td><?= htmlspecialchars($producto['Articulo']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Este atributo no puede ser desactivado porque está siendo usado por productos.
                                Si necesitas eliminarlo, primero debes cambiar los atributos de los productos que lo usan.
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Panel de acciones -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>?c=Atributo&a=editar&id=<?= $atributo['ID_AtributoValor'] ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Editar Atributo
                        </a>
                        
                        <?php if ($atributo['Activo']): ?>
                            <a href="<?= BASE_URL ?>?c=Atributo&a=cambiarEstado&id=<?= $atributo['ID_AtributoValor'] ?>&estado=0" 
                               class="btn btn-warning"
                               onclick="return confirm('<?= count($productos) > 0 ? 'Este atributo está en uso y NO puede desactivarse. ¿Continuar?' : '¿Estás seguro de desactivar este atributo?' ?>')">
                                <i class="fas fa-pause me-1"></i> Desactivar
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>?c=Atributo&a=cambiarEstado&id=<?= $atributo['ID_AtributoValor'] ?>&estado=1" 
                               class="btn btn-success"
                               onclick="return confirm('¿Estás seguro de activar este atributo?')">
                                <i class="fas fa-play me-1"></i> Activar
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i> Ver todos los atributos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información técnica -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-database me-2"></i>Información Técnica</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <small>
                                <i class="fas fa-hashtag me-2 text-muted"></i>
                                <strong>ID Tipo:</strong> <?= $atributo['ID_TipoAtributo'] ?>
                            </small>
                        </li>
                        <li class="mb-2">
                            <small>
                                <i class="fas fa-code me-2 text-muted"></i>
                                <strong>Tabla:</strong> atributo_valor
                            </small>
                        </li>
                        <li>
                            <small>
                                <i class="fas fa-link me-2 text-muted"></i>
                                <strong>Relación:</strong> tipo_atributo
                            </small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>