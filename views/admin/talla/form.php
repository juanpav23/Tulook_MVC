<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-<?= isset($talla) ? 'edit' : 'plus' ?> me-2"></i>
            <?= isset($talla) ? 'Editar Talla' : 'Nueva Talla' ?>
        </h2>
        <a href="<?= BASE_URL ?>?c=Tallas&a=index" class="btn btn-secondary">
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
                Información de la Talla
            </h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Tallas&a=<?= isset($talla) ? 'actualizar' : 'guardar' ?>" method="post">
                <?php if (isset($talla)): ?>
                    <input type="hidden" name="ID_Talla" value="<?= $talla['ID_Talla'] ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="N_Talla" class="form-label">
                                <strong>Nombre de la Talla *</strong>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="N_Talla" 
                                   name="N_Talla" 
                                   required
                                   maxlength="20"
                                   value="<?= isset($talla) ? htmlspecialchars($talla['N_Talla']) : '' ?>"
                                   placeholder="Ej: M Única, 42 Única, 10 años"
                                   <?= (isset($talla) && $talla['ID_Talla'] == 1) ? 'readonly' : '' ?>>
                            <div class="form-text">
                                Ingresa el nombre descriptivo de la talla. Máximo 20 caracteres.
                                <?php if (isset($talla) && $talla['ID_Talla'] == 1): ?>
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Esta es una talla especial del sistema y no se puede modificar.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">
                                <strong>Estado de la Talla</strong>
                            </label>
                            <div class="form-check form-switch">
                                <?php
                                $activo = isset($talla) ? ($talla['Activo'] ?? ($talla['activo'] ?? 1)) : 1;
                                ?>
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="Activo" 
                                       name="Activo" 
                                       value="1"
                                       <?= $activo ? 'checked' : '' ?>
                                       <?= (isset($talla) && $talla['ID_Talla'] == 1) ? 'disabled' : '' ?>>
                                <label class="form-check-label" for="Activo">
                                    Talla Activa
                                </label>
                            </div>
                            <div class="form-text">
                                Las tallas inactivas no estarán disponibles para nuevos productos
                                <?php if (isset($talla) && $talla['ID_Talla'] == 1): ?>
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Esta talla siempre está activa.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <?php if (isset($talla)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="alert alert-<?= $talla['ID_Talla'] == 1 ? 'info' : 'secondary' ?>">
                                <small>
                                    <strong><i class="fas fa-info-circle me-1"></i>Información:</strong><br>
                                    • ID: #<?= $talla['ID_Talla'] ?><br>
                                    • Sobrecosto actual: $<?= number_format($talla['Sobrecosto'] ?? 0, 2) ?><br>
                                    <?php if (isset($talla['FechaActualizacionSobrecosto'])): ?>
                                        • Sobrecosto actualizado: <?= date('d/m/Y H:i', strtotime($talla['FechaActualizacionSobrecosto'])) ?>
                                    <?php endif; ?>
                                    <?php if ($talla['ID_Talla'] == 1): ?>
                                        • <strong class="text-info">Talla especial del sistema</strong><br>
                                        • <em>No editable - Siempre activa</em>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botones -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <?php if (!isset($talla) || $talla['ID_Talla'] != 1): ?>
                            <button type="submit" class="btn btn-success me-2">
                                <i class="fas fa-save me-1"></i>
                                <?= isset($talla) ? 'Actualizar Talla' : 'Crear Talla' ?>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary me-2" disabled>
                                <i class="fas fa-lock me-1"></i>
                                Talla No Editable
                            </button>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>?c=Tallas&a=index" class="btn btn-secondary">
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
            • Las tallas no se pueden eliminar, solo activar/desactivar<br>
            • La talla "Indefinida" es especial del sistema y no se puede editar ni desactivar<br>
            • El sobrecosto se gestiona desde la configuración del sistema<br>
            • Las tallas activas estarán disponibles para asignar a productos
        </small>
    </div>
</div>