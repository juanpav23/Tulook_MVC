<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-info-circle me-2"></i>Detalles del Color</h2>
        <a href="<?= BASE_URL ?>?c=Color&a=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

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
                                    <td><strong>#<?= $color['ID_Color'] ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Nombre:</th>
                                    <td>
                                        <span class="fw-bold fs-5">
                                            <?= htmlspecialchars($color['N_Color']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Código HEX:</th>
                                    <td>
                                        <code><?= htmlspecialchars($color['CodigoHex']) ?></code>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column align-items-center">
                                <div class="color-muestra mb-3" 
                                     style="background-color: <?= $color['CodigoHex'] ?>; 
                                            width: 120px; 
                                            height: 120px; 
                                            border: 1px solid #ddd; 
                                            border-radius: 8px;">
                                </div>
                                <small class="text-muted">Muestra de color</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de acciones -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>?c=Color&a=editar&id=<?= $color['ID_Color'] ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Editar Color
                        </a>
                        <a href="<?= BASE_URL ?>?c=Color&a=eliminar&id=<?= $color['ID_Color'] ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('¿Estás seguro de eliminar este color? Esta acción no se puede deshacer.')">
                            <i class="fas fa-trash me-1"></i> Eliminar Color
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información técnica -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-database me-2"></i>Información Técnica</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <small>
                                <i class="fas fa-hashtag me-2 text-muted"></i>
                                <strong>Tabla:</strong> color
                            </small>
                        </li>
                        <li>
                            <small>
                                <i class="fas fa-code me-2 text-muted"></i>
                                <strong>Campo:</strong> N_Color, CodigoHex
                            </small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>