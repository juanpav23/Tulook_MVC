<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-<?= isset($precio) ? 'edit' : 'plus' ?> me-2"></i>
            <?= isset($precio) ? 'Editar Precio' : 'Nuevo Precio' ?>
        </h2>
        <a href="<?= BASE_URL ?>?c=Precio&a=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
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

    <!-- Formulario -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Información del Precio
            </h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Precio&a=<?= isset($precio) ? 'actualizar' : 'guardar' ?>" method="post">
                <?php if (isset($precio)): ?>
                    <input type="hidden" name="ID_precio" value="<?= $precio['ID_precio'] ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Valor" class="form-label">
                                <strong>Valor del Precio *</strong>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="Valor" 
                                       name="Valor" 
                                       step="0.01" 
                                       min="0.01" 
                                       required
                                       value="<?= isset($precio) ? number_format($precio['Valor'], 2, '.', '') : '' ?>"
                                       placeholder="Ej: 25000.00">
                            </div>
                            <div class="form-text">
                                Ingresa el valor numérico del precio. Mínimo: $0.01
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">
                                <strong>Estado del Precio</strong>
                            </label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="Activo" 
                                       name="Activo" 
                                       value="1"
                                       <?= (isset($precio) && $precio['Activo']) || !isset($precio) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="Activo">
                                    Precio Activo
                                </label>
                            </div>
                            <div class="form-text">
                                Los precios inactivos no estarán disponibles para nuevos productos
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <?php if (isset($precio)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <small>
                                    <strong><i class="fas fa-info-circle me-1"></i>Información:</strong><br>
                                    • ID: #<?= $precio['ID_precio'] ?><br>
                                    • Creado: <?= date('d/m/Y H:i', strtotime($precio['FechaAct'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botones -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-save me-1"></i>
                            <?= isset($precio) ? 'Actualizar Precio' : 'Crear Precio' ?>
                        </button>
                        <a href="<?= BASE_URL ?>?c=Precio&a=index" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Información importante -->
    <div class="alert alert-warning mt-3">
        <h6><i class="fas fa-exclamation-triangle me-2"></i>Importante</h6>
        <small>
            • Los precios no se pueden eliminar, solo activar/desactivar<br>
            • Los precios inactivos no estarán disponibles para asignar a nuevos productos<br>
            • Al editar un precio, se actualizará automáticamente la fecha de modificación
        </small>
    </div>
</div>