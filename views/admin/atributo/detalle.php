<?php
// Verificar si es el atributo "Única"
$esUnica = ($atributo['ID_AtributoValor'] == 16 || strtolower($atributo['Valor']) === 'única');
$enUso = count($productos) > 0 || $esUnica;
$puedeEliminar = !$enUso && !$esUnica;
$puedeEditar = !$esUnica;
$puedeCambiarEstado = !$esUnica && (!$enUso || $atributo['Activo'] == 1);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/atributos.css">
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary-dark">
            <i class="fas fa-info-circle me-2"></i>
            Detalles del Atributo
        </h2>
        <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Tarjeta de Información -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4 stats-card">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tag me-2"></i>Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">ID:</th>
                                    <td><strong class="text-primary-dark">#<?= $atributo['ID_AtributoValor'] ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Tipo:</th>
                                    <td>
                                        <span class="badge bg-primary-light text-primary-dark border border-primary">
                                            <?= htmlspecialchars($atributo['TipoNombre']) ?>
                                        </span>
                                        <?php if (!empty($atributo['TipoDescripcion'])): ?>
                                            <br><small class="text-primary-light"><?= $atributo['TipoDescripcion'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Valor:</th>
                                    <td>
                                        <span class="fw-bold fs-5 text-primary-dark">
                                            <?= htmlspecialchars($atributo['Valor']) ?>
                                        </span>
                                        <?php if ($esUnica): ?>
                                            <br><span class="badge bg-warning text-dark mt-1">
                                                <i class="fas fa-shield-alt me-1"></i> Valor universal del sistema
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Orden:</th>
                                    <td>
                                        <span class="badge bg-light border border-primary-light text-primary-dark fs-6">
                                            <?= $atributo['Orden'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        <span class="badge bg-<?= $atributo['Activo'] ? 'primary' : 'secondary' ?> fs-6">
                                            <?= $atributo['Activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>En uso:</th>
                                    <td>
                                        <?php if ($enUso): ?>
                                            <span class="badge bg-primary-light text-primary-dark border border-primary">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <?= $esUnica ? 'Valor universal' : 'Usado por ' . count($productos) . ' productos' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-primary-dark border border-primary">No en uso</span>
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
                <div class="card stats-card">
                    <div class="card-header bg-primary-light text-primary-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-boxes me-2"></i>
                            Productos que usan este atributo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-primary-dark">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Artículo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productos as $producto): ?>
                                        <tr class="hover-shadow-detalle">
                                            <td><?= htmlspecialchars($producto['Nombre_Producto']) ?></td>
                                            <td><?= htmlspecialchars($producto['Articulo']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-primary-light border border-primary mt-3">
                            <small>
                                <i class="fas fa-info-circle me-1 text-primary-dark"></i>
                                <span class="text-primary-dark">
                                    Este atributo está siendo usado por productos.
                                </span>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Panel de acciones -->
        <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-primary-dark"><i class="fas fa-cogs me-2"></i>Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($puedeEditar): ?>
                            <a href="<?= BASE_URL ?>?c=Atributo&a=editar&id=<?= $atributo['ID_AtributoValor'] ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Editar Atributo
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-ban me-1"></i> No se puede editar
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($atributo['Activo'] && $puedeCambiarEstado): ?>
                            <a href="<?= BASE_URL ?>?c=Atributo&a=cambiarEstado&id=<?= $atributo['ID_AtributoValor'] ?>&estado=0" 
                               class="btn btn-primary-light text-primary-dark"
                               onclick="return confirm('¿Estás seguro de desactivar este atributo?')">
                                <i class="fas fa-pause me-1"></i> Desactivar
                            </a>
                        <?php elseif (!$atributo['Activo'] && $puedeCambiarEstado): ?>
                            <a href="<?= BASE_URL ?>?c=Atributo&a=cambiarEstado&id=<?= $atributo['ID_AtributoValor'] ?>&estado=1" 
                               class="btn btn-primary"
                               onclick="return confirm('¿Estás seguro de activar este atributo?')">
                                <i class="fas fa-play me-1"></i> Activar
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-ban me-1"></i> No se puede cambiar estado
                            </button>
                        <?php endif; ?>
                        
                        <!-- Botón Eliminar -->
                        <?php if ($puedeEliminar): ?>
                            <a href="<?= BASE_URL ?>?c=Atributo&a=eliminar&id=<?= $atributo['ID_AtributoValor'] ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('⚠️ ¿Estás seguro de eliminar el atributo &quot;<?= htmlspecialchars($atributo['Valor']) ?>&quot;? Esta acción no se puede deshacer.')">
                                <i class="fas fa-trash me-1"></i> Eliminar Atributo
                            </a>
                        <?php elseif ($esUnica): ?>
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-shield-alt me-1"></i> Valor protegido
                            </button>
                            <div class="alert alert-warning mt-2 p-2">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    Este valor universal no puede eliminarse del sistema.
                                </small>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-ban me-1"></i> No se puede eliminar
                            </button>
                            <div class="alert alert-info mt-2 p-2">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    Este atributo está siendo usado por productos y no puede eliminarse.
                                </small>
                            </div>
                        <?php endif; ?>
                        
                        <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-outline-primary-light text-primary-dark">
                            <i class="fas fa-list me-1"></i> Ver todos los atributos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información técnica -->
            <div class="card mt-3 stats-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-primary-dark"><i class="fas fa-database me-2"></i>Información Técnica</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <small>
                                <i class="fas fa-hashtag me-2 text-primary-dark"></i>
                                <strong class="text-primary-dark">ID Tipo:</strong> <?= $atributo['ID_TipoAtributo'] ?>
                            </small>
                        </li>
                        <li class="mb-2">
                            <small>
                                <i class="fas fa-code me-2 text-primary-dark"></i>
                                <strong class="text-primary-dark">Tabla:</strong> atributo_valor
                            </small>
                        </li>
                        <li>
                            <small>
                                <i class="fas fa-link me-2 text-primary-dark"></i>
                                <strong class="text-primary-dark">Relación:</strong> tipo_atributo
                            </small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/atributo.js"></script>